<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectToDashboard();
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'senha' => 'required',
        ]);

        $usuario = Usuario::where('email', $request->email)->first();

        if (!$usuario || !Hash::check($request->senha, $usuario->senha)) {
            return back()->withErrors(['email' => 'Credenciais inválidas'])->withInput();
        }

        if (!$usuario->ativo) {
            return back()->withErrors(['email' => 'Usuário desativado'])->withInput();
        }

        Auth::login($usuario);
        $request->session()->regenerate();

        return $this->redirectToDashboard();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    protected function redirectToDashboard()
    {
        $user = Auth::user();

        return match($user->papel) {
            'superintendente' => redirect('/superintendente'),
            'diretor' => redirect('/diretor'),
            'rh' => redirect('/rh'),
            'administrativo' => redirect('/admin'),
            default => redirect('/login'),
        };
    }
}
