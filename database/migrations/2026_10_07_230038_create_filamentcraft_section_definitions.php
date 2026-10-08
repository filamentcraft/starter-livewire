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
        Schema::create('filamentcraft_section_definitions', function (Blueprint $table): void {
            Keys::id($table);
            Keys::foreignId($table, 'site_id')->constrained('filamentcraft_sites')->cascadeOnDelete();
            $table->string('slug');
            $table->string('name');
            $table->string('icon')->default('heroicon-o-squares-plus');
            $table->string('category')->default('custom');
            $table->string('status')->default('published');
            $table->json('schema_json');
            $table->json('layout_json');
            $table->json('presets_json')->nullable();
            $table->boolean('cacheable')->default(true);
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
            $table->unique(['site_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('filamentcraft_section_definitions');
    }
};
