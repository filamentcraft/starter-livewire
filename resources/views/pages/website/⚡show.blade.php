<?php

use App\Actions\Website\CreateWebsite;
use App\Models\Team;
use FilamentCraft\Blueprints\StarterFamily;
use FilamentCraft\Editor\EditorUrl;
use FilamentCraft\Enums\RegionName;
use FilamentCraft\Enums\SiteStatus;
use FilamentCraft\Enums\TemplateStatus;
use FilamentCraft\Models\Submission;
use FilamentCraft\Models\Template;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Website')] class extends Component {
    public Team $team;

    public string $family = '';

    public function mount(): void
    {
        $this->team = Auth::user()->currentTeam;

        Gate::authorize('manageWebsite', $this->team);

        $this->family = StarterFamily::default()->value;
    }

    public function createWebsite(CreateWebsite $createWebsite): void
    {
        Gate::authorize('manageWebsite', $this->team);

        $validated = $this->validate([
            'family' => ['required', Rule::enum(StarterFamily::class)],
        ]);

        $createWebsite->handle($this->team, StarterFamily::from($validated['family']));

        unset($this->website);

        Flux::toast(variant: 'success', text: __('Website created.'));
    }

    public function setLive(bool $live): void
    {
        Gate::authorize('manageWebsite', $this->team);

        $site = $this->team->website();

        abort_if($site === null, 404);

        if ($live) {
            $site->markLive();

            Flux::toast(variant: 'success', text: __('Website is live.'));
        } else {
            $site->archive();

            Flux::toast(variant: 'success', text: __('Website is offline.'));
        }

        unset($this->website);
    }

    /**
     * @return array<int, array{value: string, label: string, description: string}>
     */
    #[Computed]
    public function families(): array
    {
        return collect(StarterFamily::cases())->map(fn (StarterFamily $family) => [
            'value' => $family->value,
            'label' => $family->getLabel(),
            'description' => $family->description(),
        ])->all();
    }

    /**
     * @return array<string, mixed>|null
     */
    #[Computed]
    public function website(): ?array
    {
        $site = $this->team->website();

        if ($site === null) {
            return null;
        }

        $isLive = $site->status === SiteStatus::Live;

        $pages = Template::query()
            ->forSite($site->getKey())
            ->orderByRaw('id = ? desc', [$site->homepage_template_id])
            ->orderBy('name')
            ->get()
            ->map(function (Template $template) use ($site, $isLive): array {
                $isHomepage = $template->getKey() === $site->homepage_template_id;

                return [
                    'id' => $template->getKey(),
                    'name' => $template->name,
                    'path' => $isHomepage ? '/' : '/'.$template->slug,
                    'isHomepage' => $isHomepage,
                    'isPublished' => $template->status === TemplateStatus::Published,
                    'updatedAt' => $template->updated_at?->diffForHumans(),
                    'editUrl' => EditorUrl::to($template->setRelation('site', $site)),
                    'url' => $isLive ? $template->publicUrl() : null,
                ];
            });

        return [
            'name' => $site->name,
            'statusLabel' => $site->status->getLabel(),
            'isLive' => $isLive,
            'url' => route('website.public', ['tenantSlug' => $this->team->slug]),
            'unreadSubmissions' => Submission::query()->forSite($site)->unread()->count(),
            'regions' => collect([RegionName::Announcement, RegionName::Header, RegionName::Footer])
                ->filter(fn (RegionName $region) => in_array($region->value, config('filamentcraft.regions.enabled', []), true))
                ->map(fn (RegionName $region) => [
                    'name' => $region->getLabel(),
                    'editUrl' => EditorUrl::to($site, $region),
                ])
                ->filter(fn (array $region) => $region['editUrl'] !== null)
                ->values()
                ->all(),
            'homepageEditUrl' => $pages->firstWhere('isHomepage', true)['editUrl'] ?? null,
            'pages' => $pages->all(),
        ];
    }
}; ?>

<div class="mx-auto flex w-full max-w-5xl flex-col gap-8">
    @if ($this->website === null)
        <section class="flex flex-col gap-6">
            <div>
                <flux:heading size="xl" level="1">{{ __('Create your website') }}</flux:heading>
                <flux:subheading size="lg">
                    {{ __('Pick a starting style for :team. Every page, color and font stays editable afterwards.', ['team' => $team->name]) }}
                </flux:subheading>
            </div>

            <form wire:submit="createWebsite" class="flex flex-col gap-6">
                <flux:radio.group wire:model="family" variant="cards" :label="__('Website style')" class="grid gap-3 sm:grid-cols-2">
                    @foreach ($this->families as $option)
                        <flux:radio
                            :value="$option['value']"
                            :label="$option['label']"
                            :description="$option['description']"
                            data-test="website-family-option"
                        />
                    @endforeach
                </flux:radio.group>

                <div>
                    <flux:button variant="primary" type="submit" icon="globe-alt" data-test="create-website-button">
                        {{ __('Create website') }}
                    </flux:button>
                </div>
            </form>
        </section>
    @else
        <header class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="flex flex-col gap-2">
                <div class="flex items-center gap-2">
                    <flux:heading size="xl" level="1">{{ $this->website['name'] }}</flux:heading>
                    <flux:badge :color="$this->website['isLive'] ? 'green' : 'zinc'" size="sm">
                        {{ $this->website['statusLabel'] }}
                    </flux:badge>
                </div>

                @if ($this->website['isLive'])
                    <flux:link :href="$this->website['url']" target="_blank" variant="subtle" class="inline-flex items-center gap-1 text-sm">
                        {{ $this->website['url'] }}
                        <flux:icon name="arrow-top-right-on-square" variant="micro" />
                    </flux:link>
                @else
                    <flux:text>{{ __("Visitors can't see this website until you put it online.") }}</flux:text>
                @endif
            </div>

            <div class="flex flex-wrap gap-2">
                <flux:button wire:click="setLive(@js(! $this->website['isLive']))" data-test="website-status-button">
                    {{ $this->website['isLive'] ? __('Take offline') : __('Put online') }}
                </flux:button>

                @if ($this->website['homepageEditUrl'])
                    <flux:button variant="primary" icon="pencil-square" :href="$this->website['homepageEditUrl']">
                        {{ __('Open editor') }}
                    </flux:button>
                @endif
            </div>
        </header>

        <section class="flex flex-col gap-3">
            <div>
                <flux:heading>{{ __('Pages') }}</flux:heading>
                <flux:subheading>{{ __("Add, rename or delete pages from the editor's page menu.") }}</flux:subheading>
            </div>

            <div class="space-y-3">
                @foreach ($this->website['pages'] as $page)
                    <div class="flex flex-col gap-3 rounded-lg border border-zinc-200 bg-white p-4 sm:flex-row sm:items-center sm:justify-between dark:border-zinc-700 dark:bg-zinc-900" data-test="website-page-row">
                        <div class="flex min-w-0 flex-col gap-1">
                            <div class="flex items-center gap-2">
                                @if ($page['isHomepage'])
                                    <flux:icon name="home" variant="micro" class="text-zinc-500" />
                                @endif
                                <span class="truncate font-medium">{{ $page['name'] }}</span>
                                @unless ($page['isPublished'])
                                    <flux:badge color="zinc" size="sm">{{ __('Draft') }}</flux:badge>
                                @endunless
                            </div>
                            <flux:text class="text-sm">
                                {{ $page['path'] }}
                                @if ($page['updatedAt'])
                                    &middot; {{ __('edited :time', ['time' => $page['updatedAt']]) }}
                                @endif
                            </flux:text>
                        </div>

                        <div class="flex shrink-0 gap-2">
                            @if ($page['url'])
                                <flux:button variant="ghost" size="sm" icon="arrow-top-right-on-square" :href="$page['url']" target="_blank">
                                    {{ __('View') }}
                                </flux:button>
                            @endif
                            @if ($page['editUrl'])
                                <flux:button variant="outline" size="sm" icon="pencil-square" :href="$page['editUrl']" data-test="website-page-edit">
                                    {{ __('Edit') }}
                                </flux:button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2">
            <flux:card class="flex flex-col gap-3">
                <div class="flex items-center gap-2">
                    <flux:icon name="rectangle-group" variant="mini" class="text-zinc-500" />
                    <flux:heading>{{ __('Shared areas') }}</flux:heading>
                </div>
                <flux:text>{{ __('Shown on every page of the website.') }}</flux:text>
                <div class="flex flex-wrap gap-2">
                    @foreach ($this->website['regions'] as $region)
                        <flux:button variant="outline" size="sm" :href="$region['editUrl']">
                            {{ $region['name'] }}
                        </flux:button>
                    @endforeach
                </div>
            </flux:card>

            <flux:card class="flex flex-col gap-3">
                <div class="flex items-center gap-2">
                    <flux:icon name="inbox" variant="mini" class="text-zinc-500" />
                    <flux:heading>{{ __('Inbox') }}</flux:heading>
                    @if ($this->website['unreadSubmissions'] > 0)
                        <flux:badge color="blue" size="sm">{{ __(':count new', ['count' => $this->website['unreadSubmissions']]) }}</flux:badge>
                    @endif
                </div>
                <flux:text>{{ __("Messages and sign-ups sent from your website's forms.") }}</flux:text>
                <div>
                    <flux:button variant="outline" size="sm" :href="route('website.inbox')" wire:navigate>
                        {{ __('Open inbox') }}
                    </flux:button>
                </div>
            </flux:card>
        </section>
    @endif
</div>
