<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Heal templates that are `published` with no `published_revision_id`: the
     * public page 404s while every admin surface reports the page as live.
     * Reachable on installs that gained the revision columns after their rows
     * were already published, and on any host that deleted a live revision.
     */
    public function up(): void
    {
        DB::table('filamentcraft_templates')
            ->where('status', 'published')
            ->whereNull('published_revision_id')
            ->whereNotNull('head_revision_id')
            ->update(['published_revision_id' => DB::raw('head_revision_id')]);

        DB::table('filamentcraft_templates')
            ->where('status', 'published')
            ->whereNull('published_revision_id')
            ->update(['status' => 'draft']);
    }

    public function down(): void {}
};
