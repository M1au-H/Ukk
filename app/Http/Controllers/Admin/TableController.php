<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DiningTable;
use Illuminate\Http\Request;

class TableController extends Controller
{
    public function index()
    {
        return DiningTable::orderByRaw('CAST(table_number AS UNSIGNED)')->orderBy('table_number')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'table_number' => ['required', 'string', 'max:50'],
        ]);

        $table = DiningTable::create([
            'table_number' => $data['table_number'],
            'table_token' => DiningTable::generateToken(),
            'is_active' => true,
        ]);

        return response()->json($table, 201);
    }

    public function update(Request $request, DiningTable $table)
    {
        $data = $request->validate([
            'table_number' => ['sometimes', 'required', 'string', 'max:50'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $table->update($data);

        return response()->json($table);
    }

    /**
     * Dipanggil kalau QR fisik rusak atau dipindah ke meja lain.
     * Token lama otomatis invalid begitu diganti.
     */
    public function regenerateToken(DiningTable $table)
    {
        $table->update(['table_token' => DiningTable::generateToken()]);

        return response()->json($table);
    }

    public function destroy(DiningTable $table)
    {
        abort_if(
            $table->orders()->exists(),
            422,
            'Meja ini sudah punya riwayat pesanan, jadi tidak bisa dihapus. Nonaktifkan saja.'
        );

        $table->delete();

        return response()->json(['message' => 'Meja dihapus.']);
    }
}
