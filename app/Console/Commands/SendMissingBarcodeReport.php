<?php

namespace App\Console\Commands;

use App\Mail\MissingBarcodeReportMail;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendMissingBarcodeReport extends Command
{
    protected $signature = 'barcodes:missing-report {--force : Send the report even when no missing barcodes were logged today}';

    protected $description = 'Email a daily summary of barcodes that were scanned but not found';

    public function handle(): int
    {
        $logFile = storage_path('app/private/reports/missing_scans.jsonl');

        if (! is_file($logFile)) {
            $this->warn('Missing scan log not found: ' . $logFile);

            return self::FAILURE;
        }

        $report = $this->buildReport($logFile);

        if ($report['unique_missing_barcodes'] === 0 && ! $this->option('force')) {
            $this->info('No missing barcodes were found. Nothing was emailed.');

            return self::SUCCESS;
        }

        $recipient = config('mail.missing_barcode_report_to', config('mail.from.address'));

        if (! is_string($recipient) || trim($recipient) === '') {
            $this->error('No recipient configured. Set MISSING_BARCODE_REPORT_EMAIL or ADMIN_EMAIL in .env.');

            return self::FAILURE;
        }

        Mail::to($recipient)->send(new MissingBarcodeReportMail($report));

        $this->info('Missing barcode report emailed to ' . $recipient);

        return self::SUCCESS;
    }

    private function buildReport(string $logFile): array
    {
        $counts = [];
        $windowEnd = now();
        $windowStart = (clone $windowEnd)->subDay();

        $normalize = static function (?string $value): string {
            $value = strtoupper(trim((string) $value));

            return preg_replace('/[^A-Z0-9]/', '', $value) ?? '';
        };

        $handle = fopen($logFile, 'r');

        while (($line = fgets($handle)) !== false) {
            $row = json_decode(trim($line), true);

            if (! is_array($row)) {
                continue;
            }

            $timestamp = (string) ($row['timestamp'] ?? '');

            if ($timestamp === '') {
                continue;
            }

            $recordedAt = Carbon::parse($timestamp);

            if ($recordedAt->lt($windowStart) || $recordedAt->gt($windowEnd)) {
                continue;
            }

            $barcode = $normalize((string) ($row['normalized_barcode'] ?? $row['raw_barcode'] ?? ''));

            if ($barcode === '') {
                continue;
            }

            if (! isset($counts[$barcode])) {
                $counts[$barcode] = [
                    'barcode' => $barcode,
                    'product_name' => (string) ($row['product_name'] ?? ''),
                    'scan_count' => 0,
                    'first_seen' => $timestamp,
                    'last_seen' => $timestamp,
                ];
            }

            $counts[$barcode]['scan_count']++;
            $counts[$barcode]['last_seen'] = $timestamp;
        }

        fclose($handle);

        usort($counts, static function (array $left, array $right): int {
            return [$right['scan_count'], $left['barcode']] <=> [$left['scan_count'], $right['barcode']];
        });

        return [
            'report_date' => $windowEnd->toDateString(),
            'window_start' => $windowStart->toIso8601String(),
            'window_end' => $windowEnd->toIso8601String(),
            'unique_missing_barcodes' => count($counts),
            'total_missing_scans' => array_sum(array_column($counts, 'scan_count')),
            'items' => $counts,
        ];
    }
}