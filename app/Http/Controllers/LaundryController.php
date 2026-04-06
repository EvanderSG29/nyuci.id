<?php

namespace App\Http\Controllers;

use App\Models\Laundry;
use Illuminate\Http\Request;

class LaundryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $toko = auth()->user()->toko;

        if (! $toko) {
            return redirect()->route('register.toko.create')->with('warning', 'Lengkapi data toko terlebih dahulu sebelum mengelola laundry.');
        }

        $laundries = Laundry::where('toko_id', $toko->id)->get();
        return view('laundry.index', ['data' => $laundries]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (! auth()->user()->toko) {
            return redirect()->route('register.toko.create')->with('warning', 'Lengkapi data toko terlebih dahulu sebelum menambah laundry.');
        }

        return view('laundry.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $toko = auth()->user()->toko;

        if (! $toko) {
            return redirect()->route('register.toko.create')->with('warning', 'Lengkapi data toko terlebih dahulu sebelum menyimpan laundry.');
        }

        $data = $request->all();
        $data['toko_id'] = $toko->id;
        Laundry::create($data);
        return redirect()->route('laundry.index')->with('success', 'Data laundry ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(Laundry $laundry)
    {
        // Pastikan user hanya bisa lihat laundry dari tokonya
        $this->authorize('view', $laundry);
        return view('laundry.show', ['laundry' => $laundry]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Laundry $laundry)
    {
        // Pastikan user hanya bisa edit laundry dari tokonya
        $this->authorize('update', $laundry);
        return view('laundry.edit', ['laundry' => $laundry]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Laundry $laundry)
    {
        // Pastikan user hanya bisa update laundry dari tokonya
        $this->authorize('update', $laundry);
        $laundry->update($request->all());
        return redirect()->route('laundry.index')->with('success', 'Data laundry diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Laundry $laundry)
    {
        // Pastikan user hanya bisa delete laundry dari tokonya
        $this->authorize('delete', $laundry);
        $laundry->delete();
        return redirect()->route('laundry.index')->with('success', 'Data laundry dihapus');
    }

    /**
     * Toggle is_taken status
     */
    public function updateStatus($id)
    {
        $laundry = Laundry::findOrFail($id);
        
        // Pastikan user hanya bisa update laundry dari tokonya
        $this->authorize('update', $laundry);
        
        $laundry->is_taken = !$laundry->is_taken;
        $laundry->save();

        return back()->with('success', 'Status diperbarui');
    }
}
