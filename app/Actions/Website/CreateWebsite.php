<?php

namespace App\Actions\Website;

use App\Models\Team;
use FilamentCraft\Blueprints\StarterFamily;
use FilamentCraft\Blueprints\StarterSiteBlueprint;
use FilamentCraft\Models\Site;

class CreateWebsite
{
    /**
     * Provision the team's website from a starter style family.
     */
    public function handle(Team $team, StarterFamily $family): Site
    {
        return $team->website() ?? StarterSiteBlueprint::seed($family, owner: $team, name: $team->name, slug: $team->slug);
    }
}
