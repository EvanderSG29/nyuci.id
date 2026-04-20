<?php

namespace App\Http\Controllers;

use App\Http\Requests\DashboardChartDefaultsUpdateRequest;
use App\Http\Requests\DashboardChartOverridesUpdateRequest;
use App\Services\DashboardCharts\DashboardChartConfigResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardChartSettingsController extends Controller
{
    public function edit(Request $request, DashboardChartConfigResolver $resolver): View
    {
        $user = $request->user();
        $toko = $user->toko;

        if (! $toko) {
            return view('settings.dashboard', [
                'hasStore' => false,
                'chartSettings' => null,
            ]);
        }

        return view('settings.dashboard', [
            'hasStore' => true,
            'chartSettings' => $resolver->settingsPayload($toko, $user),
        ]);
    }

    public function updateDefaults(DashboardChartDefaultsUpdateRequest $request, DashboardChartConfigResolver $resolver): RedirectResponse
    {
        $toko = $request->user()->toko;

        abort_unless($toko, 403);

        $resolver->updateDefaults($toko, $request->validated('charts'));

        return redirect()
            ->route('settings.dashboard')
            ->with('status', 'dashboard-chart-defaults-updated');
    }

    public function updateOverrides(DashboardChartOverridesUpdateRequest $request, DashboardChartConfigResolver $resolver): RedirectResponse
    {
        $user = $request->user();
        $toko = $user->toko;

        abort_unless($toko, 403);

        $resolver->updateOverrides($toko, $user, $request->validated('overrides'));

        return redirect()
            ->route('settings.dashboard')
            ->with('status', 'dashboard-chart-overrides-updated');
    }

    public function destroyOverride(Request $request, string $slot, DashboardChartConfigResolver $resolver): RedirectResponse
    {
        $user = $request->user();
        $toko = $user->toko;

        abort_unless($toko, 403);
        abort_unless(in_array($slot, DashboardChartConfigResolver::slotKeys(), true), 404);

        $resolver->deleteOverride($toko, $user, $slot);

        return redirect()
            ->route('settings.dashboard')
            ->with('status', 'dashboard-chart-override-deleted');
    }

    public function resetDefault(Request $request, string $slot, DashboardChartConfigResolver $resolver): RedirectResponse
    {
        $toko = $request->user()->toko;

        abort_unless($toko, 403);
        abort_unless(in_array($slot, DashboardChartConfigResolver::slotKeys(), true), 404);

        $resolver->resetPreset($toko, $slot);

        return redirect()
            ->route('settings.dashboard')
            ->with('status', 'dashboard-chart-default-reset');
    }
}
