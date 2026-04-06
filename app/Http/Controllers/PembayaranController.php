<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Laundry;
use Illuminate\Http\Request;

class PembayaranController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $toko = auth()->user()->toko;

        if (! $toko) {
            return redirect()->route('register.toko.create')->with('warning', 'Lengkapi data toko terlebih dahulu sebelum mengelola pembayaran.');
        }

        $pembayarans = Pembayaran::with('laundry')
            ->whereHas('laundry', function ($query) use ($toko) {
                $query->where('toko_id', $toko->id);
            })
            ->get();
        return view('pembayaran.index', ['data' => $pembayarans]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $toko = auth()->user()->toko;

        if (! $toko) {
            return redirect()->route('register.toko.create')->with('warning', 'Lengkapi data toko terlebih dahulu sebelum membuat pembayaran.');
        }

        $laundries = Laundry::where('toko_id', $toko->id)
            ->doesntHave('pembayaran')
            ->get();
        return view('pembayaran.create', ['laundries' => $laundries]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Pembayaran::create($request->all());
        return redirect()->route('pembayaran.index')->with('success', 'Pembayaran ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(Pembayaran $pembayaran)
    {
        $this->authorize('view', $pembayaran);
        return view('pembayaran.show', ['pembayaran' => $pembayaran]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Pembayaran $pembayaran)
    {
        $this->authorize('update', $pembayaran);
        return view('pembayaran.edit', ['pembayaran' => $pembayaran]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Pembayaran $pembayaran)
    {
        $this->authorize('update', $pembayaran);
        $pembayaran->update($request->all());
        return redirect()->route('pembayaran.index')->with('success', 'Pembayaran diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Pembayaran $pembayaran)
    {
        $this->authorize('delete', $pembayaran);
        $pembayaran->delete();
        return redirect()->route('pembayaran.index')->with('success', 'Pembayaran dihapus');
    }

    /**
     * Mark as paid
     */
    public function markAsPaid($id)
    {
        $p = Pembayaran::findOrFail($id);
        $this->authorize('update', $p);
        $p->status = 'sudah_bayar';
        $p->save();

        return back()->with('success', 'Status pembayaran diperbarui');
    }
}
