<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        return User::orderBy('role')->orderBy('name')->get(['id', 'name', 'email', 'role']);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'in:admin,kitchen'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
        ]);

        return response()->json($user->only(['id', 'name', 'email', 'role']), 201);
    }

    /** Ubah nama dan/atau reset password. Email dan peran sengaja tidak bisa diubah di sini. */
    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        if (isset($data['name'])) {
            $user->name = $data['name'];
        }
        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
            $user->tokens()->delete(); // paksa login ulang di perangkat lain
        }
        $user->save();

        return response()->json($user->only(['id', 'name', 'email', 'role']));
    }

    public function destroy(Request $request, User $user)
    {
        abort_if($user->id === $request->user()->id, 422, 'Kamu tidak bisa menghapus akunmu sendiri.');
        abort_if(
            $user->role === 'admin' && User::where('role', 'admin')->count() <= 1,
            422,
            'Admin terakhir tidak bisa dihapus.'
        );

        $user->tokens()->delete();
        $user->delete();

        return response()->json(['message' => 'Akun dihapus.']);
    }
}
