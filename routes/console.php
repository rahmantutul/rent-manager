<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
use App\Services\OwnerBootstrap;

Artisan::command('owner:bootstrap {owner}', function (OwnerBootstrap $bootstrap) {
    $bootstrap->run((int) $this->argument('owner'));
    $this->info('Owner bootstrapped.');
});
