<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\WebsiteEditorPanelProvider;
use App\Providers\FortifyServiceProvider;

return [
    AppServiceProvider::class,
    WebsiteEditorPanelProvider::class,
    FortifyServiceProvider::class,
];
