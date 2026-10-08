<?php

declare(strict_types=1);

use FilamentCraft\Database\Keys;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('filamentcraft_sites', function (Blueprint $table): void {
            Keys::id($table);
            // Explicit owner columns rather than nullableMorphs(): the composite
            // (owner_type, owner_id, slug) index below already covers owner-pair
            // lookups as a left prefix, so nullableMorphs' own (owner_type,
            // owner_id) index would be redundant.
            $table->string('owner_type')->nullable();
            Keys::ownerId($table)->nullable();
            Keys::foreignId($table, 'theme_id')->constrained('filamentcraft_themes');
            $table->string('name');
            $table->string('slug')->index();
            $table->string('domain')->nullable()->unique();
            $table->string('subdomain')->nullable()->unique();
            $table->string('default_locale', 12)->default('en');
            $table->json('locales')->nullable();
            $table->string('status')->default('draft');
            Keys::foreignId($table, 'homepage_template_id')->nullable();
            $table->json('settings_json')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['owner_type', 'owner_id', 'slug']);
            $table->index('homepage_template_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('filamentcraft_sites');
    }
};
