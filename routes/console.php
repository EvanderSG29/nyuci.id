<?php

use Database\Seeders\NyuciDemoSeeder;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('nyuci:seed-demo', function () {
    $summary = app(NyuciDemoSeeder::class)->seed();
    $formatStats = fn (array $stats): string => sprintf(
        '%d dibuat, %d diupdate, %d dilewati',
        $stats['created'],
        $stats['updated'],
        $stats['skipped'],
    );

    $this->info('Dummy data laundry berhasil disinkronkan.');
    $this->newLine();
    $this->comment('Ringkasan per toko:');

    foreach ($summary['stores'] as $storeSummary) {
        $this->line(sprintf(
            '%s%s | jasa %s | klien %s | laundry %s | pembayaran %s',
            $storeSummary['store_name'],
            $storeSummary['owner_email'] ? ' <'.$storeSummary['owner_email'].'>' : '',
            $formatStats($storeSummary['stats']['jasa']),
            $formatStats($storeSummary['stats']['klien']),
            $formatStats($storeSummary['stats']['laundry']),
            $formatStats($storeSummary['stats']['pembayaran']),
        ));
    }

    $this->newLine();
    $this->comment(sprintf(
        'Demo login: %s / %s',
        $summary['demo_credentials']['email'],
        $summary['demo_credentials']['password'],
    ));
})->purpose('Seed dummy laundry data for demo and existing stores');
