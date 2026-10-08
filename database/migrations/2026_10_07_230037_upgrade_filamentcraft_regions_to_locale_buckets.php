<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Upgrade region sections_json from the legacy flat {order, sections} shape to
 * the v2 locale-bucketed payload {version: 2, locales: {<locale>: {...}}} that
 * pages already use, so regions (header/footer/announcement) become per-locale.
 *
 * Existing content is parked under each region's site default locale; other
 * locales inherit it via the renderer's default-locale fallback until
 * translated. Idempotent — rows already at version 2 are skipped.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('filamentcraft_regions')
            ->join('filamentcraft_sites', 'filamentcraft_regions.site_id', '=', 'filamentcraft_sites.id')
            ->orderBy('filamentcraft_regions.id')
            ->select([
                'filamentcraft_regions.id',
                'filamentcraft_regions.sections_json',
                'filamentcraft_sites.default_locale',
            ])
            ->chunk(200, function ($rows): void {
                foreach ($rows as $row) {
                    $data = json_decode((string) $row->sections_json, true);

                    if (! is_array($data) || ($data['version'] ?? null) === 2) {
                        continue;
                    }

                    $default = is_string($row->default_locale) && $row->default_locale !== ''
                        ? $row->default_locale
                        : 'en';

                    $bucket = [
                        'order' => is_array($data['order'] ?? null) ? array_values(array_filter($data['order'], 'is_string')) : [],
                        'sections' => is_array($data['sections'] ?? null) ? $data['sections'] : [],
                    ];

                    DB::table('filamentcraft_regions')
                        ->where('id', $row->id)
                        ->update([
                            'sections_json' => json_encode([
                                'version' => 2,
                                'locales' => [$default => $bucket],
                            ]),
                        ]);
                }
            });
    }

    public function down(): void
    {
        DB::table('filamentcraft_regions')
            ->join('filamentcraft_sites', 'filamentcraft_regions.site_id', '=', 'filamentcraft_sites.id')
            ->orderBy('filamentcraft_regions.id')
            ->select([
                'filamentcraft_regions.id',
                'filamentcraft_regions.sections_json',
                'filamentcraft_sites.default_locale',
            ])
            ->chunk(200, function ($rows): void {
                foreach ($rows as $row) {
                    $data = json_decode((string) $row->sections_json, true);

                    if (! is_array($data) || ($data['version'] ?? null) !== 2) {
                        continue;
                    }

                    $locales = is_array($data['locales'] ?? null) ? $data['locales'] : [];
                    $preferred = is_string($row->default_locale) ? ($locales[$row->default_locale] ?? null) : null;
                    $first = is_array($preferred) ? $preferred : ($locales === [] ? null : reset($locales));
                    $bucket = is_array($first) ? $first : ['order' => [], 'sections' => []];

                    DB::table('filamentcraft_regions')
                        ->where('id', $row->id)
                        ->update([
                            'sections_json' => json_encode([
                                'order' => $bucket['order'] ?? [],
                                'sections' => $bucket['sections'] ?? [],
                            ]),
                        ]);
                }
            });
    }
};
