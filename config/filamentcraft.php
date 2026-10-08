<?php

declare(strict_types=1);
use FilamentCraft\Models\AiUsage;
use FilamentCraft\Models\CustomFont;
use FilamentCraft\Models\Media;
use FilamentCraft\Models\Redirect;
use FilamentCraft\Models\Region;
use FilamentCraft\Models\SectionDefinition;
use FilamentCraft\Models\Site;
use FilamentCraft\Models\SiteSnapshot;
use FilamentCraft\Models\Submission;
use FilamentCraft\Models\Template;
use FilamentCraft\Models\TemplateRevision;
use FilamentCraft\Models\Theme;

return [

    /*
    |--------------------------------------------------------------------------
    | Public Routes
    |--------------------------------------------------------------------------
    |
    | When true, the catch-all public site route is registered so the plugin
    | serves public-facing pages. Leave false when the host application owns
    | its own front-end and only wants the admin editor.
    |
    */

    'public_routes' => false,

    /*
    |--------------------------------------------------------------------------
    | Built-in Sections
    |--------------------------------------------------------------------------
    |
    | When true, FilamentCraft auto-registers its bundled 32-section library
    | (marketing, content, and commerce) on boot. Disable if the consumer
    | ships their own section library and does not want the defaults.
    |
    */

    'builtin_sections_enabled' => true,

    /*
    |--------------------------------------------------------------------------
    | Tenancy
    |--------------------------------------------------------------------------
    |
    | `mode`   — one of: auto, none, filament, owner.
    | `owner_model` — FQCN of the host-app model that owns Sites (polymorphic).
    | `single_site_id` — force every request to resolve to this site.
    |
    */

    'tenancy' => [
        'mode' => 'auto',
        'owner_model' => null,
        'single_site_id' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Keys
    |--------------------------------------------------------------------------
    |
    | `key_type` — primary keys of every FilamentCraft table: int, uuid or ulid.
    | Migrations read it, so choose before the first `migrate`; changing it
    | later does not convert existing tables (`filamentcraft:doctor` flags it).
    | `owner_key_type` / `user_key_type` — type of `sites.owner_id` and the
    | `user_id` columns. null detects it from your tenant and User models.
    |
    */

    'database' => [
        'key_type' => 'int',
        'owner_key_type' => null,
        'user_key_type' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Models
    |--------------------------------------------------------------------------
    |
    | Swap any FilamentCraft model for your own subclass of it to add traits,
    | casts, relations or observers. Every query, relation, resource and route
    | binding in the package then uses your class.
    | `php artisan make:filamentcraft-model Site` scaffolds one and registers it here.
    |
    */

    'models' => [
        'site' => Site::class,
        'template' => Template::class,
        'template_revision' => TemplateRevision::class,
        'theme' => Theme::class,
        'region' => Region::class,
        'media' => Media::class,
        'custom_font' => CustomFont::class,
        'redirect' => Redirect::class,
        'section_definition' => SectionDefinition::class,
        'ai_usage' => AiUsage::class,
        'submission' => Submission::class,
        'site_snapshot' => SiteSnapshot::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Domain
    |--------------------------------------------------------------------------
    |
    | `primary` — the parent host sites hang off. Inbound `{subdomain}.{primary}`
    | requests resolve to that site, and outbound public URLs (sitemaps, og:url,
    | the editor's "open live" pill) are built from it when a Site has no domain
    | of its own. The host itself is reserved. Defaults to the APP_URL host.
    |
    */

    'domain' => [
        'primary' => parse_url((string) env('APP_URL', 'http://localhost'), PHP_URL_HOST),
    ],

    /*
    |--------------------------------------------------------------------------
    | SEO
    |--------------------------------------------------------------------------
    |
    | The generated public files. Each is registered only when `public_routes`
    | is on (the host serves them otherwise) and can be turned off here even
    | then. `indexnow` pings Bing/Yandex/Naver when a page is published; Google
    | does not consume IndexNow, so this complements — never replaces — the
    | sitemap. Per-site AI-crawler policy and the OG/meta defaults live on each
    | Site (editor → Site settings → Search & AI), not here.
    |
    */

    'seo' => [
        'sitemap' => true,
        'robots_txt' => true,
        'llms_txt' => true,
        'indexnow' => [
            'enabled' => true,
            'endpoint' => 'https://api.indexnow.org/indexnow',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Localization
    |--------------------------------------------------------------------------
    |
    | `remember` — keep a visitor's `?locale=` choice per site in the session,
    | so plain links (navigation, cards, host pages) stay in that language.
    | Language switchers then name the default locale explicitly (`?locale=en`)
    | so switching back to it wins; hreflang and canonical URLs are unchanged.
    | Needs the `web` middleware group (a session); without one it is a no-op.
    |
    */

    'localization' => [
        'remember' => env('FILAMENTCRAFT_REMEMBER_LOCALE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Regions (pinned site chrome)
    |--------------------------------------------------------------------------
    |
    | Site-wide section groups rendered around every template and pinned in
    | the editor sidebar. `announcement` and `header` are placed above the
    | page body (in the order listed here); `footer` is placed below it.
    | Remove a region to hide it from both the public shell and the editor;
    | add `announcement` (before `header`) for a bar above the header.
    |
    | Allowed values: announcement, header, footer.
    |
    */

    'regions' => [
        'enabled' => ['header', 'footer'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Editor
    |--------------------------------------------------------------------------
    |
    | `enabled`      — master switch; false hides the editor page entirely
    |                  (useful per environment via FILAMENTCRAFT_EDITOR_ENABLED).
    | `autosave_ms`  — debounce (ms) before an edit is auto-saved to the draft.
    | `undo_depth`   — undo steps retained per template in the ring buffer.
    | `control_size` — density of the settings-panel enum/toggle controls:
    |                  `compact`, `comfortable` (default), or `spacious`.
    | `devices`      — preview viewport dimensions (px) per device shell; each
    |                  entry is a `['width' => …, 'height' => …]` pair.
    |
    */

    'editor' => [
        'enabled' => env('FILAMENTCRAFT_EDITOR_ENABLED', true),
        'autosave_ms' => 3000,
        'undo_depth' => 50,
        'control_size' => 'comfortable',
        'devices' => [
            'desktop' => ['width' => 1440, 'height' => 900],
            'tablet' => ['width' => 820, 'height' => 1180],
            'mobile' => ['width' => 390, 'height' => 844],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Assistant
    |--------------------------------------------------------------------------
    |
    | The in-editor assistant (generate a page or site from a brief, ask for
    | changes in plain language, rewrite a section). It runs on the optional
    | `laravel/ai` package — install it and set the provider key and the
    | sparkle button appears in the editor. Nothing here is enforced when the
    | package is absent.
    |
    | `provider`     — the `laravel/ai` provider name (`config/ai.php`).
    | `models`       — the model behind each tier. Editors only ever see the
    |                  tier names (fast / balanced / advanced), never a model id.
    | `default_tier` / `allow_tier_choice` — the tier used when editors can't
    |                  (or don't) pick one.
    | `provider_options` — merged into every request's generation config
    |                  (Gemini: `thinkingConfig` keeps reasoning tokens down).
    | `site_keys`    — let each site store its own API key (encrypted on the
    |                  Site row, scoped by tenant). `required` refuses the host
    |                  key as a fallback so every site pays for its own usage.
    | `usage`        — `track` writes one row per call to
    |                  `filamentcraft_ai_usages`; `show` surfaces the month's
    |                  totals to editors inside the assistant.
    | `limits`       — caps on what one brief may generate, how many model
    |                  calls one user may start per minute, and an optional
    |                  monthly token budget per site (null = unlimited).
    | `timeout`      — seconds of model time one web request may spend, retries
    |                  included. Keep it under your web server's timeout.
    | `prompts`      — your say in what the assistant is told. `voice` replaces
    |                  the built-in copywriting style; `instructions` adds rules
    |                  for every task (`*`) or one (plan_site, plan_page,
    |                  plan_design, fill_sections, command). Closures go through
    |                  the plugin: aiInstructions(), aiVoice(), aiPromptUsing().
    |
    */

    'ai' => [
        'enabled' => env('FILAMENTCRAFT_AI_ENABLED', true),
        'provider' => env('FILAMENTCRAFT_AI_PROVIDER', 'gemini'),
        'models' => [
            'fast' => env('FILAMENTCRAFT_AI_MODEL_FAST', 'gemini-3.5-flash-lite'),
            'balanced' => env('FILAMENTCRAFT_AI_MODEL_BALANCED', 'gemini-3.6-flash'),
            'advanced' => env('FILAMENTCRAFT_AI_MODEL_ADVANCED', 'gemini-pro-latest'),
        ],
        'default_tier' => env('FILAMENTCRAFT_AI_DEFAULT_TIER', 'fast'),
        'allow_tier_choice' => true,
        'provider_options' => [
            'thinkingConfig' => ['thinkingLevel' => 'low'],
        ],
        'site_keys' => [
            'enabled' => env('FILAMENTCRAFT_AI_SITE_KEYS', false),
            'required' => env('FILAMENTCRAFT_AI_SITE_KEYS_REQUIRED', false),
        ],
        'usage' => [
            'track' => true,
            'show' => true,
        ],
        'limits' => [
            'sections_per_page' => 9,
            'pages_per_site' => 6,
            'requests_per_minute' => 30,
            'monthly_tokens' => env('FILAMENTCRAFT_AI_MONTHLY_TOKENS'),
        ],
        'timeout' => 45,
        'prompts' => [
            'voice' => null,
            'instructions' => [
                // '*' => 'Write British English and never mention prices.',
                // 'plan_design' => 'Stay within our brand blues.',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Form submissions
    |--------------------------------------------------------------------------
    |
    | What happens when a visitor sends the built-in Contact or Newsletter
    | section. `ContactFormSubmitted` / `NewsletterSubscribed` always fire, so
    | a listener of your own (CRM, mailing list) keeps working either way.
    |
    | `store`    — write every submission to `filamentcraft_submissions` and
    |              list it in the panel's Inbox. Turn off when your own
    |              listener is the only destination.
    | `notify`   — an address, a comma-separated list or an array that gets a
    |              plain mail per submission (queued when a queue is set up).
    | `throttle` — submissions one visitor may send per minute, per form type.
    |              null or 0 turns the limit off.
    | `honeypot` — a hidden field bots fill in. A filled one is shown the
    |              success state and dropped.
    | `inbox`    — register the Inbox resource in the panel.
    |
    */

    'forms' => [
        'store' => env('FILAMENTCRAFT_FORMS_STORE', true),
        'notify' => env('FILAMENTCRAFT_FORMS_NOTIFY'),
        'throttle' => 5,
        'honeypot' => true,
        'inbox' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    |
    | Fragment cache for rendered sections. `store` = null uses the default
    | application cache store; Redis is recommended in production because
    | only tagged stores support the per-site invalidation on publish.
    | `ttl` is the fragment lifetime in seconds; `tag_prefix` namespaces the
    | cache keys so several apps can share one Redis instance.
    |
    */

    'cache' => [
        'enabled' => true,
        'store' => null,
        'ttl' => 3600,
        'tag_prefix' => 'fc',
    ],

    /*
    |--------------------------------------------------------------------------
    | License
    |--------------------------------------------------------------------------
    |
    | `key` — the license key from your FilamentCraft purchase (the same key
    | you use as the Composer password for packages.filamentcraft.dev).
    |
    | `soft_degrade` — when true (default), an install without a key keeps
    | every feature working and renders a small "Built with FilamentCraft"
    | link on public pages. Nothing is ever blocked. Set
    | `FILAMENTCRAFT_SOFT_DEGRADE=false` to silence the link per environment.
    |
    | `public_key` — the Ed25519 key that signed your license. Keys carry their
    | tier, so `filamentcraft:doctor` can report which one you hold without the
    | package ever contacting a server. Nothing is enforced from it: the site
    | count your tier covers is a license term, not a technical limit.
    |
    */

    'license' => [
        'key' => env('FILAMENTCRAFT_LICENSE_KEY'),
        'soft_degrade' => env('FILAMENTCRAFT_SOFT_DEGRADE', true),
        'public_key' => env('FILAMENTCRAFT_LICENSE_PUBLIC_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Commerce
    |--------------------------------------------------------------------------
    |
    | `card_view` — the Blade view the product listing, carousel and related-
    | products rail render each product with. Point it at your own view to keep
    | the grid, filters, pagination and empty state while showing whatever your
    | catalog carries; read host-defined fields off `$product->meta('…')`.
    | Null keeps the built-in card. A missing view falls back to it too.
    |
    | `catalogs` — extra `Catalog` classes (courses, events…) the commerce
    | sections can be pointed at, beside the bound Storefront's `products`.
    |
    | `legacy_shipping_signature` — still accept the pre-1.41 shipping signature
    | from a published checkout view that lacks the `site`/`shipping_expires`
    | fields. Turn it off once your checkout view is updated; it goes in 2.0.
    |
    */

    'commerce' => [
        'card_view' => null,
        'catalogs' => [],
        'legacy_shipping_signature' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Rendering
    |--------------------------------------------------------------------------
    |
    | `show_errors` — a section that throws is reported and dropped from the
    | published page, so one broken block never takes the page down. Turn this
    | on to render the failure card publicly instead: the hole becomes visible
    | on staging rather than showing up as content that quietly went missing.
    | Leave it off in production — the card prints the exception message.
    |
    */

    'rendering' => [
        'show_errors' => env('FILAMENTCRAFT_SHOW_RENDER_ERRORS', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    |
    | `viewport` — the `<meta name="viewport">` content of every page shell
    | (built templates and `<x-filamentcraft::layout>`). `viewport-fit=cover`
    | lets the safe-area insets the site CSS reads work on notched phones and
    | home-screen installs. Letters, digits, `=`, `,`, `.`, `-` and spaces only;
    | anything else falls back to the default. The layout component's
    | `viewport` prop overrides this per page.
    |
    */

    'layout' => [
        'viewport' => env('FILAMENTCRAFT_VIEWPORT', 'width=device-width, initial-scale=1, viewport-fit=cover'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Assets
    |--------------------------------------------------------------------------
    */

    'assets' => [
        /*
         | URLs injected into every rendered template's iframe. The package
         | already ships its own Tailwind bundle for the builtin sections;
         | hosts that have their own theme CSS / Vite manifest add it here.
         | Programmatic equivalent: `FilamentCraftPlugin::make()->stylesheet(...)`
         */
        'site_css_enabled' => true,
        'site_js_enabled' => true,
        'extra_styles' => [],
        'extra_scripts' => [],
        'vite_builds' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Uploads
    |--------------------------------------------------------------------------
    |
    | Defaults applied to every Image setting that doesn't override them.
    | The disk MUST be publicly readable (run `php artisan storage:link` for
    | the default `public` disk) so the renderer's `Storage::disk()->url($path)`
    | call resolves to a working public URL.
    |
    */

    'uploads' => [
        'disk' => env('FILAMENTCRAFT_UPLOADS_DISK', 'public'),
        'directory' => env('FILAMENTCRAFT_UPLOADS_DIR', 'filamentcraft/uploads'),
        'visibility' => env('FILAMENTCRAFT_UPLOADS_VISIBILITY', 'public'),
        'max_size_kb' => (int) env('FILAMENTCRAFT_UPLOADS_MAX_KB', 20480),
    ],

    /*
    |--------------------------------------------------------------------------
    | Maps
    |--------------------------------------------------------------------------
    |
    | The raster tile source behind the Locations section's map. The default
    | needs no account and no API key, so a map appears the moment an editor
    | enters coordinates.
    |
    | `tile_url` — an `{z}/{x}/{y}` template. OpenStreetMap's tile policy asks
    |              that busy sites use their own provider (MapTiler, Stadia,
    |              Thunderforest, a self-hosted renderer, …) — swap the template
    |              and the attribution and nothing else changes. Set it empty to
    |              turn tile maps off site-wide.
    | `attribution` / `attribution_url` — shown on every map. Most providers
    |              require this; check your provider's terms before changing it.
    | `max_zoom` — the deepest zoom the provider serves.
    |
    */

    'maps' => [
        'tile_url' => env('FILAMENTCRAFT_MAP_TILE_URL', 'https://tile.openstreetmap.org/{z}/{x}/{y}.png'),
        'attribution' => env('FILAMENTCRAFT_MAP_ATTRIBUTION', '© OpenStreetMap contributors'),
        'attribution_url' => env('FILAMENTCRAFT_MAP_ATTRIBUTION_URL', 'https://www.openstreetmap.org/copyright'),
        'max_zoom' => (int) env('FILAMENTCRAFT_MAP_MAX_ZOOM', 19),
    ],

    /*
    |--------------------------------------------------------------------------
    | Sections
    |--------------------------------------------------------------------------
    |
    | `paths`    — filesystem paths auto-discovered for section classes.
    | `register` — explicit list of section class names to register.
    | `allowed`  — optional allow-list for the Add Section catalog.
    | `disabled` — section slugs hidden from the Add Section catalog.
    |
    */

    'sections' => [
        'paths' => [],
        'register' => [],
        'allowed' => null,
        'disabled' => [],

        /*
         * Add a per-section typography panel (heading + body font overrides) to
         * every section's settings. Each override emits inline font CSS vars on
         * the section wrapper; left on "Theme default" it inherits site fonts.
         */
        'typography_overrides' => true,

        /*
         * Add a per-section "Look" panel (card style, image frame, arrangement,
         * corners, button weight) to every section's settings. Each knob emits
         * one class on the section wrapper and re-skins the built-in primitives
         * from CSS alone. Every knob defaults to "Theme default", so turning
         * this on changes nothing until a knob is picked.
         */
        'look_variants' => true,

        /*
         * Add a per-section "Schedule" panel: a show-from / show-until window
         * the published page honours (campaigns, seasonal banners). The editor
         * canvas always shows every section. Times use Filament's timezone
         * (FilamentTimezone::set()) and are stored in the app timezone.
         */
        'scheduling' => true,

        /*
         * Give every section that uses Alpine an `x-data` root on its wrapper.
         * Alpine only initializes subtrees under an `x-data` scope, so without
         * this a BladeSection authored with raw Alpine directives is silently
         * inert on the live site. Disable only if you root your sections by hand.
         */
        'alpine_root' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Section Definitions (no-code custom sections)
    |--------------------------------------------------------------------------
    |
    | Editor users can build their own section types visually; each definition
    | is stored per site and rendered from a token-safe element tree. The
    | allow_custom_code flag unlocks raw <script> output for the html element —
    | it is a host-developer decision and must never be exposed to tenants.
    |
    */

    'section_definitions' => [
        'enabled' => env('FILAMENTCRAFT_SECTION_DEFINITIONS', true),
        'max_per_site' => 50,
        'max_nodes' => 100,
        'allow_custom_code' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Blueprints
    |--------------------------------------------------------------------------
    |
    | `auto_seed` — when true, all registered blueprints are seeded into a
    | newly created Site automatically via the SiteCreated event listener.
    | Set to false to seed manually using `php artisan filamentcraft:seed-blueprints`.
    |
    */

    'blueprints' => [
        'auto_seed' => env('FILAMENTCRAFT_AUTO_SEED_BLUEPRINTS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Starter sites
    |--------------------------------------------------------------------------
    |
    | The style family a freshly provisioned starter site uses when none is
    | given — one of: modern, editorial, bold, elegant. Drives the default for
    | `StarterSiteBlueprint::seed()`, the `filamentcraft:starter` command, and
    | the StarterSiteSeeder. Each family pulls its matching preset from every
    | built-in section, so the whole site lands cohesive and on-brand.
    |
    */

    'starter' => [
        'family' => env('FILAMENTCRAFT_STARTER_FAMILY', 'modern'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Themes
    |--------------------------------------------------------------------------
    */

    'themes' => [
        /*
         * When true, the package-shipped Studio theme auto-registers on every
         * request (panel + preview + public routes). Disable when the host
         * ships its own theme as the only choice.
         */
        'builtin_enabled' => true,

        /*
         * Theme classes to register alongside Studio. Each must implement
         * ThemeContract — see src/Theming/Themes/StudioTheme.php for the
         * canonical example. Mirrors how `sections.register` works.
         */
        'register' => [
            // App\Themes\AcmeTheme::class,
        ],

        /*
         * Ready-made looks the editor's theme selector offers: each one sets
         * the site design and can also bring its own pages, header and footer.
         * Classes in app/Themes/Presets are discovered automatically; list
         * extra directories in `preset_paths` or classes in `presets`.
         * `php artisan make:filamentcraft-theme-preset` scaffolds one.
         */
        'presets' => [
            // App\Themes\Presets\HarborPreset::class,
        ],

        'preset_paths' => [],

        'builtin_presets' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Brand kit (design-system lock)
    |--------------------------------------------------------------------------
    |
    | An optional agency-style lock enforced in the editor. Every constraint is
    | authoring-time only — values already on a site keep rendering. Set these
    | fluently via `FilamentCraftPlugin::make()->brandKit(...)`; the fluent call
    | mirrors here so the request-time renderer reads the same lock.
    |
    | `fonts`          — allow-list of catalog slugs the font pickers accept
    |                    (e.g. ['inter', 'system-serif']). null = unrestricted.
    | `palette`        — approved colors as a `'#hex' => 'Label'` map (or a flat
    |                    list of hex strings). null = free-form color pickers.
    | `palette_locked` — when true the swatch grid offers no custom-color escape.
    | `hidden_schemes` — built-in color-scheme slugs hidden from the pickers.
    |
    */

    'brand' => [
        'fonts' => null,
        'palette' => null,
        'palette_locked' => true,
        'hidden_schemes' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Fonts
    |--------------------------------------------------------------------------
    |
    | `register` — brand fonts added to the picker catalog on top of the Bunny
    | roster. Each entry is built with `FilamentCraft\Support\BrandFont` and
    | registered via `FilamentCraftPlugin::make()->registerFont(...)`, which
    | mirrors the entry here. Stack-only entries need no files (any web-safe or
    | system font); entries with faces emit @font-face for self-hosted files.
    |
    */

    'fonts' => [
        'register' => [],
    ],

];
