<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('filamentcraft_templates', function (Blueprint $table): void {
            $table->foreign('head_revision_id')
                ->references('id')
                ->on('filamentcraft_template_revisions')
                ->nullOnDelete();

            $table->foreign('published_revision_id')
                ->references('id')
                ->on('filamentcraft_template_revisions')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('filamentcraft_templates', function (Blueprint $table): void {
            $table->dropForeign(['head_revision_id']);
            $table->dropForeign(['published_revision_id']);
        });
    }
};
