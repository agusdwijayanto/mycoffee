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
        $users = User::orderBy('name')->get();

        return view('admin.users.index', compact('users'));
    }

    public function updatePin(Request $request, User $user)
    {
        $data = $request->validate([
            'pin' => ['required', 'digits:4'],
        ], ['pin.digits' => 'PIN harus tepat 4 angka.']);

        $user->update(['pin' => Hash::make($data['pin'])]);

        return back()->with('sukses', 'PIN '.$user->name.' berhasil diubah.');
    }
}
