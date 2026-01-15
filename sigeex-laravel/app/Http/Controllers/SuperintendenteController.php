<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Unidade;
use App\Models\Usuario;
use App\Models\OrcamentoGlobal;
use App\Models\DistribuicaoOrcamento;
use App\Models\LogDistribuicao;
use App\Models\Escala;
use App\Mail\MarginViolationAlert;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

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
            ->selectRaw('COALESCE(d.margin_percentual, 10) as margin_percentual')
            ->selectRaw('COALESCE((SELECT SUM(valor_executado) FROM escalas WHERE unidade_id = unidades.id AND ano = ? AND status = \'executada\'), 0) as gasto_total', [$ano])
            ->selectRaw('COALESCE((SELECT SUM(a.horas) FROM alocacoes a INNER JOIN escalas e ON a.escala_id = e.id WHERE e.unidade_id = unidades.id AND e.ano = ? AND e.status IN (\'aprovada\', \'executada\')), 0) as horas_total', [$ano])
            ->leftJoin('distribuicao_orcamento as d', function($join) use ($ano) {
                $join->on('unidades.id', '=', 'd.unidade_id')
                     ->where('d.ano', '=', $ano);
            })
            ->where('unidades.ativo', true)
            ->orderBy('unidades.nome')
            ->get();

        $escalasAguardandoAprovacao = collect();

        $alertasAmarelo = [];
        $alertasVermelho = [];
        $mesAtual = date('n');
        $nomesMeses = ['', 'Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
        
        foreach ($unidadesStats as $unidade) {
            if ($unidade->orcamento_distribuido <= 0) continue;
            
            $orcamentoMensal = $unidade->orcamento_distribuido / 12;
            $marginPerc = $unidade->margin_percentual;
            $saldoAcumulado = 0;
            
            for ($m = 1; $m <= $mesAtual; $m++) {
                $gastoMes = Escala::where('unidade_id', $unidade->id)
                    ->where('ano', $ano)
                    ->where('mes', $m)
                    ->where('status', 'executada')
                    ->sum('valor_executado') ?? 0;
                
                $orcamentoAjustado = $orcamentoMensal + $saldoAcumulado;
                $limiteAjustado = $orcamentoAjustado * (1 + $marginPerc / 100);
                
                $saldoMes = $orcamentoAjustado - $gastoMes;
                $saldoAcumulado = $saldoMes;
                
                $passouPrevisto = $gastoMes > $orcamentoAjustado;
                $usouMargem = $passouPrevisto && $gastoMes <= $limiteAjustado;
                $excedeuMargem = $gastoMes > $limiteAjustado;
                
                if ($usouMargem) {
                    $alertasAmarelo[] = [
                        'unidade_id' => $unidade->id,
                        'unidade_nome' => $unidade->nome,
                        'mes' => $m,
                        'mes_nome' => $nomesMeses[$m],
                        'orcamento' => $orcamentoAjustado,
                        'limite' => $limiteAjustado,
                        'gasto' => $gastoMes,
                        'excedente' => $gastoMes - $orcamentoAjustado,
                    ];
                } elseif ($excedeuMargem) {
                    $alertasVermelho[] = [
                        'unidade_id' => $unidade->id,
                        'unidade_nome' => $unidade->nome,
                        'mes' => $m,
                        'mes_nome' => $nomesMeses[$m],
                        'orcamento' => $orcamentoAjustado,
                        'limite' => $limiteAjustado,
                        'gasto' => $gastoMes,
                        'excedente' => $gastoMes - $limiteAjustado,
                    ];
                }
            }
        }

        return view('superintendente.dashboard', compact(
            'ano',
            'periodo',
            'orcamento',
            'reservaTecnica',
            'valorDisponivel',
            'totalDistribuido',
            'totalGasto',
            'totalUnidades',
            'unidadesStats',
            'alertasAmarelo',
            'alertasVermelho',
            'escalasAguardandoAprovacao'
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
            'margin_percentual' => 'required|numeric|min:0|max:100',
        ]);

        $ano = date('Y');
        $unidadeId = $request->unidade_id;
        
        $distribuicao = DistribuicaoOrcamento::firstOrNew([
            'unidade_id' => $unidadeId,
            'ano' => $ano,
        ]);

        $valorAnterior = $distribuicao->valor_distribuido ?? 0;
        $distribuicao->valor_distribuido = $request->valor;
        $distribuicao->margin_percentual = $request->margin_percentual;
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

    public function escalas(Request $request)
    {
        $ano = (int)$request->get('ano', date('Y'));
        $status = $request->get('status', 'todos');

        $query = Escala::with('unidade')
            ->where('ano', $ano)
            ->whereIn('status', ['pendente', 'aprovada', 'executada', 'rejeitada']);

        if ($status !== 'todos') {
            $query->where('status', $status);
        }

        $escalas = $query->orderByDesc('updated_at')->get();

        return view('superintendente.escalas', compact('escalas', 'ano', 'status'));
    }

    public function detalharEscala(string $id)
    {
        $escala = Escala::with(['unidade', 'alocacoes.servidor', 'alocacoes.equipe', 'alocacoes.modulo'])
            ->findOrFail($id);

        $alocacoes = \DB::table('alocacoes')
            ->join('servidores', 'alocacoes.servidor_id', '=', 'servidores.id')
            ->leftJoin('equipes', 'alocacoes.equipe_id', '=', 'equipes.id')
            ->leftJoin('modulos', 'alocacoes.modulo_id', '=', 'modulos.id')
            ->where('alocacoes.escala_id', $id)
            ->select(
                'alocacoes.*',
                'servidores.nome as servidor_nome',
                'servidores.matricula',
                'equipes.nome as equipe_nome',
                'modulos.nome as modulo_nome'
            )
            ->orderBy('servidores.nome')
            ->orderBy('alocacoes.dia')
            ->get();

        $resumoPorServidor = $alocacoes->groupBy('servidor_id')->map(function ($alocacoesServidor) {
            $primeiro = $alocacoesServidor->first();
            return [
                'nome' => $primeiro->servidor_nome,
                'matricula' => $primeiro->matricula,
                'dias' => $alocacoesServidor->pluck('dia')->sort()->values()->toArray(),
                'total_horas' => $alocacoesServidor->sum(fn($a) => ($a->horas ?? 0) + ($a->horas_abono ?? 0)),
            ];
        });

        return view('superintendente.detalhar-escala', compact('escala', 'alocacoes', 'resumoPorServidor'));
    }

    public function enviarAlertaEmail(Request $request)
    {
        $ano = (int)$request->get('ano', date('Y'));
        $tipo = $request->get('tipo', 'todos');
        
        $result = $this->calcularAlertasPorTipo($ano);
        
        $alertas = match($tipo) {
            'amarelo' => $result['amarelo'],
            'vermelho' => $result['vermelho'],
            default => array_merge($result['amarelo'], $result['vermelho']),
        };
        
        if (empty($alertas)) {
            return redirect('/superintendente')->with('info', 'Nenhum alerta para enviar.');
        }
        
        $superintendente = Auth::user();
        
        if (empty($superintendente->email)) {
            return redirect('/superintendente')->with('error', 'Configure seu email no perfil para receber alertas.');
        }
        
        $tipoLabel = match($tipo) {
            'amarelo' => 'ALERTA AMARELO - Acima do Previsto',
            'vermelho' => 'ALERTA VERMELHO - Margem Excedida',
            default => 'Alertas de Margem',
        };
        
        try {
            $mensagem = "{$tipoLabel} - SIGEEX\n\nAno: {$ano}\n\n";
            foreach ($alertas as $a) {
                $mensagem .= "Unidade: {$a['unidade_nome']}\n";
                $mensagem .= "Mês: {$a['mes_nome']}/{$ano}\n";
                $mensagem .= "Previsto: R$ " . number_format($a['orcamento'], 2, ',', '.') . "\n";
                $mensagem .= "Limite: R$ " . number_format($a['limite'], 2, ',', '.') . "\n";
                $mensagem .= "Gasto: R$ " . number_format($a['gasto'], 2, ',', '.') . "\n";
                $mensagem .= "Excedente: R$ " . number_format($a['excedente'], 2, ',', '.') . "\n\n";
            }
            
            Mail::raw($mensagem, function ($message) use ($superintendente, $tipoLabel) {
                $message->to($superintendente->email)
                        ->subject("[SIGEEX] {$tipoLabel}");
            });
            
            return redirect('/superintendente')->with('success', 'Email de alerta enviado para ' . $superintendente->email);
        } catch (\Exception $e) {
            Log::error('Erro ao enviar email de alerta de margem: ' . $e->getMessage());
            return redirect('/superintendente')->with('error', 'Erro ao enviar email. Verifique a configuração de email do sistema.');
        }
    }
    
    private function calcularAlertasPorTipo(int $ano): array
    {
        $alertasAmarelo = [];
        $alertasVermelho = [];
        $mesAtual = date('n');
        $nomesMeses = ['', 'Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
        
        $unidades = Unidade::select('unidades.id', 'unidades.nome')
            ->selectRaw('COALESCE(d.valor_distribuido, 0) as orcamento_distribuido')
            ->selectRaw('COALESCE(d.margin_percentual, 10) as margin_percentual')
            ->leftJoin('distribuicao_orcamento as d', function($join) use ($ano) {
                $join->on('unidades.id', '=', 'd.unidade_id')
                     ->where('d.ano', '=', $ano);
            })
            ->where('unidades.ativo', true)
            ->get();
        
        foreach ($unidades as $unidade) {
            if ($unidade->orcamento_distribuido <= 0) continue;
            
            $orcamentoMensal = $unidade->orcamento_distribuido / 12;
            $marginPerc = $unidade->margin_percentual;
            $saldoAcumulado = 0;
            
            for ($m = 1; $m <= $mesAtual; $m++) {
                $gastoMes = Escala::where('unidade_id', $unidade->id)
                    ->where('ano', $ano)
                    ->where('mes', $m)
                    ->where('status', 'executada')
                    ->sum('valor_executado') ?? 0;
                
                $orcamentoAjustado = $orcamentoMensal + $saldoAcumulado;
                $limiteAjustado = $orcamentoAjustado * (1 + $marginPerc / 100);
                
                $saldoMes = $orcamentoAjustado - $gastoMes;
                $saldoAcumulado = $saldoMes;
                
                $passouPrevisto = $gastoMes > $orcamentoAjustado;
                $usouMargem = $passouPrevisto && $gastoMes <= $limiteAjustado;
                $excedeuMargem = $gastoMes > $limiteAjustado;
                
                if ($usouMargem) {
                    $alertasAmarelo[] = [
                        'unidade_id' => $unidade->id,
                        'unidade_nome' => $unidade->nome,
                        'mes' => $m,
                        'mes_nome' => $nomesMeses[$m],
                        'orcamento' => $orcamentoAjustado,
                        'limite' => $limiteAjustado,
                        'gasto' => $gastoMes,
                        'excedente' => $gastoMes - $orcamentoAjustado,
                    ];
                } elseif ($excedeuMargem) {
                    $alertasVermelho[] = [
                        'unidade_id' => $unidade->id,
                        'unidade_nome' => $unidade->nome,
                        'mes' => $m,
                        'mes_nome' => $nomesMeses[$m],
                        'orcamento' => $orcamentoAjustado,
                        'limite' => $limiteAjustado,
                        'gasto' => $gastoMes,
                        'excedente' => $gastoMes - $limiteAjustado,
                    ];
                }
            }
        }
        
        return ['amarelo' => $alertasAmarelo, 'vermelho' => $alertasVermelho];
    }

    public function aprovarEscalaExcedente(Request $request)
    {
        $request->validate([
            'escala_id' => 'required|exists:escalas,id',
        ]);

        $escala = Escala::findOrFail($request->escala_id);
        
        $budgetInfo = $this->recalcularBudget($escala);
        
        $escala->update([
            'valor_previsto' => $budgetInfo['valor_previsto'],
            'orcamento_mes' => $budgetInfo['orcamento_mes'],
            'limite_margem' => $budgetInfo['limite_margem'],
            'usa_margem' => $budgetInfo['usa_margem'],
            'excede_margem' => $budgetInfo['excede_margem'],
        ]);
        
        if (!$budgetInfo['excede_margem']) {
            return redirect('/superintendente/escalas')->with('info', 'Os valores foram recalculados e a escala não excede mais a margem. Aprovação normal pelo RH.');
        }
        
        $escala->update([
            'status' => 'aprovada',
            'aprovado_por' => Auth::id(),
            'data_aprovacao' => now(),
            'requer_aprovacao_super' => false,
        ]);

        return redirect('/superintendente/escalas')->with('success', 'Escala aprovada com exceção de margem orçamentária!');
    }
    
    private function recalcularBudget(Escala $escala): array
    {
        $unidadeId = $escala->unidade_id;
        $ano = $escala->ano;
        $mes = $escala->mes;
        
        $totalHoras = \App\Models\Alocacao::where('escala_id', $escala->id)
            ->sum(\DB::raw('COALESCE(horas, 0) + COALESCE(horas_abono, 0)'));
        
        $valorHora = 50;
        $valorPrevisto = $totalHoras * $valorHora;
        
        $distribuicao = DistribuicaoOrcamento::where('unidade_id', $unidadeId)
            ->where('ano', $ano)
            ->first();
        
        $orcamentoAnual = $distribuicao?->valor_distribuido ?? 0;
        $marginPercentual = $distribuicao?->margin_percentual ?? 10;
        
        $gastosPorMes = [];
        for ($m = 1; $m <= 12; $m++) {
            $gastosPorMes[$m] = Escala::where('unidade_id', $unidadeId)
                ->where('ano', $ano)
                ->where('mes', $m)
                ->where('id', '!=', $escala->id)
                ->where('status', 'executada')
                ->sum('valor_executado') ?? 0;
        }
        
        $orcamentoRestante = $orcamentoAnual;
        for ($m = 1; $m < $mes; $m++) {
            $mesesRestantes = 12 - $m + 1;
            $alocacaoMes = $orcamentoRestante / $mesesRestantes;
            $gastoMes = $gastosPorMes[$m];
            
            if ($gastoMes > 0) {
                $orcamentoRestante -= $gastoMes;
            } else {
                $orcamentoRestante -= $alocacaoMes;
            }
        }
        
        $mesesRestantes = 12 - $mes + 1;
        $orcamentoMes = $orcamentoRestante / $mesesRestantes;
        $limiteComMargem = $orcamentoMes * (1 + $marginPercentual / 100);
        
        $usaMargem = $valorPrevisto > $orcamentoMes && $valorPrevisto <= $limiteComMargem;
        $excedeMargem = $valorPrevisto > $limiteComMargem;
        
        return [
            'valor_previsto' => $valorPrevisto,
            'orcamento_mes' => $orcamentoMes,
            'limite_margem' => $limiteComMargem,
            'usa_margem' => $usaMargem,
            'excede_margem' => $excedeMargem,
        ];
    }

    public function rejeitarEscalaExcedente(Request $request)
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

        return redirect('/superintendente/escalas')->with('success', 'Escala rejeitada!');
    }

    private function calcularViolacoes(int $ano): array
    {
        $unidades = Unidade::select('unidades.id', 'unidades.nome')
            ->selectRaw('COALESCE(d.valor_distribuido, 0) as orcamento_distribuido')
            ->selectRaw('COALESCE(d.margin_percentual, 10) as margin_percentual')
            ->leftJoin('distribuicao_orcamento as d', function($join) use ($ano) {
                $join->on('unidades.id', '=', 'd.unidade_id')
                     ->where('d.ano', '=', $ano);
            })
            ->where('unidades.ativo', true)
            ->get();

        $alertasViolacao = [];
        $mesAtual = date('n');
        $nomesMeses = ['', 'Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
        
        foreach ($unidades as $unidade) {
            if ($unidade->orcamento_distribuido <= 0) continue;
            
            $orcamentoMensal = $unidade->orcamento_distribuido / 12;
            $marginPerc = $unidade->margin_percentual;
            $saldoAcumulado = 0;
            
            for ($m = 1; $m <= $mesAtual; $m++) {
                $gastoMes = Escala::where('unidade_id', $unidade->id)
                    ->where('ano', $ano)
                    ->where('mes', $m)
                    ->where('status', 'executada')
                    ->sum('valor_executado') ?? 0;
                
                $orcamentoAjustado = $orcamentoMensal + $saldoAcumulado;
                $limiteAjustado = $orcamentoAjustado * (1 + $marginPerc / 100);
                
                $saldoMes = $orcamentoAjustado - $gastoMes;
                $saldoAcumulado = $saldoMes;
                
                if ($gastoMes > $limiteAjustado) {
                    $alertasViolacao[] = [
                        'unidade_id' => $unidade->id,
                        'unidade_nome' => $unidade->nome,
                        'mes' => $m,
                        'mes_nome' => $nomesMeses[$m],
                        'orcamento' => $orcamentoAjustado,
                        'limite' => $limiteAjustado,
                        'gasto' => $gastoMes,
                        'excedente' => $gastoMes - $limiteAjustado,
                    ];
                }
            }
        }
        
        return $alertasViolacao;
    }
}
