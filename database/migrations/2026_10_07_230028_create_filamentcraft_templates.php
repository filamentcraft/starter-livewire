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
        Schema::create('filamentcraft_templates', function (Blueprint $table): void {
            Keys::id($table);
            Keys::foreignId($table, 'site_id')->constrained('filamentcraft_sites')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->index();
            $table->string('type')->default('page');
            $table->string('status')->default('draft');
            Keys::foreignId($table, 'head_revision_id')->nullable();
            Keys::foreignId($table, 'published_revision_id')->nullable();
            $table->timestamps();

            $table->unique(['site_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('filamentcraft_templates');
    }
};
