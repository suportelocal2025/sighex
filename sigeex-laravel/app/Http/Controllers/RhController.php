<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Escala;
use App\Models\Alocacao;
use App\Models\EscalaEquipeServidor;
use App\Models\DistribuicaoOrcamento;
use Illuminate\Support\Facades\Auth;

class RhController extends Controller
{
    public function dashboard()
    {
        $ano = date('Y');
        
        $escalas = Escala::with('unidade')
            ->whereIn('status', ['pendente', 'aprovada', 'rejeitada', 'executada'])
            ->where('ano', $ano)
            ->orderByRaw("CASE status 
                WHEN 'pendente' THEN 1 
                WHEN 'aprovada' THEN 2 
                WHEN 'executada' THEN 3 
                WHEN 'rejeitada' THEN 4 
                END")
            ->orderBy('mes', 'desc')
            ->get();

        $pendentes = $escalas->where('status', 'pendente')->count();
        $aprovadas = $escalas->where('status', 'aprovada')->count();
        $executadas = $escalas->where('status', 'executada')->count();

        return view('rh.dashboard', compact('escalas', 'pendentes', 'aprovadas', 'executadas', 'ano'));
    }

    public function escalas(Request $request)
    {
        $status = $request->get('status', 'pendente');
        $ano = $request->get('ano', date('Y'));

        $query = Escala::with('unidade')->where('ano', $ano);
        
        if ($status !== 'todos') {
            $query->where('status', $status);
        }

        $escalas = $query->orderBy('mes', 'desc')->get();

        return view('rh.escalas', compact('escalas', 'status', 'ano'));
    }

    public function detalharEscala($id)
    {
        $escala = Escala::with(['unidade', 'alocacoes.servidor', 'equipeServidores.servidor', 'equipeServidores.equipe'])
            ->findOrFail($id);

        $servidores = EscalaEquipeServidor::with(['servidor', 'equipe', 'modulo'])
            ->where('escala_id', $id)
            ->get();

        $alocacoes = Alocacao::where('escala_id', $id)->get();

        return view('rh.detalhar-escala', compact('escala', 'servidores', 'alocacoes'));
    }

    public function aprovarEscala(Request $request)
    {
        $request->validate([
            'escala_id' => 'required|exists:escalas,id',
        ]);

        $escala = Escala::findOrFail($request->escala_id);
        $escala->update([
            'status' => 'aprovada',
            'aprovado_por' => Auth::id(),
            'data_aprovacao' => now(),
        ]);

        return redirect('/rh/escalas')->with('success', 'Escala aprovada!');
    }

    public function rejeitarEscala(Request $request)
    {
        $request->validate([
            'escala_id' => 'required|exists:escalas,id',
            'motivo_rejeicao' => 'required|string|min:10',
        ]);

        $escala = Escala::findOrFail($request->escala_id);
        $escala->update([
            'status' => 'rejeitada',
            'motivo_rejeicao' => $request->motivo_rejeicao,
            'aprovado_por' => Auth::id(),
            'data_aprovacao' => now(),
        ]);

        return redirect('/rh/escalas')->with('success', 'Escala rejeitada!');
    }

    public function executarEscala(Request $request)
    {
        $request->validate([
            'escala_id' => 'required|exists:escalas,id',
            'valor_executado' => 'required|numeric|min:0',
        ]);

        $escala = Escala::findOrFail($request->escala_id);
        $escala->update([
            'status' => 'executada',
            'valor_executado' => $request->valor_executado,
        ]);

        $distribuicao = DistribuicaoOrcamento::firstOrCreate(
            ['unidade_id' => $escala->unidade_id, 'ano' => $escala->ano],
            ['valor_distribuido' => 0, 'valor_gasto' => 0]
        );

        $distribuicao->increment('valor_gasto', $request->valor_executado);

        return redirect('/rh/escalas')->with('success', 'Escala marcada como executada!');
    }

    public function relatorios()
    {
        return view('rh.relatorios');
    }
}
