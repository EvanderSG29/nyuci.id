<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RegisterTokoController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        if ($request->user()?->toko) {
            return redirect()->route('dashboard');
        }

        return view('auth.register-toko');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nama_toko' => ['required', 'string', 'max:255'],
            'no_hp' => ['nullable', 'string', 'max:30'],
            'alamat' => ['nullable', 'string', 'max:1000'],
        ]);

        $request->user()->toko()->updateOrCreate(
            [],
            [
                'nama_toko' => $request->string('nama_toko')->toString(),
                'no_hp' => $request->string('no_hp')->toString() ?: null,
                'alamat' => $request->string('alamat')->toString() ?: null,
            ]
        );

        return redirect()->route('dashboard')->with('success', 'Informasi toko berhasil disimpan.');
    }
}
