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
        Schema::create('filamentcraft_regions', function (Blueprint $table): void {
            Keys::id($table);
            Keys::foreignId($table, 'site_id')->constrained('filamentcraft_sites')->cascadeOnDelete();
            $table->string('name');
            $table->json('sections_json');
            $table->timestamps();

            $table->unique(['site_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('filamentcraft_regions');
    }
};
