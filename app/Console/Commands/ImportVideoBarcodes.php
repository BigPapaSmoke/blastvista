<?php

namespace App\Console\Commands;

use App\Models\Video;
use Illuminate\Console\Command;

class ImportVideoBarcodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Expected CSV headers: filename,barcode
     */
    protected $signature = 'videos:import-barcodes
                            {file=storage/barcodes_map.csv : CSV path relative to project root or absolute}
                            {--dry-run : Preview changes without writing to database}';

    /**
     * The console command description.
     */
    protected $description = 'Bulk-assign UPC barcodes to videos using a CSV file';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $pathArg = (string) $this->argument('file');
        $path = $this->resolvePath($pathArg);

        if (!is_file($path) || !is_readable($path)) {
            $this->error("CSV file not found or not readable: {$path}");
            return self::FAILURE;
        }

        [$rows, $readErrors] = $this->readCsvRows($path);

        if (!empty($readErrors)) {
            foreach ($readErrors as $error) {
                $this->warn($error);
            }
        }

        if (empty($rows)) {
            $this->error('No valid rows found. Ensure CSV has headers: filename,barcode');
            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');

        $updated = 0;
        $unchanged = 0;
        $notFound = 0;
        $duplicateInCsv = 0;
        $barcodeCollisions = 0;

        $seenFilenames = [];
        $seenBarcodes = [];

        foreach ($rows as $index => $row) {
            $line = $index + 2; // account for header row
            $filename = trim((string) ($row['filename'] ?? ''));
            $barcode = trim((string) ($row['barcode'] ?? ''));

            if ($filename === '' || $barcode === '') {
                $this->warn("Line {$line}: missing filename or barcode, skipped.");
                continue;
            }

            $filenameKey = mb_strtolower($filename);
            $barcodeKey = mb_strtolower($barcode);

            if (isset($seenFilenames[$filenameKey])) {
                $duplicateInCsv++;
                $this->warn("Line {$line}: duplicate filename in CSV ({$filename}), skipped.");
                continue;
            }

            if (isset($seenBarcodes[$barcodeKey])) {
                $duplicateInCsv++;
                $this->warn("Line {$line}: duplicate barcode in CSV ({$barcode}), skipped.");
                continue;
            }

            $seenFilenames[$filenameKey] = true;
            $seenBarcodes[$barcodeKey] = true;

            $video = Video::where('filename', $filename)->first();

            if (!$video) {
                $notFound++;
                $this->warn("Line {$line}: no video found for filename '{$filename}'.");
                continue;
            }

            $conflict = Video::where('barcode', $barcode)
                ->where('id', '!=', $video->id)
                ->exists();

            if ($conflict) {
                $barcodeCollisions++;
                $this->warn("Line {$line}: barcode '{$barcode}' already used by another video, skipped.");
                continue;
            }

            if ($video->barcode === $barcode) {
                $unchanged++;
                continue;
            }

            if (!$dryRun) {
                $video->update(['barcode' => $barcode]);
            }

            $updated++;
        }

        $this->newLine();
        $this->info($dryRun ? 'Dry-run completed.' : 'Import completed.');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Rows parsed', count($rows)],
                ['Updated', $updated],
                ['Unchanged', $unchanged],
                ['Not found by filename', $notFound],
                ['Duplicate rows in CSV', $duplicateInCsv],
                ['Barcode collisions', $barcodeCollisions],
                ['Read/format warnings', count($readErrors)],
            ]
        );

        if ($dryRun) {
            $this->line('No database changes were made because --dry-run was used.');
        }

        return self::SUCCESS;
    }

    /**
     * @return array{0: array<int, array{filename: string, barcode: string}>, 1: array<int, string>}
     */
    private function readCsvRows(string $path): array
    {
        $rows = [];
        $errors = [];

        $handle = fopen($path, 'r');
        if ($handle === false) {
            return [[], ["Unable to open CSV file: {$path}"]];
        }

        $headersRaw = fgetcsv($handle);
        if ($headersRaw === false) {
            fclose($handle);
            return [[], ['CSV appears to be empty.']];
        }

        $headers = array_map(
            static fn ($h) => mb_strtolower(trim((string) $h)),
            $headersRaw
        );

        $filenameIndex = array_search('filename', $headers, true);
        $barcodeIndex = array_search('barcode', $headers, true);

        if ($filenameIndex === false || $barcodeIndex === false) {
            fclose($handle);
            return [[], ['CSV must contain headers: filename,barcode']];
        }

        while (($data = fgetcsv($handle)) !== false) {
            if ($this->isEmptyRow($data)) {
                continue;
            }

            $rows[] = [
                'filename' => isset($data[$filenameIndex]) ? trim((string) $data[$filenameIndex]) : '',
                'barcode' => isset($data[$barcodeIndex]) ? trim((string) $data[$barcodeIndex]) : '',
            ];
        }

        fclose($handle);

        return [$rows, $errors];
    }

    /**
     * @param array<int, mixed> $row
     */
    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $cell) {
            if (trim((string) $cell) !== '') {
                return false;
            }
        }

        return true;
    }

    private function resolvePath(string $pathArg): string
    {
        if (str_starts_with($pathArg, '/')) {
            return $pathArg;
        }

        return base_path($pathArg);
    }
}
