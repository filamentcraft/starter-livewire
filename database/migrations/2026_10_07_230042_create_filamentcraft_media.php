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
        Schema::create('filamentcraft_media', function (Blueprint $table): void {
            Keys::id($table);
            Keys::foreignId($table, 'site_id')->constrained('filamentcraft_sites')->cascadeOnDelete();
            $table->string('disk');
            $table->string('path', 500);
            $table->string('name');
            $table->string('title')->nullable();
            $table->string('alt')->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->string('mime')->nullable();
            $table->timestamps();

            $table->unique(['site_id', 'path']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('filamentcraft_media');
    }
};
