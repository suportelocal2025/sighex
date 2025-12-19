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
    public function dashboard()
    {
        $ano = date('Y');
        
        $orcamento = OrcamentoGlobal::where('ano', $ano)->first();
        $valorTotal = $orcamento?->valor_total ?? 0;
        $reservaTecnica = $valorTotal * (($orcamento?->reserva_tecnica_percentual ?? 10) / 100);
        $valorDistribuido = DistribuicaoOrcamento::where('ano', $ano)->sum('valor_distribuido');
        $valorGasto = DistribuicaoOrcamento::where('ano', $ano)->sum('valor_gasto');
        $valorDisponivel = $valorTotal - $reservaTecnica - $valorDistribuido;

        $unidades = Unidade::where('ativo', true)->count();
        $escalasAprovadas = Escala::where('ano', $ano)->where('status', 'aprovada')->count();
        $escalasExecutadas = Escala::where('ano', $ano)->where('status', 'executada')->count();

        $distribuicoes = DistribuicaoOrcamento::with('unidade')
            ->where('ano', $ano)
            ->get();

        return view('superintendente.dashboard', compact(
            'valorTotal',
            'reservaTecnica',
            'valorDistribuido',
            'valorGasto',
            'valorDisponivel',
            'unidades',
            'escalasAprovadas',
            'escalasExecutadas',
            'distribuicoes',
            'ano'
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
