<?php

namespace Hybridcore\Announcements\Tests\Feature;

use App\Http\Middleware\EnsureIsAdmin;
use App\Models\User;
use App\Services\Extensions\ExtensionAutoloader;
use Hybridcore\Announcements\AnnouncementsServiceProvider;
use Hybridcore\Announcements\Models\Announcement;
use Hybridcore\Announcements\Services\AnnouncementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AnnouncementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        file_put_contents(storage_path('installed.lock'), 'installed');

        // Routes normally register only for extensions the DB marks enabled
        // at boot — a fresh RefreshDatabase test has no such row, so wire
        // them by hand. Mirrors demo's ExampleTest.
        $base = base_path('extensions/hybridcore/announcements');
        ExtensionAutoloader::register($base, ['namespace' => 'Hybridcore\\Announcements']);

        $this->app->register(AnnouncementsServiceProvider::class);

        Route::middleware(['web', 'auth', EnsureIsAdmin::class])
            ->prefix('admin')->group($base.'/routes/admin.php');

        $this->app['router']->getRoutes()->refreshNameLookups();
    }

    protected function tearDown(): void
    {
        @unlink(storage_path('installed.lock'));
        parent::tearDown();
    }

    public function test_admin_can_create_an_announcement(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post(route('admin.announcements.store'), [
            'title' => 'Maintenance tonight',
            'body' => 'Servers restart at 22:00 UTC.',
            'type' => 'warning',
            'is_active' => true,
        ])->assertRedirect(route('admin.announcements.index'));

        $this->assertDatabaseHas('announcements', ['title' => 'Maintenance tonight', 'type' => 'warning']);
    }

    public function test_visible_scope_respects_active_flag_and_time_window(): void
    {
        Announcement::create(['title' => 'Live', 'body' => '.', 'type' => 'info', 'is_active' => true]);
        Announcement::create(['title' => 'Inactive', 'body' => '.', 'type' => 'info', 'is_active' => false]);
        Announcement::create([
            'title' => 'Not started yet', 'body' => '.', 'type' => 'info', 'is_active' => true,
            'starts_at' => now()->addDay(),
        ]);
        Announcement::create([
            'title' => 'Expired', 'body' => '.', 'type' => 'info', 'is_active' => true,
            'ends_at' => now()->subDay(),
        ]);

        $titles = Announcement::visible()->pluck('title');

        $this->assertSame(['Live'], $titles->all());
    }

    public function test_service_caches_active_list_and_flushes_on_write(): void
    {
        $service = app(AnnouncementService::class);

        $a = Announcement::create(['title' => 'One', 'body' => '.', 'type' => 'info', 'is_active' => true]);
        $this->assertCount(1, $service->active());

        // A second announcement created without going through the service
        // must not appear until the cache is flushed — proves active() is
        // actually reading the cache, not the table, on the second call.
        Announcement::create(['title' => 'Two', 'body' => '.', 'type' => 'info', 'is_active' => true]);
        $this->assertCount(1, $service->active());

        $service->update($a, ['title' => 'One (edited)']);
        $this->assertCount(2, $service->active());
    }

    public function test_service_delete_removes_the_row_and_flushes_cache(): void
    {
        $service = app(AnnouncementService::class);
        $a = Announcement::create(['title' => 'Gone soon', 'body' => '.', 'type' => 'info', 'is_active' => true]);

        $this->assertCount(1, $service->active());

        $service->delete($a);

        $this->assertCount(0, $service->active());
        $this->assertDatabaseMissing('announcements', ['id' => $a->id]);
    }

    public function test_admin_can_save_settings(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->patch(route('admin.settings.extensions.announcements.update'), [
            'max_shown' => 5,
            'show_banner' => false,
        ])->assertSessionHasNoErrors();

        $this->assertSame(5, (int) app(\App\Services\SettingsService::class)->get('announcements.max_shown'));
        $this->assertFalse((bool) app(\App\Services\SettingsService::class)->get('announcements.show_banner'));
    }
}
