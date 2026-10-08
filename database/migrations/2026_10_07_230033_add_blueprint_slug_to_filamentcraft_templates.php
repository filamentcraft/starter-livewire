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
            $table->string('blueprint_slug', 190)->nullable()->after('status');
            $table->index('blueprint_slug', 'filamentcraft_templates_blueprint_slug_index');
        });
    }

    public function down(): void
    {
        Schema::table('filamentcraft_templates', function (Blueprint $table): void {
            $table->dropIndex('filamentcraft_templates_blueprint_slug_index');
            $table->dropColumn('blueprint_slug');
        });
    }
};
