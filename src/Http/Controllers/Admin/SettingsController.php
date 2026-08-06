<?php

namespace Hybridcore\Announcements\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function __construct(private readonly SettingsService $settings) {}

    public function show(): Response
    {
        return Inertia::render('Extensions/hybridcore/announcements/Admin/Settings', [
            'settings' => [
                'max_shown' => (int) $this->settings->get('announcements.max_shown', 3),
                'show_banner' => (bool) $this->settings->get('announcements.show_banner', true),
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'max_shown' => ['required', 'integer', 'min:1', 'max:10'],
            'show_banner' => ['boolean'],
        ]);

        $this->settings->set('announcements.max_shown', $data['max_shown']);
        $this->settings->set('announcements.show_banner', (bool) ($data['show_banner'] ?? false));

        return back()->with('success', trans('announcements::messages.settings_saved'));
    }
}
