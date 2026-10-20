<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        return Auth::check() ? redirect()->route('admin.index') : view('admin.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            // Mensaje único: no revela si el correo existe.
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'Correo o clave incorrectos.']);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('admin.index'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
