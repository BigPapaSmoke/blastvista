@component('mail::message')
# Daily Missing Barcode Report

Date: {{ $report['report_date'] }}

Window: {{ $report['window_start'] }} to {{ $report['window_end'] }}

Unique missing barcodes: {{ $report['unique_missing_barcodes'] }}

Total missing scans: {{ $report['total_missing_scans'] }}

@if ($report['items'] === [])
No missing barcodes were logged today.
@else
@component('mail::table')
| Barcode | Product | Scans | First Seen | Last Seen |
|:--|:--|:--|:--|:--|
@foreach ($report['items'] as $item)
| {{ $item['barcode'] }} | {{ $item['product_name'] !== '' ? $item['product_name'] : '[not found in shelf CSV]' }} | {{ $item['scan_count'] }} | {{ $item['first_seen'] }} | {{ $item['last_seen'] }} |
@endforeach
@endcomponent
@endif

@if (config('mail.from.address'))
This report was sent from {{ config('mail.from.address') }}.
@endif
@endcomponent