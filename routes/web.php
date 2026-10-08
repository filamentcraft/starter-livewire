<?php

use App\Http\Middleware\EnsureTeamMembership;
use App\Models\Team;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::prefix('{current_team}')
    ->middleware(['auth', 'verified', EnsureTeamMembership::class])
    ->group(function () {
        Route::view('dashboard', 'dashboard')->name('dashboard');
        Route::livewire('website', 'pages::website.show')->name('website.show');
        Route::livewire('website/inbox', 'pages::website.inbox')->name('website.inbox');
    });

require __DIR__.'/settings.php';

Route::prefix('s')->group(fn () => Route::filamentCraftTenant(Team::class, name: 'website.public'));
