<?php

namespace Tests\Feature\Website;

use App\Actions\Website\CreateWebsite;
use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\User;
use FilamentCraft\Blueprints\StarterFamily;
use FilamentCraft\Editor\EditorPanelProvider;
use FilamentCraft\Editor\EditorUrl;
use FilamentCraft\Editor\Pages\EditorPage;
use FilamentCraft\Enums\SiteStatus;
use FilamentCraft\Models\Submission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WebsiteTest extends TestCase
{
    use RefreshDatabase;

    public function test_website_page_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('website.show'))
            ->assertOk()
            ->assertSee(__('Create your website'));
    }

    public function test_owners_see_the_style_families_before_a_website_exists(): void
    {
        $this->actingAs(User::factory()->create());

        $component = Livewire::test('pages::website.show')
            ->assertSee(__('Create website'));

        $this->assertNull($component->instance()->website);

        foreach (StarterFamily::cases() as $family) {
            $component->assertSee($family->getLabel());
        }
    }

    public function test_owners_can_create_the_team_website_from_a_style_family(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test('pages::website.show')
            ->set('family', StarterFamily::Editorial->value)
            ->call('createWebsite')
            ->assertHasNoErrors()
            ->assertSee(__('Take offline'))
            ->assertSee($this->editorUrl($user->currentTeam), escape: false);

        $site = $user->currentTeam->website();

        $this->assertNotNull($site);
        $this->assertSame(SiteStatus::Live, $site->status);
        $this->assertNotNull($site->homepage_template_id);
    }

    public function test_creating_a_website_twice_keeps_the_first_one(): void
    {
        $team = $this->teamWithWebsite();

        $this->actingAs($team->owner());

        Livewire::test('pages::website.show')
            ->set('family', StarterFamily::Bold->value)
            ->call('createWebsite');

        $this->assertSame(1, $team->sites()->count());
    }

    public function test_the_style_family_must_be_a_known_one(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('pages::website.show')
            ->set('family', 'neon')
            ->call('createWebsite')
            ->assertHasErrors(['family']);
    }

    public function test_owners_can_take_the_website_offline_and_back_online(): void
    {
        $team = $this->teamWithWebsite();
        $publicUrl = route('website.public', ['tenantSlug' => $team->slug]);

        $this->actingAs($team->owner());

        $component = Livewire::test('pages::website.show')->call('setLive', false);

        $this->assertSame(SiteStatus::Archived, $team->website()->status);
        $this->get($publicUrl)->assertNotFound();

        $component->call('setLive', true);

        $this->assertSame(SiteStatus::Live, $team->website()->status);
        $this->get($publicUrl)->assertOk();
    }

    public function test_the_live_website_is_public_at_its_team_path(): void
    {
        $team = $this->teamWithWebsite();

        $this->get(route('website.public', ['tenantSlug' => $team->slug]))
            ->assertOk()
            ->assertSee($team->name);
    }

    public function test_members_without_the_website_permission_cannot_manage_it(): void
    {
        $team = $this->teamWithWebsite();
        $member = User::factory()->create();
        $team->members()->attach($member, ['role' => TeamRole::Member->value]);

        $this->actingAs($member)
            ->get(route('website.show', ['current_team' => $team->slug]))
            ->assertForbidden();

        $this->get(route('website.inbox', ['current_team' => $team->slug]))->assertForbidden();
        $this->get($this->editorUrl($team))->assertNotFound();

        $this->assertSame(SiteStatus::Live, $team->website()->status);
    }

    public function test_members_do_not_see_the_website_navigation(): void
    {
        $team = $this->teamWithWebsite();
        $member = User::factory()->create();
        $team->members()->attach($member, ['role' => TeamRole::Member->value]);

        $this->actingAs($member)
            ->get(route('dashboard', ['current_team' => $team->slug]))
            ->assertOk()
            ->assertDontSee(route('website.show', ['current_team' => $team->slug]));
    }

    public function test_owners_see_the_website_navigation(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('website.show'))
            ->assertSee(route('website.inbox'));
    }

    public function test_admins_can_manage_the_website_and_open_the_editor(): void
    {
        $team = $this->teamWithWebsite();
        $admin = User::factory()->create();
        $team->members()->attach($admin, ['role' => TeamRole::Admin->value]);

        $this->actingAs($admin)
            ->get(route('website.show', ['current_team' => $team->slug]))
            ->assertOk()
            ->assertSee($this->editorUrl($team), escape: false);

        $this->get($this->editorUrl($team))->assertOk();
    }

    public function test_the_editor_needs_a_signed_in_user(): void
    {
        $team = $this->teamWithWebsite();

        $this->get($this->editorUrl($team))->assertRedirect(route('login'));
    }

    public function test_a_team_cannot_open_another_teams_page_through_its_own_editor_url(): void
    {
        $mine = $this->teamWithWebsite();
        $theirs = $this->teamWithWebsite();

        $url = EditorPage::getUrl(
            ['template' => $theirs->website()->homepage_template_id],
            panel: EditorPanelProvider::DEFAULT_ID,
            tenant: $mine,
        );

        $this->actingAs($mine->owner())->get($url)->assertNotFound();
        $this->get($this->editorUrl($theirs))->assertNotFound();
    }

    public function test_the_builder_panel_exposes_nothing_but_the_editor(): void
    {
        $team = $this->teamWithWebsite();

        $this->actingAs($team->owner());

        $this->get('/builder/'.$team->slug.'/sites')->assertNotFound();
        $this->get('/builder/'.$team->slug.'/filament-craft-dashboard')->assertNotFound();
        $this->get('/builder/'.$team->slug)->assertNotFound();
        $this->get('/builder')->assertNotFound();
    }

    public function test_the_editor_leads_back_to_the_team_website_page(): void
    {
        $team = $this->teamWithWebsite();

        $this->actingAs($team->owner())
            ->get($this->editorUrl($team))
            ->assertOk()
            ->assertSee(route('website.show', ['current_team' => $team->slug]), escape: false);
    }

    public function test_renaming_a_team_keeps_its_website_and_editor_addresses(): void
    {
        $team = $this->teamWithWebsite();
        $publicUrl = route('website.public', ['tenantSlug' => $team->slug]);
        $editorUrl = $this->editorUrl($team);

        $this->actingAs($team->owner());

        Livewire::test('pages::teams.edit', ['team' => $team])
            ->set('teamName', 'Renamed Studio')
            ->call('updateTeam')
            ->assertHasNoErrors();

        $this->assertSame('Renamed Studio', $team->fresh()->name);
        $this->assertSame($team->slug, $team->fresh()->slug);

        $this->get($publicUrl)->assertOk();
        $this->get($editorUrl)->assertOk();
    }

    public function test_the_inbox_lists_and_marks_the_teams_submissions(): void
    {
        $team = $this->teamWithWebsite();
        $submission = Submission::factory()->for($team->website())->create([
            'payload' => ['name' => 'Ada', 'email' => 'ada@example.com', 'message' => 'Hello'],
        ]);

        $this->actingAs($team->owner());

        Livewire::test('pages::website.show')->assertSee(__(':count new', ['count' => 1]));

        Livewire::test('pages::website.inbox')
            ->assertSee('Ada')
            ->assertSee('Hello')
            ->assertSee(__('Mark as read'))
            ->call('markAsRead', $submission->getKey())
            ->assertDontSee(__('Mark as read'));

        $this->assertTrue($submission->fresh()->isRead());
    }

    public function test_submissions_of_another_team_are_out_of_reach(): void
    {
        $mine = $this->teamWithWebsite();
        $theirs = $this->teamWithWebsite();
        $submission = Submission::factory()->for($theirs->website())->create();

        $this->actingAs($mine->owner());

        Livewire::test('pages::website.inbox')
            ->call('markAsRead', $submission->getKey())
            ->assertNotFound();

        $this->assertFalse($submission->fresh()->isRead());
    }

    private function teamWithWebsite(): Team
    {
        $team = User::factory()->create()->currentTeam;

        app(CreateWebsite::class)->handle($team, StarterFamily::Modern);

        return $team;
    }

    private function editorUrl(Team $team): string
    {
        return EditorUrl::to($team->website());
    }
}
