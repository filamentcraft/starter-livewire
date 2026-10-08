<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The theme's "Default scheme" picker lives in the editor's global settings but
 * used to persist into the template draft, so each page carried its own scheme.
 * It is now site-wide (`sites.settings_json['color_scheme']`), and this backfills
 * the selection a live site already made so its look survives the upgrade.
 *
 * The winning value is the home page's, falling back to the first template that
 * has one. Idempotent — a site that already carries the key is skipped, and the
 * package default ('modern') is left unstamped so future default changes flow.
 */
return new class extends Migration
{
    private const DEFAULT_SLUG = 'modern';

    public function up(): void
    {
        DB::table('filamentcraft_sites')
            ->orderBy('id')
            ->select(['id', 'homepage_template_id', 'settings_json'])
            ->chunk(200, function ($sites): void {
                foreach ($sites as $site) {
                    $settings = json_decode((string) $site->settings_json, true);
                    $settings = is_array($settings) ? $settings : [];

                    if (array_key_exists('color_scheme', $settings)) {
                        continue;
                    }

                    $slug = $this->schemeForSite($site->id, $site->homepage_template_id);

                    if ($slug === null || $slug === self::DEFAULT_SLUG) {
                        continue;
                    }

                    $settings['color_scheme'] = $slug;

                    DB::table('filamentcraft_sites')
                        ->where('id', $site->id)
                        ->update(['settings_json' => json_encode($settings)]);
                }
            });
    }

    /**
     * Promoted values are indistinguishable from ones the operator picked after
     * the upgrade, so dropping the key here would silently restyle a live site.
     */
    public function down(): void {}

    private function schemeForSite(int|string $siteId, mixed $homepageTemplateId): ?string
    {
        $templates = DB::table('filamentcraft_templates')
            ->where('site_id', $siteId)
            ->orderBy('id')
            ->get(['id', 'slug', 'published_revision_id', 'head_revision_id']);

        $home = $templates->first(fn ($template): bool => $homepageTemplateId !== null
            ? (string) $template->id === (string) $homepageTemplateId
            : $template->slug === 'home');

        $ordered = $home === null ? $templates : $templates->prepend($home);

        foreach ($ordered as $template) {
            foreach ([$template->published_revision_id, $template->head_revision_id] as $revisionId) {
                $slug = $revisionId === null ? null : $this->schemeInRevision($revisionId);

                if ($slug !== null) {
                    return $slug;
                }
            }
        }

        return null;
    }

    private function schemeInRevision(int|string $revisionId): ?string
    {
        $revision = DB::table('filamentcraft_template_revisions')
            ->where('id', $revisionId)
            ->value('sections_json');

        $payload = json_decode((string) $revision, true);
        $settings = is_array($payload) && is_array($payload['settings'] ?? null) ? $payload['settings'] : [];
        $slug = $settings['color_scheme'] ?? null;

        return is_string($slug) && $slug !== '' ? $slug : null;
    }
};
