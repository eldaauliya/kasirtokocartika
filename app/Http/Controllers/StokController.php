<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Stok;
use Illuminate\Http\Request;

class StokController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->search;

        $stoks = Stok::join('produks', 'produks.id', 'stoks.produk_id')
            ->select('stoks.*', 'nama_produk')
            ->orderBy('stoks.id', 'desc')
            ->when($search, function ($q, $search) {
                return $q->where('tanggal', 'like', "%{$search}%");
            })
            ->paginate();

            if ($search) $stoks->appends(['search' => $search]);

            return view('stok.index', [
                'stoks' => $stoks
            ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('stok.create');
    }

    public function produk(Request $request)
    {
        $produks = Produk::select('id', 'nama_produk')
            ->where('nama_produk', 'like', "%{$request->search}%")
            ->take(15)
            ->orderBy('nama_produk')
            ->get();
        
        return response()->json($produks);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'produk_id' => ['required', 'exists:produks,id'],
            'jumlah' => ['required', 'numeric'],
            'nama_suplier' => ['required', 'max:150']
        ], [], [
            'produk_id' => 'Nama produk'
        ]);

        $request->merge([
            'tanggal' => date('Y-m-d')
        ]);

        Stok::create($request->all());

        $produk = Produk::find($request->produk_id);

        $produk->update([
            'stok' => $produk->stok + $request->jumlah
        ]);

        return redirect()->route('stok.index')->with('store', 'success');
    }

    /**
     * Display the specified resource.
     */
    public function show(Stok $stok)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Stok $stok)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Stok $stok)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Stok $stok)
    {
        $produk = Produk::find($stok->produk_id);
        $produk->update([
            'stok' => $produk->stok - $stok->jumlah
        ]);

        $stok->delete();

        return back()->with('destroy', 'success');
    }
}