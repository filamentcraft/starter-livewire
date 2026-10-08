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
        Schema::create('filamentcraft_site_snapshots', function (Blueprint $table): void {
            Keys::id($table);
            Keys::foreignId($table, 'site_id')->constrained('filamentcraft_sites')->cascadeOnDelete();
            $table->string('label');
            $table->string('preset_slug')->nullable();
            $table->string('mode', 16);
            $table->json('payload');
            $table->timestamp('restored_at')->nullable();
            $table->timestamps();

            $table->index(['site_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('filamentcraft_site_snapshots');
    }
};
