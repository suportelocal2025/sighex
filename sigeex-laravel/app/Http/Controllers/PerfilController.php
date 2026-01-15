<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PerfilController extends Controller
{
    public function index()
    {
        $usuario = Auth::user();
        return view('perfil.index', compact('usuario'));
    }

    public function update(Request $request)
    {
        $usuario = Auth::user();
        
        $request->validate([
            'nome' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:usuarios,email,' . $usuario->id,
            'telefone' => 'nullable|string|max:20',
            'matricula' => 'nullable|string|max:50',
        ]);
        
        $usuario->nome = $request->nome;
        $usuario->email = $request->email;
        $usuario->telefone = $request->telefone;
        $usuario->matricula = $request->matricula;
        $usuario->save();
        
        return redirect('/perfil')->with('success', 'Perfil atualizado com sucesso!');
    }

    public function alterarSenha(Request $request)
    {
        $usuario = Auth::user();
        
        $request->validate([
            'senha_atual' => 'required',
            'nova_senha' => 'required|min:6|confirmed',
        ]);
        
        if (!Hash::check($request->senha_atual, $usuario->senha)) {
            return redirect('/perfil')->with('error', 'Senha atual incorreta.');
        }
        
        $usuario->senha = Hash::make($request->nova_senha);
        $usuario->save();
        
        return redirect('/perfil')->with('success', 'Senha alterada com sucesso!');
    }
}
