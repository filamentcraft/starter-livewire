<?php

declare(strict_types=1);

use FilamentCraft\Support\Hostname;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Canonicalise hosts stored before Site gained its domain/subdomain mutators.
 *
 * Rows written back then kept whatever was typed — `Example.COM`,
 * `https://shop.example.com/`, a stray trailing dot — none of which match the
 * bare lowercase host `Request::getHost()` hands the resolver, so those sites
 * silently served nothing. Idempotent: a row already canonical is skipped, and
 * a value that normalises onto a host another row already owns is left alone
 * rather than crashing the migration on the unique index (doctor reports it).
 */
return new class extends Migration
{
    public function up(): void
    {
        $seen = DB::table('filamentcraft_sites')
            ->whereNotNull('domain')
            ->pluck('domain')
            ->all();

        DB::table('filamentcraft_sites')
            ->select(['id', 'domain', 'subdomain'])
            ->orderBy('id')
            ->chunk(200, function ($rows) use (&$seen): void {
                foreach ($rows as $row) {
                    $updates = [];

                    $domain = Hostname::normalise(is_string($row->domain) ? $row->domain : null);

                    if ($domain !== $row->domain && ! in_array($domain, $seen, true)) {
                        $updates['domain'] = $domain;
                        $seen[] = $domain;
                    }

                    $subdomain = Hostname::label(is_string($row->subdomain) ? $row->subdomain : null);

                    if ($subdomain !== $row->subdomain) {
                        $updates['subdomain'] = $subdomain;
                    }

                    if ($updates !== []) {
                        DB::table('filamentcraft_sites')->where('id', $row->id)->update($updates);
                    }
                }
            });
    }
};
