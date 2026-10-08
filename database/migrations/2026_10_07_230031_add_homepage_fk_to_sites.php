<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('filamentcraft_sites', function (Blueprint $table): void {
            $table->foreign('homepage_template_id')
                ->references('id')
                ->on('filamentcraft_templates')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('filamentcraft_sites', function (Blueprint $table): void {
            $table->dropForeign(['homepage_template_id']);
        });
    }
};
