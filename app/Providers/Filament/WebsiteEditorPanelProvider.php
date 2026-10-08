<?php

namespace App\Providers\Filament;

use App\Models\Team;
use FilamentCraft\Editor\EditorPanelProvider;
use FilamentCraft\FilamentCraftPlugin;
use FilamentCraft\Models\Template;

class WebsiteEditorPanelProvider extends EditorPanelProvider
{
    protected function path(): string
    {
        return 'builder';
    }

    protected function ownerModel(): ?string
    {
        return Team::class;
    }

    protected function publicRouteName(): string
    {
        return 'website.public';
    }

    protected function exitUrl(Template $template): ?string
    {
        $team = $template->site?->owner;

        return $team instanceof Team ? route('website.show', ['current_team' => $team]) : null;
    }

    protected function configureEditor(FilamentCraftPlugin $plugin): FilamentCraftPlugin
    {
        return $plugin->allowSiteCreation(false);
    }
}
