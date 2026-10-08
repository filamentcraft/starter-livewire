<?php

use App\Models\Team;
use FilamentCraft\Models\Submission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Inbox')] class extends Component {
    public Team $team;

    public function mount(): void
    {
        $this->team = Auth::user()->currentTeam;

        Gate::authorize('manageWebsite', $this->team);
    }

    public function markAsRead(int $submissionId): void
    {
        Gate::authorize('manageWebsite', $this->team);

        $site = $this->team->website();

        abort_if($site === null, 404);

        Submission::query()->forSite($site)->findOrFail($submissionId)->markAsRead();

        unset($this->submissions);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    #[Computed]
    public function submissions(): array
    {
        $site = $this->team->website();

        if ($site === null) {
            return [];
        }

        return Submission::query()
            ->forSite($site)
            ->latest()
            ->limit(100)
            ->get()
            ->map(fn (Submission $submission) => [
                'id' => $submission->getKey(),
                'typeLabel' => $submission->type->getLabel(),
                'sender' => $submission->sender(),
                'fields' => collect($submission->payload->getArrayCopy())
                    ->filter(fn (mixed $value) => is_scalar($value) && (string) $value !== '')
                    ->map(fn (mixed $value) => (string) $value)
                    ->all(),
                'isRead' => $submission->isRead(),
                'receivedAt' => $submission->created_at->diffForHumans(),
            ])
            ->all();
    }
}; ?>

<div class="mx-auto flex w-full max-w-5xl flex-col gap-6">
    <div>
        <flux:heading size="xl" level="1">{{ __('Inbox') }}</flux:heading>
        <flux:subheading size="lg">{{ __("Messages and sign-ups sent from your website's contact and newsletter forms.") }}</flux:subheading>
    </div>

    @if ($this->submissions === [])
        <div class="flex flex-col items-center gap-2 rounded-lg border border-dashed border-zinc-300 p-10 text-center dark:border-zinc-700">
            <flux:icon name="inbox" class="text-zinc-400" />
            <flux:heading>{{ __('Nothing here yet') }}</flux:heading>
            <flux:text class="max-w-sm">{{ __('Add a Contact or Newsletter section in the editor and new messages will show up here.') }}</flux:text>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($this->submissions as $submission)
                <div
                    wire:key="submission-{{ $submission['id'] }}"
                    @class([
                        'flex flex-col gap-3 rounded-lg border p-4',
                        'border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900' => $submission['isRead'],
                        'border-zinc-300 bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800' => ! $submission['isRead'],
                    ])
                    data-test="submission-row"
                >
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <span class="font-medium">{{ $submission['sender'] ?: __('Anonymous visitor') }}</span>
                            <flux:badge color="zinc" size="sm">{{ $submission['typeLabel'] }}</flux:badge>
                            @unless ($submission['isRead'])
                                <flux:badge color="blue" size="sm">{{ __('New') }}</flux:badge>
                            @endunless
                        </div>

                        <div class="flex items-center gap-3">
                            <flux:text class="text-sm">{{ $submission['receivedAt'] }}</flux:text>
                            @unless ($submission['isRead'])
                                <flux:button
                                    variant="ghost"
                                    size="sm"
                                    icon="check"
                                    wire:click="markAsRead({{ $submission['id'] }})"
                                    data-test="submission-mark-read"
                                >
                                    {{ __('Mark as read') }}
                                </flux:button>
                            @endunless
                        </div>
                    </div>

                    <dl class="grid gap-x-6 gap-y-1 text-sm sm:grid-cols-[max-content_1fr]">
                        @foreach ($submission['fields'] as $field => $value)
                            <div class="contents">
                                <dt class="text-zinc-500 capitalize dark:text-zinc-400">{{ str_replace('_', ' ', $field) }}</dt>
                                <dd class="break-words whitespace-pre-line">{{ $value }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            @endforeach
        </div>
    @endif
</div>
