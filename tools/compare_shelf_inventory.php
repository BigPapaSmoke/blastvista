<?php

declare(strict_types=1);

use App\Models\Video;
use Illuminate\Contracts\Console\Kernel;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$shelfCsv = __DIR__ . '/../storage/barcodes.csv';
$reportDir = __DIR__ . '/../storage/reports';
$csvReport = $reportDir . '/shelf_vs_videos.csv';
$mdReport = $reportDir . '/shelf_vs_videos_summary.md';

if (! is_file($shelfCsv)) {
    fwrite(STDERR, "Shelf CSV not found: {$shelfCsv}\n");
    exit(1);
}

if (! is_dir($reportDir) && ! mkdir($reportDir, 0775, true) && ! is_dir($reportDir)) {
    fwrite(STDERR, "Unable to create report directory: {$reportDir}\n");
    exit(1);
}

$normalize = static function (?string $value): string {
    $value = strtoupper(trim((string) $value));

    return preg_replace('/[^A-Z0-9]/', '', $value) ?? '';
};

$normalizeFilename = static function (string $filename) use ($normalize): string {
    $base = pathinfo($filename, PATHINFO_FILENAME);

    return $normalize($base);
};

$rows = [];
$handle = fopen($shelfCsv, 'r');

while (($row = fgetcsv($handle)) !== false) {
    $productName = trim((string) ($row[2] ?? ''));
    $primaryBarcode = trim((string) ($row[3] ?? ''));
    $secondaryBarcode = trim((string) ($row[4] ?? ''));

    if ($productName === '' && $primaryBarcode === '' && $secondaryBarcode === '') {
        continue;
    }

    $rows[] = [
        'product_name' => $productName,
        'primary_barcode' => $primaryBarcode,
        'secondary_barcode' => $secondaryBarcode,
    ];
}

fclose($handle);

$videos = Video::query()->get(['barcode', 'filename']);

$videosByBarcode = [];
$videosByFilename = [];

foreach ($videos as $video) {
    $normalizedBarcode = $normalize($video->barcode);
    $normalizedFilename = $normalizeFilename($video->filename);

    if ($normalizedBarcode !== '' && ! array_key_exists($normalizedBarcode, $videosByBarcode)) {
        $videosByBarcode[$normalizedBarcode] = $video;
    }

    if ($normalizedFilename !== '' && ! array_key_exists($normalizedFilename, $videosByFilename)) {
        $videosByFilename[$normalizedFilename] = $video;
    }
}

$reportRows = [];
$matchedCount = 0;
$possibleNameMatchCount = 0;
$missingCount = 0;

foreach ($rows as $row) {
    $productName = $row['product_name'];
    $primaryBarcode = $row['primary_barcode'];
    $secondaryBarcode = $row['secondary_barcode'];

    $candidateBarcodes = array_values(array_unique(array_filter([
        $normalize($primaryBarcode),
        $normalize($secondaryBarcode),
    ])));

    $matchedVideo = null;
    $matchType = 'missing';

    foreach ($candidateBarcodes as $candidateBarcode) {
        if (isset($videosByBarcode[$candidateBarcode])) {
            $matchedVideo = $videosByBarcode[$candidateBarcode];
            $matchType = $candidateBarcode === $normalize($primaryBarcode)
                ? 'matched_primary_barcode'
                : 'matched_secondary_barcode';
            break;
        }
    }

    if (! $matchedVideo) {
        $normalizedProductName = $normalize($productName);

        if ($normalizedProductName !== '' && isset($videosByFilename[$normalizedProductName])) {
            $matchedVideo = $videosByFilename[$normalizedProductName];
            $matchType = 'possible_name_match';
        }
    }

    if ($matchType === 'matched_primary_barcode' || $matchType === 'matched_secondary_barcode') {
        $matchedCount++;
    } elseif ($matchType === 'possible_name_match') {
        $possibleNameMatchCount++;
    } else {
        $missingCount++;
    }

    $reportRows[] = [
        'product_name' => $productName,
        'primary_barcode' => $primaryBarcode,
        'secondary_barcode' => $secondaryBarcode,
        'status' => $matchType,
        'matched_db_barcode' => $matchedVideo?->barcode ?? '',
        'matched_filename' => $matchedVideo?->filename ?? '',
    ];
}

$csvHandle = fopen($csvReport, 'w');
fputcsv($csvHandle, array_keys($reportRows[0] ?? [
    'product_name' => '',
    'primary_barcode' => '',
    'secondary_barcode' => '',
    'status' => '',
    'matched_db_barcode' => '',
    'matched_filename' => '',
]));

foreach ($reportRows as $reportRow) {
    fputcsv($csvHandle, $reportRow);
}

fclose($csvHandle);

$missingProducts = array_values(array_filter(
    $reportRows,
    static fn (array $row): bool => $row['status'] === 'missing'
));

$possibleNameMatches = array_values(array_filter(
    $reportRows,
    static fn (array $row): bool => $row['status'] === 'possible_name_match'
));

$markdown = [];
$markdown[] = '# Shelf vs Video Inventory';
$markdown[] = '';
$markdown[] = '- Shelf products checked: ' . count($rows);
$markdown[] = '- Exact barcode matches: ' . $matchedCount;
$markdown[] = '- Possible filename/name matches: ' . $possibleNameMatchCount;
$markdown[] = '- Missing from video inventory: ' . $missingCount;
$markdown[] = '';
$markdown[] = '## Missing Products';
$markdown[] = '';

if ($missingProducts === []) {
    $markdown[] = 'None';
} else {
    foreach ($missingProducts as $row) {
        $markdown[] = '- ' . $row['product_name']
            . ' | primary: ' . ($row['primary_barcode'] !== '' ? $row['primary_barcode'] : '-')
            . ' | secondary: ' . ($row['secondary_barcode'] !== '' ? $row['secondary_barcode'] : '-');
    }
}

$markdown[] = '';
$markdown[] = '## Possible Name Matches';
$markdown[] = '';

if ($possibleNameMatches === []) {
    $markdown[] = 'None';
} else {
    foreach ($possibleNameMatches as $row) {
        $markdown[] = '- ' . $row['product_name'] . ' -> ' . $row['matched_filename'];
    }
}

file_put_contents($mdReport, implode(PHP_EOL, $markdown) . PHP_EOL);

echo "Wrote CSV report: {$csvReport}\n";
echo "Wrote summary: {$mdReport}\n";
echo 'Shelf products checked: ' . count($rows) . "\n";
echo 'Exact barcode matches: ' . $matchedCount . "\n";
echo 'Possible filename/name matches: ' . $possibleNameMatchCount . "\n";
echo 'Missing from video inventory: ' . $missingCount . "\n";