<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Requests\StoreSettingsUpdateRequest;
use App\Models\Toko;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Display the store settings form.
     */
    public function editStore(Request $request): View
    {
        return view('settings.store', [
            'user' => $request->user(),
            'dashboardCards' => $request->user()->toko?->dashboardCards() ?? Toko::dashboardCardDefaults(),
            'dashboardCardOptions' => Toko::dashboardCardOptions(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Update the authenticated user's store settings.
     */
    public function updateStore(StoreSettingsUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $request->user()->toko()->updateOrCreate(
            [],
            [
                'nama_toko' => $validated['nama_toko'],
                'alamat' => $validated['alamat'] ?: null,
                'no_hp' => $validated['no_hp'] ?: null,
                'dashboard_cards' => Toko::normalizeDashboardCards($validated['dashboard_cards'] ?? []),
            ]
        );

        return Redirect::route('pengaturan-toko.edit')->with('status', 'store-settings-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
