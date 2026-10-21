<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route(Auth::user()->isAdmin() ? 'admin.dashboard' : 'pos.index');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        if (! Auth::attempt($data, $request->boolean('remember'))) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Email atau password salah.']);
        }

        $request->session()->regenerate();

        return redirect()->route(Auth::user()->isAdmin() ? 'admin.dashboard' : 'pos.index');
    }

    public function showLoginPin()
    {
        if (Auth::check()) {
            return redirect()->route(Auth::user()->isAdmin() ? 'admin.dashboard' : 'pos.index');
        }

        $users = User::whereNotNull('pin')->orderBy('name')->get();

        return view('auth.login-pin', compact('users'));
    }

    public function loginPin(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'pin' => ['required', 'digits:4'],
        ], [
            'pin.digits' => 'PIN harus tepat 4 angka.',
        ]);

        $user = User::findOrFail($data['user_id']);

        if (! $user->pin || ! Hash::check($data['pin'], $user->pin)) {
            return back()->withErrors(['pin' => 'PIN salah untuk pengguna '.$user->name.'.']);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route($user->isAdmin() ? 'admin.dashboard' : 'pos.index');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
