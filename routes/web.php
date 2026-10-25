<?php

use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BuildingController;
use App\Http\Controllers\InstallController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\LeaseController;
use App\Http\Controllers\LookupController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\UnitController;
use Illuminate\Support\Facades\Route;

Route::get('/install', [InstallController::class, 'show'])->name('install.show');
Route::post('/install', [InstallController::class, 'store'])->middleware('throttle:10,1')->name('install.store');
Route::middleware('installed')->group(function () {
    Route::get('/', fn () => redirect()->route(auth()->user()?->role->landingRoute() ?? 'login'))->name('home');
    Route::get('/login', [AuthController::class, 'show'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
    Route::get('/branding/{scope}', [SettingsController::class, 'logo'])->whereIn('scope', ['global', 'owner'])->name('branding.logo');
    Route::middleware('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
        Route::post('/locale', [SettingsController::class, 'locale'])->name('locale.update');
        Route::prefix('admin')->name('admin.')->middleware('role:owner,manager,accountant')->group(function () {
            Route::view('/', 'dashboard', ['area' => 'admin'])->name('dashboard');
            Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
            Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
            Route::get('/lookups/{type}', [LookupController::class, 'index'])->name('lookups.index');
            Route::post('/lookups/{type}', [LookupController::class, 'store'])->name('lookups.store');
            Route::delete('/lookups/{type}/{id}', [LookupController::class, 'destroy'])->name('lookups.destroy');
            Route::resource('tenants', TenantController::class);
            Route::post('tenants/{tenant}/document', [TenantController::class, 'document'])->name('tenants.document');
            Route::resource('leases', LeaseController::class)->only(['index', 'create', 'store', 'show']);
            Route::post('leases/{lease}/activate', [LeaseController::class, 'activate'])->name('leases.activate');
            Route::post('leases/{lease}/change-rent', [LeaseController::class, 'changeRent'])->name('leases.change-rent');
            Route::post('leases/{lease}/terminate', [LeaseController::class, 'terminate'])->name('leases.terminate');
            Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
            Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
            Route::resource('properties', PropertyController::class)->except(['show']);
            Route::get('properties/{property}', [PropertyController::class, 'show'])->name('properties.show');
            Route::get('properties/{property}/buildings/create', [BuildingController::class, 'create'])->name('buildings.create');
            Route::post('properties/{property}/buildings', [BuildingController::class, 'store'])->name('buildings.store');
            Route::get('buildings/{building}/edit', [BuildingController::class, 'edit'])->name('buildings.edit');
            Route::put('buildings/{building}', [BuildingController::class, 'update'])->name('buildings.update');
            Route::delete('buildings/{building}', [BuildingController::class, 'destroy'])->name('buildings.destroy');
            Route::get('buildings/{building}/units', [UnitController::class, 'index'])->name('buildings.units.index');
            Route::get('buildings/{building}/units/create', [UnitController::class, 'create'])->name('buildings.units.create');
            Route::post('buildings/{building}/units', [UnitController::class, 'store'])->name('buildings.units.store');
            Route::get('buildings/{building}/units/bulk', [UnitController::class, 'bulk'])->name('buildings.units.bulk');
            Route::post('buildings/{building}/units/bulk', [UnitController::class, 'generate'])->name('buildings.units.generate');
            Route::get('units/{unit}/edit', [UnitController::class, 'edit'])->name('units.edit');
            Route::put('units/{unit}', [UnitController::class, 'update'])->name('units.update');
            Route::delete('units/{unit}', [UnitController::class, 'destroy'])->name('units.destroy');
        });
        Route::prefix('super-admin')->name('super-admin.')->middleware('role:super_admin')->group(function () {
            Route::view('/', 'dashboard', ['area' => 'super_admin'])->name('dashboard');
            Route::get('/branding', [SettingsController::class, 'branding'])->name('branding.edit');
            Route::put('/branding', [SettingsController::class, 'updateBranding'])->name('branding.update');
        });
        Route::view('/portal', 'dashboard', ['area' => 'portal'])->middleware('role:tenant')->name('portal.dashboard');
        Route::get('/attachments/{attachment}', [AttachmentController::class, 'show'])->name('attachments.show');
    });
});
