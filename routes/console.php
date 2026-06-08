<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

$isManNode = static function (): bool {
    $role = strtolower(trim((string) env('NODE_ROLE', '')));
    $manHostname = trim((string) env('MAN_HOSTNAME', 'TheMan'));
    $currentHostname = trim((string) gethostname());

    return $role === 'man'
        || ($manHostname !== '' && strcasecmp($currentHostname, $manHostname) === 0);
};

Schedule::command('videos:sync')
    ->dailyAt('02:10')
    ->when($isManNode);

Schedule::command('barcodes:missing-report')
    ->dailyAt('18:00')
    ->when($isManNode);
