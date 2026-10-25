<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
use App\Services\InvoiceGenerationService;
use App\Services\OwnerBootstrap;

Artisan::command('owner:bootstrap {owner}', function (OwnerBootstrap $bootstrap) {
    $bootstrap->run((int) $this->argument('owner'));
    $this->info('Owner bootstrapped.');
});

Artisan::command('rent:generate {--date=} {--lease=} {--dry-run}', function (InvoiceGenerationService $service) {
    $result = $service->generate($this->option('date'), $this->option('lease') ? (int) $this->option('lease') : null, (bool) $this->option('dry-run'));
    foreach ($result['created'] as $item) {
        $this->line($item);
    }
    foreach ($result['errors'] as $id => $error) {
        $this->error("Lease {$id}: {$error}");
    }
});
