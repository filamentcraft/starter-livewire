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
        if (Schema::hasColumn('filamentcraft_custom_fonts', 'site_id')) {
            return;
        }

        Schema::table('filamentcraft_custom_fonts', function (Blueprint $table): void {
            Keys::foreignId($table, 'site_id')->nullable()->constrained('filamentcraft_sites')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('filamentcraft_custom_fonts', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('site_id');
        });
    }
};
