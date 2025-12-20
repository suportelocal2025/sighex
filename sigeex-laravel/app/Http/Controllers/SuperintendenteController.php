<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Unidade;
use App\Models\OrcamentoGlobal;
use App\Models\DistribuicaoOrcamento;
use App\Models\LogDistribuicao;
use App\Models\Escala;
use Illuminate\Support\Facades\Auth;

class SuperintendenteController extends Controller
{
    public function dashboard(Request $request)
    {
        $ano = (int)$request->get('ano', date('Y'));
        $periodo = $request->get('periodo', 'ano');
        
        $orcamento = OrcamentoGlobal::where('ano', $ano)->first();
        $valorTotal = $orcamento?->valor_total ?? 0;
        $percentualReserva = $orcamento?->reserva_tecnica_percentual ?? 10;
        $reservaTecnica = $valorTotal * ($percentualReserva / 100);
        
        $totalDistribuido = DistribuicaoOrcamento::where('ano', $ano)->sum('valor_distribuido');
        $valorDisponivel = $valorTotal - $reservaTecnica - $totalDistribuido;
        
        $totalGasto = Escala::where('ano', $ano)
            ->where('status', 'executada')
            ->sum('valor_executado');
        
        $totalUnidades = Unidade::where('ativo', true)->count();
        
        $unidadesStats = Unidade::select('unidades.id', 'unidades.nome')
            ->selectRaw('COALESCE(d.valor_distribuido, 0) as orcamento_distribuido')
            ->selectRaw('COALESCE((SELECT SUM(valor_executado) FROM escalas WHERE unidade_id = unidades.id AND ano = ? AND status = \'executada\'), 0) as gasto_total', [$ano])
            ->selectRaw('COALESCE((SELECT SUM(a.horas) FROM alocacoes a INNER JOIN escalas e ON a.escala_id = e.id WHERE e.unidade_id = unidades.id AND e.ano = ? AND e.status IN (\'aprovada\', \'executada\')), 0) as horas_total', [$ano])
            ->leftJoin('distribuicao_orcamento as d', function($join) use ($ano) {
                $join->on('unidades.id', '=', 'd.unidade_id')
                     ->where('d.ano', '=', $ano);
            })
            ->orderBy('unidades.nome')
            ->get();

        return view('superintendente.dashboard', compact(
            'ano',
            'periodo',
            'orcamento',
            'reservaTecnica',
            'valorDisponivel',
            'totalDistribuido',
            'totalGasto',
            'totalUnidades',
            'unidadesStats'
        ));
    }

    public function orcamento()
    {
        $ano = date('Y');
        $orcamento = OrcamentoGlobal::where('ano', $ano)->first();
        
        return view('superintendente.orcamento', compact('orcamento', 'ano'));
    }

    public function salvarOrcamento(Request $request)
    {
        $request->validate([
            'ano' => 'required|integer',
            'valor_total' => 'required|numeric|min:0',
            'reserva_tecnica_percentual' => 'required|numeric|min:0|max:100',
        ]);

        OrcamentoGlobal::updateOrCreate(
            ['ano' => $request->ano],
            [
                'valor_total' => $request->valor_total,
                'reserva_tecnica_percentual' => $request->reserva_tecnica_percentual,
            ]
        );

        return redirect('/superintendente/orcamento')->with('success', 'Orçamento atualizado!');
    }

    public function distribuicao()
    {
        $ano = date('Y');
        $orcamento = OrcamentoGlobal::where('ano', $ano)->first();
        $unidades = Unidade::where('ativo', true)->get();
        
        $distribuicoes = DistribuicaoOrcamento::with('unidade')
            ->where('ano', $ano)
            ->get()
            ->keyBy('unidade_id');

        $valorDistribuido = $distribuicoes->sum('valor_distribuido');
        $valorTotal = $orcamento?->valor_total ?? 0;
        $reservaTecnica = $valorTotal * (($orcamento?->reserva_tecnica_percentual ?? 10) / 100);
        $valorDisponivel = $valorTotal - $reservaTecnica - $valorDistribuido;

        return view('superintendente.distribuicao', compact(
            'unidades',
            'distribuicoes',
            'valorDisponivel',
            'valorTotal',
            'reservaTecnica',
            'ano'
        ));
    }

    public function salvarDistribuicao(Request $request)
    {
        $request->validate([
            'unidade_id' => 'required|exists:unidades,id',
            'valor' => 'required|numeric|min:0',
        ]);

        $ano = date('Y');
        $unidadeId = $request->unidade_id;
        
        $distribuicao = DistribuicaoOrcamento::firstOrNew([
            'unidade_id' => $unidadeId,
            'ano' => $ano,
        ]);

        $valorAnterior = $distribuicao->valor_distribuido ?? 0;
        $distribuicao->valor_distribuido = $request->valor;
        $distribuicao->save();

        LogDistribuicao::create([
            'unidade_id' => $unidadeId,
            'ano' => $ano,
            'valor_anterior' => $valorAnterior,
            'valor_novo' => $request->valor,
            'usuario_id' => Auth::id(),
        ]);

        return redirect('/superintendente/distribuicao')->with('success', 'Distribuição atualizada!');
    }

    public function relatorios()
    {
        $anos = OrcamentoGlobal::pluck('ano')->unique()->sort()->reverse();
        return view('superintendente.relatorios', compact('anos'));
    }
}
