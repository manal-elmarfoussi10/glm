<?php

namespace App\Http\Controllers\App\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlatformSettingsController extends Controller
{
    public const KEYS_GENERAL = ['app_name', 'support_email', 'default_trial_days'];
    public const KEYS_LEGAL = ['legal_terms_url', 'legal_privacy_url', 'legal_terms_content', 'legal_privacy_content'];

    public function index(): View
    {
        $general = [
            'app_name' => Setting::get('app_name', config('app.name')),
            'support_email' => Setting::get('support_email', ''),
            'default_trial_days' => Setting::get('default_trial_days', '14'),
        ];
        $legal = [
            'legal_terms_url' => Setting::get('legal_terms_url', ''),
            'legal_privacy_url' => Setting::get('legal_privacy_url', ''),
            'legal_terms_content' => Setting::get('legal_terms_content', ''),
            'legal_privacy_content' => Setting::get('legal_privacy_content', ''),
        ];

        return view('app.admin.settings.index', [
            'title' => 'Paramètres plateforme',
            'general' => $general,
            'legal' => $legal,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $tab = $request->input('tab', 'general');

        if ($tab === 'general') {
            $validated = $request->validate([
                'app_name' => 'nullable|string|max:255',
                'support_email' => 'nullable|email|max:255',
                'default_trial_days' => 'nullable|integer|min:0|max:365',
            ]);
            foreach ($validated as $key => $value) {
                Setting::set($key, $value !== null && $value !== '' ? (string) $value : null);
            }
            AuditLog::log('settings.updated', 'platform_settings', 0, null, ['tab' => 'general', 'keys' => array_keys($validated)]);
        }

        if ($tab === 'legal') {
            $validated = $request->validate([
                'legal_terms_url' => 'nullable|string|max:500',
                'legal_privacy_url' => 'nullable|string|max:500',
                'legal_terms_content' => 'nullable|string|max:50000',
                'legal_privacy_content' => 'nullable|string|max:50000',
            ]);
            foreach ($validated as $key => $value) {
                Setting::set($key, $value !== null && $value !== '' ? (string) $value : null);
            }
            AuditLog::log('settings.updated', 'platform_settings', 0, null, ['tab' => 'legal', 'keys' => array_keys($validated)]);
        }

        return redirect()
            ->route('app.admin.settings.index')
            ->with('success', 'Paramètres enregistrés.');
    }
}
