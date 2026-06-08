<?php

declare(strict_types=1);

$logFile = __DIR__ . '/../storage/app/private/reports/missing_scans.jsonl';
$shelfCsv = __DIR__ . '/../storage/barcodes.csv';
$reportDir = __DIR__ . '/../storage/reports';
$csvReport = $reportDir . '/missing_scans_summary.csv';
$mdReport = $reportDir . '/missing_scans_summary.md';

if (! is_file($logFile)) {
    fwrite(STDERR, "Missing scan log not found: {$logFile}\n");
    exit(1);
}

if (! is_dir($reportDir) && ! mkdir($reportDir, 0775, true) && ! is_dir($reportDir)) {
    fwrite(STDERR, "Unable to create report directory: {$reportDir}\n");
    exit(1);
}

$counts = [];

$normalize = static function (?string $value): string {
    $value = strtoupper(trim((string) $value));

    return preg_replace('/[^A-Z0-9]/', '', $value) ?? '';
};

$productNamesByBarcode = [];

if (is_file($shelfCsv)) {
    $shelfHandle = fopen($shelfCsv, 'r');

    while (($row = fgetcsv($shelfHandle)) !== false) {
        $productName = trim((string) ($row[2] ?? ''));
        $primaryBarcode = $normalize($row[3] ?? '');
        $secondaryBarcode = $normalize($row[4] ?? '');

        foreach (array_filter([$primaryBarcode, $secondaryBarcode]) as $barcode) {
            if ($productName !== '' && ! isset($productNamesByBarcode[$barcode])) {
                $productNamesByBarcode[$barcode] = $productName;
            }
        }
    }

    fclose($shelfHandle);
}

$handle = fopen($logFile, 'r');

while (($line = fgets($handle)) !== false) {
    $row = json_decode(trim($line), true);

    if (! is_array($row)) {
        continue;
    }

    $barcode = $normalize((string) ($row['normalized_barcode'] ?? $row['raw_barcode'] ?? ''));

    if ($barcode === '') {
        continue;
    }

    if (! isset($counts[$barcode])) {
        $counts[$barcode] = [
            'barcode' => $barcode,
            'product_name' => $productNamesByBarcode[$barcode] ?? '',
            'scan_count' => 0,
            'first_seen' => (string) ($row['timestamp'] ?? ''),
            'last_seen' => (string) ($row['timestamp'] ?? ''),
            'sample_raw_barcode' => (string) ($row['raw_barcode'] ?? ''),
        ];
    }

    $counts[$barcode]['scan_count']++;
    $counts[$barcode]['last_seen'] = (string) ($row['timestamp'] ?? $counts[$barcode]['last_seen']);
}

fclose($handle);

usort($counts, static function (array $left, array $right): int {
    return [$right['scan_count'], $left['barcode']] <=> [$left['scan_count'], $right['barcode']];
});

$csvHandle = fopen($csvReport, 'w');
fputcsv($csvHandle, ['barcode', 'product_name', 'scan_count', 'first_seen', 'last_seen', 'sample_raw_barcode']);

foreach ($counts as $row) {
    fputcsv($csvHandle, $row);
}

fclose($csvHandle);

$markdown = [];
$markdown[] = '# Missing Scan Summary';
$markdown[] = '';
$markdown[] = '- Unique missing barcodes: ' . count($counts);
$markdown[] = '- Total missing scans logged: ' . array_sum(array_column($counts, 'scan_count'));
$markdown[] = '';
$markdown[] = '## Top Missing Barcodes';
$markdown[] = '';

if ($counts === []) {
    $markdown[] = 'None';
} else {
    foreach (array_slice($counts, 0, 50) as $row) {
        $markdown[] = '- ' . $row['barcode']
            . ' | name: ' . ($row['product_name'] !== '' ? $row['product_name'] : '[not found in shelf CSV]')
            . ' | scans: ' . $row['scan_count']
            . ' | first: ' . $row['first_seen']
            . ' | last: ' . $row['last_seen'];
    }
}

file_put_contents($mdReport, implode(PHP_EOL, $markdown) . PHP_EOL);

echo "Wrote CSV report: {$csvReport}\n";
echo "Wrote summary: {$mdReport}\n";
echo 'Unique missing barcodes: ' . count($counts) . "\n";
echo 'Total missing scans logged: ' . array_sum(array_column($counts, 'scan_count')) . "\n";

if ($counts !== []) {
    echo "\nMissing barcodes:\n";

    foreach ($counts as $row) {
        echo '- ' . $row['barcode'];

        if ($row['product_name'] !== '') {
            echo ' | ' . $row['product_name'];
        }

        echo ' | scans: ' . $row['scan_count'] . "\n";
    }
}