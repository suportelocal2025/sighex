<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Unidade;
use App\Models\Servidor;
use App\Models\Usuario;
use App\Models\Equipe;
use App\Models\Modulo;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function dashboard()
    {
        $unidades = Unidade::count();
        $servidores = Servidor::count();
        $usuarios = Usuario::count();
        $unidadesAtivas = Unidade::where('ativo', true)->count();

        return view('administrativo.dashboard', compact('unidades', 'servidores', 'usuarios', 'unidadesAtivas'));
    }

    public function unidades()
    {
        $unidades = Unidade::withCount(['servidores', 'modulos'])->get();
        return view('administrativo.unidades', compact('unidades'));
    }

    public function formUnidade($id = null)
    {
        $unidade = $id ? Unidade::with(['modulos'])->findOrFail($id) : null;
        return view('administrativo.form-unidade', compact('unidade'));
    }

    public function salvarUnidade(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'codigo' => 'required|string|max:50|unique:unidades,codigo,' . $request->id,
            'endereco' => 'nullable|string|max:255',
            'telefone' => 'nullable|string|max:20',
        ]);

        $unidade = Unidade::updateOrCreate(
            ['id' => $request->id],
            [
                'nome' => $request->nome,
                'codigo' => $request->codigo,
                'endereco' => $request->endereco,
                'telefone' => $request->telefone,
                'ativo' => $request->has('ativo'),
            ]
        );

        if (!$request->id) {
            foreach (['A', 'B', 'C', 'D'] as $letra) {
                Equipe::create([
                    'unidade_id' => $unidade->id,
                    'nome' => "Equipe $letra",
                ]);
            }
        }

        return redirect('/admin/unidades')->with('success', 'Unidade salva!');
    }

    public function excluirUnidade($id)
    {
        Unidade::findOrFail($id)->delete();
        return redirect('/admin/unidades')->with('success', 'Unidade excluída!');
    }

    public function servidores(Request $request)
    {
        $query = Servidor::with('unidade');
        
        if ($request->filled('unidade_id')) {
            $query->where('unidade_id', $request->unidade_id);
        }

        $servidores = $query->orderBy('nome')->get();
        $unidades = Unidade::where('ativo', true)->get();

        return view('administrativo.servidores', compact('servidores', 'unidades'));
    }

    public function salvarServidor(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'matricula' => 'required|string|max:50|unique:servidores,matricula,' . $request->id,
            'unidade_id' => 'required|exists:unidades,id',
            'cargo' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:255',
            'telefone' => 'nullable|string|max:20',
        ]);

        Servidor::updateOrCreate(
            ['id' => $request->id],
            [
                'nome' => $request->nome,
                'matricula' => $request->matricula,
                'unidade_id' => $request->unidade_id,
                'cargo' => $request->cargo,
                'email' => $request->email,
                'telefone' => $request->telefone,
                'apto_escala_extra' => $request->has('apto_escala_extra'),
                'ativo' => $request->has('ativo'),
            ]
        );

        return redirect('/admin/servidores')->with('success', 'Servidor salvo!');
    }

    public function excluirServidor($id)
    {
        Servidor::findOrFail($id)->delete();
        return redirect('/admin/servidores')->with('success', 'Servidor excluído!');
    }

    public function usuarios()
    {
        $usuarios = Usuario::select('id', 'nome', 'email', 'papel', 'unidade_id', 'ativo', 'created_at', 'updated_at')
            ->with('unidade:id,nome')
            ->orderBy('papel')
            ->orderBy('nome')
            ->get();
        $unidades = Unidade::where('ativo', true)->get();
        
        return view('administrativo.usuarios', compact('usuarios', 'unidades'));
    }

    public function salvarUsuario(Request $request)
    {
        $rules = [
            'nome' => 'required|string|max:255',
            'email' => 'required|email|unique:usuarios,email,' . $request->id,
            'papel' => 'required|in:superintendente,diretor,rh,administrativo',
        ];

        if (!$request->id) {
            $rules['senha'] = 'required|min:6';
        }

        if ($request->papel === 'diretor') {
            $rules['unidade_id'] = 'required|exists:unidades,id';
        }

        $request->validate($rules);

        $data = [
            'nome' => $request->nome,
            'email' => $request->email,
            'papel' => $request->papel,
            'unidade_id' => $request->papel === 'diretor' ? $request->unidade_id : null,
            'ativo' => $request->has('ativo'),
        ];

        if ($request->filled('senha')) {
            $data['senha'] = Hash::make($request->senha);
        }

        Usuario::updateOrCreate(['id' => $request->id], $data);

        return redirect('/admin/usuarios')->with('success', 'Usuário salvo!');
    }

    public function resetarSenha(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:usuarios,id',
            'nova_senha' => 'required|min:6',
        ]);

        Usuario::where('id', $request->id)->update([
            'senha' => Hash::make($request->nova_senha),
        ]);

        return redirect('/admin/usuarios')->with('success', 'Senha resetada!');
    }

    public function excluirUsuario($id)
    {
        if ($id == Auth::id()) {
            return back()->withErrors(['error' => 'Você não pode excluir seu próprio usuário']);
        }

        Usuario::findOrFail($id)->delete();
        return redirect('/admin/usuarios')->with('success', 'Usuário excluído!');
    }
}
