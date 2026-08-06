<?php

namespace Hybridcore\Announcements;

use App\Services\Extensions\Registries\ExtensionRegistry;
use App\Services\SettingsService;
use App\Support\Slots;
use Hybridcore\Announcements\Services\AnnouncementService;
use Illuminate\Support\ServiceProvider;

class AnnouncementsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register the service as a singleton so any class can inject it.
        $this->app->singleton(AnnouncementService::class);
    }

    public function boot(ExtensionRegistry $registry): void
    {
        // ── Permissions ───────────────────────────────────────────────────────
        // Group slug 'announcements' groups these in the admin role editor.
        $registry->permissions()->register('announcements.view', 'View Announcements', 'announcements');
        $registry->permissions()->register('announcements.manage', 'Manage Announcements', 'announcements');

        // ── Admin navigation ──────────────────────────────────────────────────
        // Appears under the "Content" section in the left sidebar.
        $registry->navigation()->register(
            label: 'Announcements',
            route: 'admin.announcements.index',
            icon: 'Megaphone',
            section: 'Content',
            permission: 'announcements.view',
            sort: 50,
        );

        // ── Admin dashboard widget ────────────────────────────────────────────
        // Shows a stat card: total active announcements.
        $registry->widgets()->register(
            id: 'announcements.active_count',
            title: 'Active Announcements',
            component: 'stat',
            data: fn () => [
                'value' => $this->app->make(AnnouncementService::class)->active()->count(),
                'icon' => 'Megaphone',
                'label' => 'active now',
                'accent' => 'blue',
            ],
            permission: 'announcements.view',
            sort: 10,
        );

        // ── Extension settings page ───────────────────────────────────────────
        // URL:   /admin/settings/extensions/announcements
        // Route: admin.settings.extensions.announcements  (defined in routes/admin.php)
        $registry->settings()->register(
            slug: 'announcements',
            label: 'Announcements',
            permission: 'announcements.manage',
        );

        // ── Site-wide banner slot ────────────────────────────────────────────
        // Registers HybridcoreAnnouncementsBanner.vue below the header on every
        // public page. The data closure is lazy — runs only when the slot is
        // rendered and only if show_banner is enabled.
        //
        // The component receives { announcements: [...] } as props and handles
        // its own per-visitor dismiss state client-side (localStorage) — a
        // dismissed announcement stays dismissed until the admin edits it,
        // since editing changes updated_at and the dismiss key includes it.
        $registry->slots()->register(
            slot: Slots::LAYOUT_TOP,
            component: 'HybridcoreAnnouncementsBanner',
            data: function () {
                $settings = $this->app->make(SettingsService::class);

                if (! $settings->get('announcements.show_banner', true)) {
                    return null; // returning null skips the component entirely
                }

                $limit = (int) $settings->get('announcements.max_shown', 3);
                $items = $this->app->make(AnnouncementService::class)->active($limit);

                return [
                    'announcements' => $items->map(fn ($a) => [
                        'id' => $a->id,
                        'title' => $a->title,
                        'body' => $a->body,
                        'type' => $a->type,
                        'updated_at' => $a->updated_at?->toIso8601String(),
                    ])->values()->all(),
                ];
            },
            priority: 10,
        );
    }
}
