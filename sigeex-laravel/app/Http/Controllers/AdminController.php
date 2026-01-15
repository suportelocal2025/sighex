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
        $codigoRule = $request->id 
            ? 'required|string|max:50|unique:unidades,codigo,' . $request->id
            : 'required|string|max:50|unique:unidades,codigo';
            
        $request->validate([
            'nome' => 'required|string|max:255',
            'codigo' => $codigoRule,
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
        $uniqueRule = $request->id 
            ? 'required|string|max:50|unique:servidores,matricula,' . $request->id
            : 'required|string|max:50|unique:servidores,matricula';
            
        $request->validate([
            'nome' => 'required|string|max:255',
            'matricula' => $uniqueRule,
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
        $emailRule = $request->id 
            ? 'required|email|unique:usuarios,email,' . $request->id
            : 'required|email|unique:usuarios,email';
            
        $rules = [
            'nome' => 'required|string|max:255',
            'email' => $emailRule,
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

    public function salvarModulo(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'unidade_id' => 'required|exists:unidades,id',
            'descricao' => 'nullable|string|max:255',
        ]);

        Modulo::create([
            'nome' => $request->nome,
            'unidade_id' => $request->unidade_id,
            'descricao' => $request->descricao,
            'ativo' => $request->has('ativo'),
        ]);

        return redirect('/admin/unidade/' . $request->unidade_id)->with('success', 'Setor/Módulo/Raio criado!');
    }

    public function excluirModulo($id)
    {
        $modulo = Modulo::findOrFail($id);
        $unidadeId = $modulo->unidade_id;
        $modulo->delete();
        
        return redirect('/admin/unidade/' . $unidadeId)->with('success', 'Setor/Módulo/Raio excluído!');
    }

    public function importarUnidades(Request $request)
    {
        $request->validate([
            'arquivo' => 'required|file|mimes:csv,txt',
        ]);

        $arquivo = $request->file('arquivo');
        $conteudo = file_get_contents($arquivo->getPathname());
        $linhas = array_filter(explode("\n", $conteudo));
        
        $importados = 0;
        $erros = [];
        
        foreach ($linhas as $i => $linha) {
            if ($i === 0 && stripos($linha, 'codigo') !== false) continue;
            
            $linha = trim($linha);
            if (empty($linha)) continue;
            
            $dados = str_getcsv($linha, ',');
            if (count($dados) < 2) {
                $erros[] = "Linha " . ($i + 1) . ": formato inválido";
                continue;
            }
            
            $codigo = trim($dados[0]);
            $nome = trim($dados[1]);
            $endereco = isset($dados[2]) ? trim($dados[2]) : null;
            $telefone = isset($dados[3]) ? trim($dados[3]) : null;
            
            if (Unidade::where('codigo', $codigo)->exists()) {
                $erros[] = "Linha " . ($i + 1) . ": Código '{$codigo}' já existe";
                continue;
            }
            
            $unidade = Unidade::create([
                'codigo' => $codigo,
                'nome' => $nome,
                'endereco' => $endereco,
                'telefone' => $telefone,
                'ativo' => true,
            ]);
            
            foreach (['A', 'B', 'C', 'D'] as $letra) {
                Equipe::create([
                    'unidade_id' => $unidade->id,
                    'nome' => "Equipe $letra",
                ]);
            }
            
            $importados++;
        }
        
        $mensagem = "Importação concluída: {$importados} unidade(s) importada(s).";
        if (!empty($erros)) {
            $mensagem .= " Erros: " . implode('; ', array_slice($erros, 0, 5));
            if (count($erros) > 5) $mensagem .= "... e mais " . (count($erros) - 5) . " erros.";
        }
        
        return redirect('/admin')->with($erros ? 'warning' : 'success', $mensagem);
    }

    public function importarServidores(Request $request)
    {
        $request->validate([
            'arquivo' => 'required|file|mimes:csv,txt',
        ]);

        $arquivo = $request->file('arquivo');
        $conteudo = file_get_contents($arquivo->getPathname());
        $linhas = array_filter(explode("\n", $conteudo));
        
        $importados = 0;
        $erros = [];
        
        foreach ($linhas as $i => $linha) {
            if ($i === 0 && stripos($linha, 'matricula') !== false) continue;
            
            $linha = trim($linha);
            if (empty($linha)) continue;
            
            $dados = str_getcsv($linha, ',');
            if (count($dados) < 4) {
                $erros[] = "Linha " . ($i + 1) . ": formato inválido";
                continue;
            }
            
            $matricula = trim($dados[0]);
            $nome = trim($dados[1]);
            $codigoUnidade = trim($dados[2]);
            $cargo = trim($dados[3]);
            $escalaExtra = isset($dados[4]) ? (strtolower(trim($dados[4])) === 'sim') : true;
            $ativo = isset($dados[5]) ? (strtolower(trim($dados[5])) === 'ativo') : true;
            
            $unidade = Unidade::where('codigo', $codigoUnidade)->first();
            if (!$unidade) {
                $erros[] = "Linha " . ($i + 1) . ": Unidade '{$codigoUnidade}' não encontrada";
                continue;
            }
            
            if (Servidor::where('matricula', $matricula)->exists()) {
                $erros[] = "Linha " . ($i + 1) . ": Matrícula '{$matricula}' já existe";
                continue;
            }
            
            Servidor::create([
                'matricula' => $matricula,
                'nome' => $nome,
                'unidade_id' => $unidade->id,
                'cargo' => $cargo,
                'apto_escala_extra' => $escalaExtra,
                'ativo' => $ativo,
            ]);
            
            $importados++;
        }
        
        $mensagem = "Importação concluída: {$importados} servidor(es) importado(s).";
        if (!empty($erros)) {
            $mensagem .= " Erros: " . implode('; ', array_slice($erros, 0, 5));
            if (count($erros) > 5) $mensagem .= "... e mais " . (count($erros) - 5) . " erros.";
        }
        
        return redirect('/admin')->with($erros ? 'warning' : 'success', $mensagem);
    }
}
