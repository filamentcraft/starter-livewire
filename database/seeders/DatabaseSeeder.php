<?php

namespace Database\Seeders;

use App\Actions\Teams\CreateTeam;
use App\Actions\Website\CreateWebsite;
use App\Models\User;
use FilamentCraft\Blueprints\StarterFamily;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(CreateTeam $createTeam, CreateWebsite $createWebsite): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $createWebsite->handle($user->currentTeam, StarterFamily::Modern);

        $studio = $createTeam->handle($user, 'Northwind Studio');

        $createWebsite->handle($studio, StarterFamily::Editorial);

        $user->switchTeam($user->personalTeam());
    }
}
