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
        Schema::create('filamentcraft_template_revisions', function (Blueprint $table): void {
            Keys::id($table);
            Keys::foreignId($table, 'template_id')->constrained('filamentcraft_templates')->cascadeOnDelete();
            Keys::foreignId($table, 'parent_revision_id')
                ->nullable()
                ->constrained('filamentcraft_template_revisions')
                ->nullOnDelete();
            Keys::userId($table)->nullable();
            $table->json('sections_json');
            $table->string('summary')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('filamentcraft_template_revisions');
    }
};
