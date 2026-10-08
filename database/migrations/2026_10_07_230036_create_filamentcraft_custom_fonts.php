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
        Schema::create('filamentcraft_custom_fonts', function (Blueprint $table): void {
            Keys::id($table);
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('category')->default('sans');
            $table->string('stack')->nullable();
            $table->json('faces_json');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('filamentcraft_custom_fonts');
    }
};
