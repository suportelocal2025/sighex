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
use Illuminate\Support\Facades\DB;
use App\Models\Servidor;
use App\Models\Alocacao;

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
        $nomesMeses = ['', 'Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
        
        $escalasExecutadas = Escala::where('ano', $ano)
            ->where('status', 'executada')
            ->where(function($q) {
                $q->where('usa_margem', true)->orWhere('excede_margem', true);
            })
            ->with('unidade')
            ->get();
        
        foreach ($escalasExecutadas as $escala) {
            $alerta = [
                'unidade_id' => $escala->unidade_id,
                'unidade_nome' => $escala->unidade->nome ?? 'N/A',
                'mes' => $escala->mes,
                'mes_nome' => $nomesMeses[$escala->mes],
                'orcamento' => $escala->orcamento_mes ?? 0,
                'limite' => $escala->limite_margem ?? 0,
                'gasto' => $escala->valor_executado ?? 0,
                'excedente' => ($escala->valor_executado ?? 0) - ($escala->orcamento_mes ?? 0),
                'escala_id' => $escala->id,
            ];
            
            if ($escala->excede_margem) {
                $alerta['excedente'] = ($escala->valor_executado ?? 0) - ($escala->limite_margem ?? 0);
                $alertasVermelho[] = $alerta;
            } elseif ($escala->usa_margem) {
                $alertasAmarelo[] = $alerta;
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

    public function alertas(Request $request)
    {
        $ano = (int)$request->get('ano', date('Y'));
        $mes = $request->get('mes');
        $unidadeId = $request->get('unidade_id');
        $tipo = $request->get('tipo');
        
        $unidades = Unidade::where('ativo', true)->orderBy('nome')->get();
        
        $result = $this->calcularAlertasPorTipo($ano);
        
        $alertasAmarelo = collect($result['amarelo']);
        $alertasVermelho = collect($result['vermelho']);
        
        if ($mes) {
            $alertasAmarelo = $alertasAmarelo->filter(fn($a) => $a['mes'] == $mes);
            $alertasVermelho = $alertasVermelho->filter(fn($a) => $a['mes'] == $mes);
        }
        
        if ($unidadeId) {
            $alertasAmarelo = $alertasAmarelo->filter(fn($a) => $a['unidade_id'] == $unidadeId);
            $alertasVermelho = $alertasVermelho->filter(fn($a) => $a['unidade_id'] == $unidadeId);
        }
        
        if ($tipo === 'amarelo') {
            $alertasVermelho = collect();
        } elseif ($tipo === 'vermelho') {
            $alertasAmarelo = collect();
        }
        
        return view('superintendente.alertas', [
            'ano' => $ano,
            'mes' => $mes,
            'unidadeId' => $unidadeId,
            'tipo' => $tipo,
            'unidades' => $unidades,
            'alertasAmarelo' => $alertasAmarelo->values()->toArray(),
            'alertasVermelho' => $alertasVermelho->values()->toArray(),
        ]);
    }

    public function relatorioHoras(Request $request)
    {
        $ano = $request->get('ano', date('Y'));
        $mesInicio = $request->get('mes_inicio', 1);
        $mesFim = $request->get('mes_fim', date('n'));
        $unidadeId = $request->get('unidade_id', '');
        $servidorId = $request->get('servidor_id', '');
        
        $unidades = Unidade::where('ativo', true)->orderBy('nome')->get();
        $unidadeSelecionada = $unidadeId ? Unidade::find($unidadeId) : null;
        $servidorSelecionado = $servidorId ? Servidor::find($servidorId) : null;
        
        $query = Alocacao::select(
                'servidores.id as servidor_id',
                'servidores.matricula',
                'servidores.nome as servidor_nome',
                'unidades.nome as unidade_nome',
                DB::raw("SUM(CASE WHEN UPPER(alocacoes.tipo_extra) = 'DIURNA' THEN alocacoes.horas ELSE 0 END) as horas_diurnas"),
                DB::raw("SUM(CASE WHEN UPPER(alocacoes.tipo_extra) = 'NOTURNA' THEN alocacoes.horas ELSE 0 END) as horas_noturnas"),
                DB::raw("SUM(alocacoes.horas) as total_horas"),
                DB::raw("COUNT(alocacoes.id) as dias_alocados")
            )
            ->join('servidores', 'alocacoes.servidor_id', '=', 'servidores.id')
            ->join('escalas', 'alocacoes.escala_id', '=', 'escalas.id')
            ->join('unidades', 'escalas.unidade_id', '=', 'unidades.id')
            ->where('escalas.ano', $ano)
            ->whereBetween('escalas.mes', [$mesInicio, $mesFim])
            ->whereIn('escalas.status', ['aprovada', 'executada']);
        
        if ($unidadeId) {
            $query->where('escalas.unidade_id', $unidadeId);
        }
        
        if ($servidorId) {
            $query->where('servidores.id', $servidorId);
        }
        
        $dados = $query->groupBy('servidores.id', 'servidores.matricula', 'servidores.nome', 'unidades.nome')
            ->orderBy('servidores.nome')
            ->get();
        
        return view('superintendente.relatorio-horas', compact('ano', 'mesInicio', 'mesFim', 'unidadeId', 'servidorId', 'unidades', 'unidadeSelecionada', 'servidorSelecionado', 'dados'));
    }

    public function exportarRelatorioHorasExcel(Request $request)
    {
        $meses = ['', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 
                  'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
        
        $ano = $request->get('ano', date('Y'));
        $mesInicio = $request->get('mes_inicio', 1);
        $mesFim = $request->get('mes_fim', date('n'));
        $unidadeId = $request->get('unidade_id', '');
        $servidorId = $request->get('servidor_id', '');
        
        $unidadeSelecionada = $unidadeId ? Unidade::find($unidadeId) : null;
        
        $query = Alocacao::select(
                'servidores.id as servidor_id',
                'servidores.matricula',
                'servidores.nome as servidor_nome',
                'unidades.nome as unidade_nome',
                DB::raw("SUM(CASE WHEN UPPER(alocacoes.tipo_extra) = 'DIURNA' THEN alocacoes.horas ELSE 0 END) as horas_diurnas"),
                DB::raw("SUM(CASE WHEN UPPER(alocacoes.tipo_extra) = 'NOTURNA' THEN alocacoes.horas ELSE 0 END) as horas_noturnas"),
                DB::raw("SUM(alocacoes.horas) as total_horas"),
                DB::raw("COUNT(alocacoes.id) as dias_alocados")
            )
            ->join('servidores', 'alocacoes.servidor_id', '=', 'servidores.id')
            ->join('escalas', 'alocacoes.escala_id', '=', 'escalas.id')
            ->join('unidades', 'escalas.unidade_id', '=', 'unidades.id')
            ->where('escalas.ano', $ano)
            ->whereBetween('escalas.mes', [$mesInicio, $mesFim])
            ->whereIn('escalas.status', ['aprovada', 'executada']);
        
        if ($unidadeId) {
            $query->where('escalas.unidade_id', $unidadeId);
        }
        
        if ($servidorId) {
            $query->where('servidores.id', $servidorId);
        }
        
        $dados = $query->groupBy('servidores.id', 'servidores.matricula', 'servidores.nome', 'unidades.nome')
            ->orderBy('servidores.nome')
            ->get();
        
        if ($dados->isEmpty()) {
            return redirect('/superintendente/relatorio-horas?' . http_build_query($request->all()))
                ->with('error', 'Nenhum dado encontrado para exportar.');
        }
        
        $periodoStr = $meses[$mesInicio];
        if ($mesInicio != $mesFim) {
            $periodoStr .= "_a_{$meses[$mesFim]}";
        }
        $nomeArquivo = "relatorio_horas_{$periodoStr}_{$ano}.xls";
        
        $html = "\xEF\xBB\xBF";
        $html .= "<html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:x='urn:schemas-microsoft-com:office:excel'>";
        $html .= "<head><meta charset='UTF-8'>";
        $html .= "<style>";
        $html .= "table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }";
        $html .= "th, td { border: 1px solid #000; padding: 8px; text-align: left; }";
        $html .= "th { background-color: #0d6efd; color: white; font-weight: bold; }";
        $html .= ".header { text-align: center; font-size: 16pt; font-weight: bold; background-color: #0a58ca; color: white; }";
        $html .= ".subheader { text-align: center; font-size: 12pt; background-color: #6ea8fe; }";
        $html .= ".total-row { background-color: #D9E2F3; font-weight: bold; }";
        $html .= ".numero { text-align: center; }";
        $html .= ".horas { text-align: center; }";
        $html .= "</style>";
        $html .= "</head><body>";
        
        $titulo = "Relatório de Horas Trabalhadas";
        $subtitulo = "{$meses[$mesInicio]}" . ($mesInicio != $mesFim ? " a {$meses[$mesFim]}" : "") . " / {$ano}";
        if ($unidadeSelecionada) {
            $subtitulo .= " - " . $unidadeSelecionada->nome;
        }
        
        $html .= "<table>";
        $html .= "<tr><td class='header' colspan='8'>{$titulo}</td></tr>";
        $html .= "<tr><td class='subheader' colspan='8'>{$subtitulo}</td></tr>";
        $html .= "</table>";
        
        $html .= "<table>";
        $html .= "<thead>";
        $html .= "<tr>";
        $html .= "<th class='numero'>Núm.</th>";
        $html .= "<th>Matrícula</th>";
        $html .= "<th>Nome do Servidor</th>";
        $html .= "<th>Unidade</th>";
        $html .= "<th class='horas'>Horas Diurnas</th>";
        $html .= "<th class='horas'>Horas Noturnas</th>";
        $html .= "<th class='horas'>Total Horas</th>";
        $html .= "<th class='horas'>Dias Alocados</th>";
        $html .= "</tr>";
        $html .= "</thead>";
        $html .= "<tbody>";
        
        $num = 1;
        $totalDiurnas = 0;
        $totalNoturnas = 0;
        $totalGeral = 0;
        $totalDias = 0;
        
        foreach ($dados as $d) {
            $totalDiurnas += $d->horas_diurnas;
            $totalNoturnas += $d->horas_noturnas;
            $totalGeral += $d->total_horas;
            $totalDias += $d->dias_alocados;
            
            $html .= "<tr>";
            $html .= "<td class='numero'>" . str_pad($num, 3, '0', STR_PAD_LEFT) . "</td>";
            $html .= "<td>" . htmlspecialchars($d->matricula) . "</td>";
            $html .= "<td>" . htmlspecialchars($d->servidor_nome) . "</td>";
            $html .= "<td>" . htmlspecialchars($d->unidade_nome) . "</td>";
            $html .= "<td class='horas'>" . number_format($d->horas_diurnas, 0, ',', '.') . "</td>";
            $html .= "<td class='horas'>" . number_format($d->horas_noturnas, 0, ',', '.') . "</td>";
            $html .= "<td class='horas'>" . number_format($d->total_horas, 0, ',', '.') . "</td>";
            $html .= "<td class='horas'>" . $d->dias_alocados . "</td>";
            $html .= "</tr>";
            
            $num++;
        }
        
        $html .= "<tr class='total-row'>";
        $html .= "<td colspan='4' style='text-align: right;'>TOTAIS:</td>";
        $html .= "<td class='horas'>" . number_format($totalDiurnas, 0, ',', '.') . "</td>";
        $html .= "<td class='horas'>" . number_format($totalNoturnas, 0, ',', '.') . "</td>";
        $html .= "<td class='horas'>" . number_format($totalGeral, 0, ',', '.') . "</td>";
        $html .= "<td class='horas'>" . $totalDias . "</td>";
        $html .= "</tr>";
        $html .= "</tbody>";
        $html .= "</table>";
        
        $html .= "<br>";
        $html .= "<table>";
        $html .= "<tr><td style='background-color: #D9E2F3;'>Total de Servidores:</td><td style='text-align: center; font-weight: bold;'>" . $dados->count() . "</td></tr>";
        $html .= "<tr><td style='background-color: #D9E2F3;'>Total de Horas:</td><td style='text-align: center; font-weight: bold;'>" . number_format($totalGeral, 0, ',', '.') . "</td></tr>";
        $html .= "<tr><td style='background-color: #D9E2F3;'>Média por Servidor:</td><td style='text-align: center; font-weight: bold;'>" . ($dados->count() > 0 ? number_format($totalGeral / $dados->count(), 1, ',', '.') : 0) . "</td></tr>";
        $html .= "</table>";
        
        $html .= "</body></html>";
        
        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel; charset=utf-8')
            ->header('Content-Disposition', "attachment; filename=\"{$nomeArquivo}\"")
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function relatorioFinanceiro(Request $request)
    {
        $ano = $request->get('ano', date('Y'));
        $mesInicio = $request->get('mes_inicio', 1);
        $mesFim = $request->get('mes_fim', date('n'));
        $unidadeId = $request->get('unidade_id', '');
        
        $unidades = Unidade::where('ativo', true)->orderBy('nome')->get();
        $unidadeSelecionada = $unidadeId ? Unidade::find($unidadeId) : null;
        
        $query = Escala::select(
                'escalas.id',
                'escalas.ano',
                'escalas.mes',
                'escalas.status',
                'escalas.valor_executado',
                'unidades.nome as unidade_nome',
                DB::raw("COALESCE(distribuicao_orcamento.valor_distribuido / 12, 0) as orcamento_previsto"),
                DB::raw("(SELECT COUNT(*) FROM alocacoes WHERE alocacoes.escala_id = escalas.id) as total_alocacoes"),
                DB::raw("(SELECT COALESCE(SUM(alocacoes.horas), 0) FROM alocacoes WHERE alocacoes.escala_id = escalas.id) as total_horas"),
                DB::raw("(SELECT COUNT(DISTINCT alocacoes.servidor_id) FROM alocacoes WHERE alocacoes.escala_id = escalas.id) as total_servidores")
            )
            ->join('unidades', 'escalas.unidade_id', '=', 'unidades.id')
            ->leftJoin('distribuicao_orcamento', function($join) {
                $join->on('escalas.unidade_id', '=', 'distribuicao_orcamento.unidade_id')
                     ->on('escalas.ano', '=', 'distribuicao_orcamento.ano');
            })
            ->where('escalas.ano', $ano)
            ->whereBetween('escalas.mes', [$mesInicio, $mesFim])
            ->whereIn('escalas.status', ['aprovada', 'executada']);
        
        if ($unidadeId) {
            $query->where('escalas.unidade_id', $unidadeId);
        }
        
        $dados = $query->orderBy('unidades.nome')
            ->orderBy('escalas.mes')
            ->get();
        
        return view('superintendente.relatorio-financeiro', compact('ano', 'mesInicio', 'mesFim', 'unidadeId', 'unidades', 'unidadeSelecionada', 'dados'));
    }

    public function exportarRelatorioFinanceiroExcel(Request $request)
    {
        $meses = ['', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 
                  'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
        
        $ano = $request->get('ano', date('Y'));
        $mesInicio = $request->get('mes_inicio', 1);
        $mesFim = $request->get('mes_fim', date('n'));
        $unidadeId = $request->get('unidade_id', '');
        
        $unidadeSelecionada = $unidadeId ? Unidade::find($unidadeId) : null;
        
        $query = Escala::select(
                'escalas.id',
                'escalas.ano',
                'escalas.mes',
                'escalas.status',
                'escalas.valor_executado',
                'unidades.nome as unidade_nome',
                DB::raw("COALESCE(distribuicao_orcamento.valor_distribuido / 12, 0) as orcamento_previsto"),
                DB::raw("(SELECT COALESCE(SUM(alocacoes.horas), 0) FROM alocacoes WHERE alocacoes.escala_id = escalas.id) as total_horas"),
                DB::raw("(SELECT COUNT(DISTINCT alocacoes.servidor_id) FROM alocacoes WHERE alocacoes.escala_id = escalas.id) as total_servidores")
            )
            ->join('unidades', 'escalas.unidade_id', '=', 'unidades.id')
            ->leftJoin('distribuicao_orcamento', function($join) {
                $join->on('escalas.unidade_id', '=', 'distribuicao_orcamento.unidade_id')
                     ->on('escalas.ano', '=', 'distribuicao_orcamento.ano');
            })
            ->where('escalas.ano', $ano)
            ->whereBetween('escalas.mes', [$mesInicio, $mesFim])
            ->whereIn('escalas.status', ['aprovada', 'executada']);
        
        if ($unidadeId) {
            $query->where('escalas.unidade_id', $unidadeId);
        }
        
        $dados = $query->orderBy('unidades.nome')
            ->orderBy('escalas.mes')
            ->get();
        
        if ($dados->isEmpty()) {
            return redirect('/superintendente/relatorio-financeiro?' . http_build_query($request->all()))
                ->with('error', 'Nenhum dado encontrado para exportar.');
        }
        
        $periodoStr = $meses[$mesInicio];
        if ($mesInicio != $mesFim) {
            $periodoStr .= "_a_{$meses[$mesFim]}";
        }
        $nomeArquivo = "relatorio_financeiro_{$periodoStr}_{$ano}.xls";
        
        $html = "\xEF\xBB\xBF";
        $html .= "<html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:x='urn:schemas-microsoft-com:office:excel'>";
        $html .= "<head><meta charset='UTF-8'>";
        $html .= "<style>";
        $html .= "table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }";
        $html .= "th, td { border: 1px solid #000; padding: 8px; text-align: left; }";
        $html .= "th { background-color: #198754; color: white; font-weight: bold; }";
        $html .= ".header { text-align: center; font-size: 16pt; font-weight: bold; background-color: #146c43; color: white; }";
        $html .= ".subheader { text-align: center; font-size: 12pt; background-color: #75b798; }";
        $html .= ".total-row { background-color: #D1E7DD; font-weight: bold; }";
        $html .= ".numero { text-align: center; }";
        $html .= ".valor { text-align: right; }";
        $html .= ".positivo { color: #198754; }";
        $html .= ".negativo { color: #dc3545; }";
        $html .= "</style>";
        $html .= "</head><body>";
        
        $titulo = "Relatório Financeiro - Valores Executados";
        $subtitulo = "{$meses[$mesInicio]}" . ($mesInicio != $mesFim ? " a {$meses[$mesFim]}" : "") . " / {$ano}";
        if ($unidadeSelecionada) {
            $subtitulo .= " - " . $unidadeSelecionada->nome;
        }
        
        $html .= "<table>";
        $html .= "<tr><td class='header' colspan='9'>{$titulo}</td></tr>";
        $html .= "<tr><td class='subheader' colspan='9'>{$subtitulo}</td></tr>";
        $html .= "</table>";
        
        $html .= "<table>";
        $html .= "<thead>";
        $html .= "<tr>";
        $html .= "<th class='numero'>Núm.</th>";
        $html .= "<th>Unidade</th>";
        $html .= "<th>Mês/Ano</th>";
        $html .= "<th class='numero'>Status</th>";
        $html .= "<th class='valor'>Orçamento Previsto</th>";
        $html .= "<th class='valor'>Valor Executado</th>";
        $html .= "<th class='valor'>Diferença</th>";
        $html .= "<th class='numero'>Total Horas</th>";
        $html .= "<th class='numero'>Servidores</th>";
        $html .= "</tr>";
        $html .= "</thead>";
        $html .= "<tbody>";
        
        $num = 1;
        $totalPrevisto = 0;
        $totalExecutado = 0;
        $totalHoras = 0;
        
        foreach ($dados as $d) {
            $totalPrevisto += $d->orcamento_previsto;
            $totalExecutado += $d->valor_executado;
            $totalHoras += $d->total_horas;
            $diferenca = $d->orcamento_previsto - $d->valor_executado;
            
            $html .= "<tr>";
            $html .= "<td class='numero'>" . str_pad($num, 3, '0', STR_PAD_LEFT) . "</td>";
            $html .= "<td>" . htmlspecialchars($d->unidade_nome) . "</td>";
            $html .= "<td>" . $meses[$d->mes] . "/" . $d->ano . "</td>";
            $html .= "<td class='numero'>" . ucfirst($d->status) . "</td>";
            $html .= "<td class='valor'>R$ " . number_format($d->orcamento_previsto, 2, ',', '.') . "</td>";
            $html .= "<td class='valor'>R$ " . number_format($d->valor_executado, 2, ',', '.') . "</td>";
            $html .= "<td class='valor " . ($diferenca >= 0 ? 'positivo' : 'negativo') . "'>R$ " . number_format($diferenca, 2, ',', '.') . "</td>";
            $html .= "<td class='numero'>" . number_format($d->total_horas, 0, ',', '.') . "</td>";
            $html .= "<td class='numero'>" . $d->total_servidores . "</td>";
            $html .= "</tr>";
            
            $num++;
        }
        
        $diferencaTotal = $totalPrevisto - $totalExecutado;
        
        $html .= "<tr class='total-row'>";
        $html .= "<td colspan='4' style='text-align: right;'>TOTAIS:</td>";
        $html .= "<td class='valor'>R$ " . number_format($totalPrevisto, 2, ',', '.') . "</td>";
        $html .= "<td class='valor'>R$ " . number_format($totalExecutado, 2, ',', '.') . "</td>";
        $html .= "<td class='valor " . ($diferencaTotal >= 0 ? 'positivo' : 'negativo') . "'>R$ " . number_format($diferencaTotal, 2, ',', '.') . "</td>";
        $html .= "<td class='numero'>" . number_format($totalHoras, 0, ',', '.') . "</td>";
        $html .= "<td></td>";
        $html .= "</tr>";
        $html .= "</tbody>";
        $html .= "</table>";
        
        $html .= "<br>";
        $html .= "<table>";
        $html .= "<tr><td style='background-color: #D1E7DD;'>Total de Escalas:</td><td style='text-align: center; font-weight: bold;'>" . $dados->count() . "</td></tr>";
        $html .= "<tr><td style='background-color: #D1E7DD;'>Orçamento Previsto:</td><td style='text-align: center; font-weight: bold;'>R$ " . number_format($totalPrevisto, 2, ',', '.') . "</td></tr>";
        $html .= "<tr><td style='background-color: #D1E7DD;'>Valor Executado:</td><td style='text-align: center; font-weight: bold;'>R$ " . number_format($totalExecutado, 2, ',', '.') . "</td></tr>";
        $html .= "<tr><td style='background-color: #D1E7DD;'>Saldo:</td><td style='text-align: center; font-weight: bold; " . ($diferencaTotal >= 0 ? 'color: #198754;' : 'color: #dc3545;') . "'>R$ " . number_format($diferencaTotal, 2, ',', '.') . "</td></tr>";
        $html .= "</table>";
        
        $html .= "</body></html>";
        
        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel; charset=utf-8')
            ->header('Content-Disposition', "attachment; filename=\"{$nomeArquivo}\"")
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}
