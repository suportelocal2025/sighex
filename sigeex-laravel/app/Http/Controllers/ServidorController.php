<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Servidor;
use App\Models\Unidade;
use Illuminate\Support\Facades\Auth;

class ServidorController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $busca = $request->get('busca');
        $servidores = collect();
        
        if ($busca && strlen($busca) >= 3) {
            $servidores = Servidor::with('unidade')
                ->where(function($q) use ($busca) {
                    $q->whereRaw('LOWER(nome) LIKE ?', ['%' . strtolower($busca) . '%'])
                      ->orWhereRaw('LOWER(matricula) LIKE ?', ['%' . strtolower($busca) . '%']);
                })
                ->orderBy('nome')
                ->limit(50)
                ->get();
        }
        
        $podeEditar = in_array($user->papel, ['rh', 'superintendente', 'administrativo']);
        $podeImportar = $user->papel === 'administrativo';
        $unidades = Unidade::where('ativo', true)->orderBy('nome')->get();
        
        return view('servidores.index', compact('servidores', 'busca', 'podeEditar', 'podeImportar', 'unidades'));
    }
    
    public function buscar(Request $request)
    {
        $busca = $request->get('q');
        
        if (!$busca || strlen($busca) < 3) {
            return response()->json([]);
        }
        
        $servidores = Servidor::with('unidade')
            ->where(function($q) use ($busca) {
                $q->whereRaw('LOWER(nome) LIKE ?', ['%' . strtolower($busca) . '%'])
                  ->orWhereRaw('LOWER(matricula) LIKE ?', ['%' . strtolower($busca) . '%']);
            })
            ->orderBy('nome')
            ->limit(20)
            ->get()
            ->map(function($s) {
                return [
                    'id' => $s->id,
                    'nome' => $s->nome,
                    'matricula' => $s->matricula,
                    'cargo' => $s->cargo,
                    'unidade' => $s->unidade->nome ?? 'N/A',
                    'ativo' => $s->ativo,
                    'apto' => $s->apto_escala_extra,
                    'disponivel' => $s->isDisponivelParaEscala(),
                    'motivo_inativo' => $s->motivo_inativo,
                    'inativo_inicio' => $s->inativo_inicio?->format('d/m/Y'),
                    'inativo_fim' => $s->inativo_fim?->format('d/m/Y'),
                ];
            });
        
        return response()->json($servidores);
    }
    
    public function alterarStatus(Request $request)
    {
        $user = Auth::user();
        
        if (!in_array($user->papel, ['rh', 'superintendente', 'administrativo'])) {
            return redirect()->back()->with('error', 'Você não tem permissão para alterar status de servidores.');
        }
        
        $request->validate([
            'servidor_id' => 'required|exists:servidores,id',
            'ativo' => 'required|boolean',
            'apto_escala_extra' => 'required|boolean',
            'motivo_inativo' => 'nullable|string|max:255',
            'inativo_inicio' => 'nullable|date',
            'inativo_fim' => 'nullable|date|after_or_equal:inativo_inicio',
            'inativo_indefinido' => 'nullable|boolean',
        ]);
        
        $servidor = Servidor::findOrFail($request->servidor_id);
        
        $servidor->ativo = $request->ativo;
        $servidor->apto_escala_extra = $request->apto_escala_extra;
        
        if (!$request->ativo) {
            $servidor->motivo_inativo = $request->motivo_inativo;
            $servidor->inativo_inicio = $request->inativo_inicio;
            $servidor->inativo_fim = $request->inativo_fim;
            $servidor->inativo_indefinido = $request->inativo_indefinido ?? false;
        } else {
            $servidor->motivo_inativo = null;
            $servidor->inativo_inicio = null;
            $servidor->inativo_fim = null;
            $servidor->inativo_indefinido = false;
        }
        
        $servidor->save();
        
        return redirect()->back()->with('success', 'Status do servidor atualizado com sucesso!');
    }
    
    public function importarCsv(Request $request)
    {
        $user = Auth::user();
        
        if ($user->papel !== 'administrativo') {
            return redirect()->back()->with('error', 'Apenas administradores podem importar CSV.');
        }
        
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
        ]);
        
        $file = $request->file('csv_file');
        $handle = fopen($file->getPathname(), 'r');
        
        $header = fgetcsv($handle, 0, ';');
        if (!$header) {
            $header = fgetcsv($handle, 0, ',');
            rewind($handle);
            fgetcsv($handle);
        }
        
        $unidades = Unidade::pluck('id', 'nome')->toArray();
        $importados = 0;
        $erros = 0;
        
        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            if (count($row) < 6) {
                $row = str_getcsv(implode(';', $row), ',');
            }
            
            if (count($row) < 6) {
                $erros++;
                continue;
            }
            
            $matricula = trim($row[0] ?? '');
            $nome = trim($row[1] ?? '');
            $unidadeNome = trim($row[2] ?? '');
            $cargo = trim($row[3] ?? '');
            $escalaExtra = strtolower(trim($row[4] ?? '')) === 'sim' || $row[4] === '1' || $row[4] === 'true';
            $status = strtolower(trim($row[5] ?? '')) === 'ativo' || $row[5] === '1' || $row[5] === 'true';
            
            if (empty($matricula) || empty($nome)) {
                $erros++;
                continue;
            }
            
            $unidadeId = $unidades[$unidadeNome] ?? null;
            if (!$unidadeId) {
                $unidade = Unidade::whereRaw('LOWER(nome) LIKE ?', ['%' . strtolower($unidadeNome) . '%'])->first();
                $unidadeId = $unidade?->id;
            }
            
            Servidor::updateOrCreate(
                ['matricula' => $matricula],
                [
                    'nome' => $nome,
                    'unidade_id' => $unidadeId,
                    'cargo' => $cargo,
                    'apto_escala_extra' => $escalaExtra,
                    'ativo' => $status,
                ]
            );
            
            $importados++;
        }
        
        fclose($handle);
        
        return redirect()->back()->with('success', "Importação concluída! {$importados} servidores importados" . ($erros > 0 ? ", {$erros} linhas com erro." : "."));
    }
    
    public function cadastrar(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'nome' => 'required|string|max:255',
            'matricula' => 'required|string|max:50|unique:servidores,matricula',
            'cargo' => 'nullable|string|max:100',
            'unidade_id' => 'required|exists:unidades,id',
        ]);
        
        $apto = in_array($user->papel, ['rh', 'superintendente', 'administrativo']);
        
        Servidor::create([
            'nome' => $request->nome,
            'matricula' => $request->matricula,
            'cargo' => $request->cargo,
            'unidade_id' => $request->unidade_id,
            'ativo' => $apto,
            'apto_escala_extra' => false,
        ]);
        
        $msg = $apto 
            ? 'Servidor cadastrado com sucesso! Aguardando aprovação do RH para escala extra.'
            : 'Servidor cadastrado. Aguardando RH marcar como ATIVO e APTO para escala.';
        
        return redirect()->back()->with('success', $msg);
    }
}
