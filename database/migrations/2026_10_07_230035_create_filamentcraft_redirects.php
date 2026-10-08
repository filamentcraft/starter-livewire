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
        Schema::create('filamentcraft_redirects', function (Blueprint $table): void {
            Keys::id($table);
            Keys::foreignId($table, 'site_id')->constrained('filamentcraft_sites')->cascadeOnDelete();
            $table->string('from_path');
            $table->string('to_path');
            $table->unsignedSmallInteger('status')->default(301);
            $table->timestamps();

            $table->unique(['site_id', 'from_path']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('filamentcraft_redirects');
    }
};
