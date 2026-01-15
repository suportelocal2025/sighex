<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Escala;
use App\Models\Servidor;
use App\Models\Equipe;
use App\Models\Modulo;
use App\Models\Alocacao;
use App\Models\EscalaEquipeServidor;
use App\Models\DistribuicaoOrcamento;
use App\Models\SolicitacaoServidor;
use App\Models\AlertaDiretor;
use App\Models\ModuloEquipeServidor;
use Illuminate\Support\Facades\Auth;

class DiretorController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        $unidadeId = $user->unidade_id;
        $ano = date('Y');
        $mes = date('n');

        $distribuicao = DistribuicaoOrcamento::where('unidade_id', $unidadeId)
            ->where('ano', $ano)
            ->first();

        $orcamento = $distribuicao?->valor_distribuido ?? 0;
        $gasto = $distribuicao?->valor_gasto ?? 0;
        $disponivel = $orcamento - $gasto;
        $marginPercentual = $distribuicao?->margin_percentual ?? 10;

        $horasExecutadas = Escala::where('unidade_id', $unidadeId)
            ->where('status', 'executada')
            ->where('ano', $ano)
            ->join('alocacoes', 'escalas.id', '=', 'alocacoes.escala_id')
            ->sum('alocacoes.horas');

        $escalas = Escala::where('unidade_id', $unidadeId)
            ->where('ano', $ano)
            ->orderBy('mes', 'desc')
            ->get();

        $escalasRejeitadas = $escalas->where('status', 'rejeitada')->count();
        $escalasAprovadas = $escalas->where('status', 'aprovada')->count();
        $escalasPendentes = $escalas->where('status', 'pendente')->count();
        
        $alertasMargemAmarelo = $escalas->filter(function($e) {
            return $e->status === 'executada' && $e->usa_margem && !$e->excede_margem;
        });
        
        $alertasMargemVermelho = $escalas->filter(function($e) {
            return $e->status === 'executada' && $e->excede_margem;
        });

        $alertasPrazo = AlertaDiretor::where('unidade_id', $unidadeId)
            ->where('lido', false)
            ->orderBy('created_at', 'desc')
            ->get();

        $orcamentoMensalBase = $orcamento / 12;
        
        $mesesInfo = [];
        $nomesMeses = ['', 'Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
        
        $gastosPorMes = [];
        for ($m = 1; $m <= 12; $m++) {
            $gastosPorMes[$m] = Escala::where('unidade_id', $unidadeId)
                ->where('ano', $ano)
                ->where('mes', $m)
                ->where('status', 'executada')
                ->sum('valor_executado') ?? 0;
        }
        
        $orcamentoRestante = $orcamento;
        $maxOrcamentoMes = $orcamentoMensalBase;
        
        for ($m = 1; $m <= 12; $m++) {
            $mesesRestantes = 12 - $m + 1;
            $orcamentoMes = $orcamentoRestante / $mesesRestantes;
            $gastoMes = $gastosPorMes[$m];
            
            if ($gastoMes > 0) {
                $orcamentoRestante -= $gastoMes;
            } else {
                $orcamentoRestante -= $orcamentoMes;
            }
            
            $limiteComMargem = $orcamentoMes * (1 + $marginPercentual / 100);
            $ultrapassouMargem = $gastoMes > $limiteComMargem;
            
            if ($orcamentoMes > $maxOrcamentoMes) {
                $maxOrcamentoMes = $orcamentoMes;
            }
            
            $mesesInfo[$m] = [
                'nome' => $nomesMeses[$m],
                'orcamento' => $orcamentoMes,
                'gasto' => $gastoMes,
                'saldo' => $orcamentoMes - $gastoMes,
                'limite' => $limiteComMargem,
                'ultrapassouMargem' => $ultrapassouMargem,
                'mesAtual' => ($m == $mes),
            ];
        }

        return view('diretor.dashboard', compact(
            'orcamento',
            'gasto',
            'disponivel',
            'horasExecutadas',
            'escalas',
            'escalasRejeitadas',
            'escalasAprovadas',
            'escalasPendentes',
            'alertasMargemAmarelo',
            'alertasMargemVermelho',
            'alertasPrazo',
            'ano',
            'mes',
            'mesesInfo',
            'marginPercentual',
            'maxOrcamentoMes',
            'orcamentoMensalBase'
        ));
    }

    public function escalaMensal(Request $request)
    {
        $user = Auth::user();
        $unidadeId = $user->unidade_id;
        $unidade = \App\Models\Unidade::find($unidadeId);
        $mes = (int)$request->get('mes', date('n'));
        $ano = (int)$request->get('ano', date('Y'));

        $escala = Escala::where('unidade_id', $unidadeId)
            ->where('mes', $mes)
            ->where('ano', $ano)
            ->first();

        if (!$escala) {
            $escala = Escala::create([
                'unidade_id' => $unidadeId,
                'mes' => $mes,
                'ano' => $ano,
                'status' => 'rascunho',
                'criado_por' => Auth::id(),
            ]);
        }

        $equipes = Equipe::where('unidade_id', $unidadeId)->get();
        $modulos = Modulo::where('unidade_id', $unidadeId)->where('ativo', true)->get();
        $servidores = Servidor::where('unidade_id', $unidadeId)
            ->where('ativo', true)
            ->where('apto_escala_extra', true)
            ->get();

        $escalaServidores = EscalaEquipeServidor::with(['servidor', 'equipe', 'modulo'])
            ->where('escala_id', $escala->id)
            ->get();

        $alocacoes = Alocacao::where('escala_id', $escala->id)->get();
        
        $diasNoMes = cal_days_in_month(CAL_GREGORIAN, $mes, $ano);
        $feriados = $this->getFeriados($ano);
        $nomeDias = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
        
        $diasInfo = [];
        for ($d = 1; $d <= $diasNoMes; $d++) {
            $dataStr = sprintf('%04d-%02d-%02d', $ano, $mes, $d);
            $timestamp = strtotime($dataStr);
            $diaSemana = (int)date('w', $timestamp);
            $diasInfo[$d] = [
                'diaSemana' => $diaSemana,
                'nomeDia' => $nomeDias[$diaSemana],
                'isFeriado' => isset($feriados[$dataStr]),
                'nomeFeriado' => $feriados[$dataStr] ?? null,
            ];
        }
        
        $horasPorServidor = [];
        foreach ($alocacoes as $a) {
            if (!isset($horasPorServidor[$a->servidor_id])) {
                $horasPorServidor[$a->servidor_id] = 0;
            }
            $horasPorServidor[$a->servidor_id] += $a->horas + ($a->horas_abono ?? 0);
        }

        $podeEditar = in_array($escala->status, ['rascunho', 'rejeitada']);
        $limiteHoras = 60;

        return view('diretor.escala-mensal', compact(
            'escala',
            'equipes',
            'modulos',
            'servidores',
            'escalaServidores',
            'alocacoes',
            'mes',
            'ano',
            'unidade',
            'diasNoMes',
            'diasInfo',
            'feriados',
            'horasPorServidor',
            'podeEditar',
            'limiteHoras'
        ));
    }

    private function getFeriados(int $ano): array
    {
        $feriados = [
            "$ano-01-01" => "Confraternização Universal",
            "$ano-04-21" => "Tiradentes",
            "$ano-05-01" => "Dia do Trabalho",
            "$ano-09-07" => "Independência do Brasil",
            "$ano-10-12" => "Nossa Senhora Aparecida",
            "$ano-11-02" => "Finados",
            "$ano-11-15" => "Proclamação da República",
            "$ano-12-25" => "Natal",
        ];
        
        $pascoa = easter_date($ano);
        $carnaval = date('Y-m-d', strtotime('-47 days', $pascoa));
        $carnaval2 = date('Y-m-d', strtotime('-46 days', $pascoa));
        $sextaSanta = date('Y-m-d', strtotime('-2 days', $pascoa));
        $corpusChristi = date('Y-m-d', strtotime('+60 days', $pascoa));
        
        $feriados[$carnaval] = "Carnaval";
        $feriados[$carnaval2] = "Carnaval";
        $feriados[$sextaSanta] = "Sexta-Feira Santa";
        $feriados[$corpusChristi] = "Corpus Christi";
        
        return $feriados;
    }

    public function adicionarServidor(Request $request)
    {
        $request->validate([
            'escala_id' => 'required|exists:escalas,id',
            'equipe_id' => 'required|exists:equipes,id',
            'servidor_id' => 'required|exists:servidores,id',
            'modulo_id' => 'nullable|exists:modulos,id',
        ]);
        
        $servidor = Servidor::find($request->servidor_id);
        
        if (!$servidor) {
            return back()->withErrors(['servidor' => 'Servidor não encontrado na base de dados.']);
        }
        
        if (!$servidor->ativo) {
            return back()->withErrors(['servidor' => 'Servidor está INATIVO e não pode ser alocado.']);
        }
        
        if (!$servidor->apto_escala_extra) {
            return back()->withErrors(['servidor' => 'Servidor não está APTO para escala extra. Aguarde aprovação do RH.']);
        }
        
        if (!$servidor->isDisponivelParaEscala()) {
            $motivo = $servidor->motivo_inativo ?? 'período de inatividade';
            return back()->withErrors(['servidor' => "Servidor indisponível: {$motivo}"]);
        }

        $existe = EscalaEquipeServidor::where('escala_id', $request->escala_id)
            ->where('servidor_id', $request->servidor_id)
            ->exists();

        if ($existe) {
            return back()->withErrors(['servidor' => 'Servidor já está na escala']);
        }

        EscalaEquipeServidor::create([
            'escala_id' => $request->escala_id,
            'equipe_id' => $request->equipe_id,
            'servidor_id' => $request->servidor_id,
            'modulo_id' => $request->modulo_id,
            'lider' => $request->has('lider'),
        ]);

        return back()->with('success', 'Servidor adicionado!');
    }

    public function removerServidor(Request $request)
    {
        $request->validate([
            'escala_id' => 'required|exists:escalas,id',
            'servidor_id' => 'required|exists:servidores,id',
        ]);

        EscalaEquipeServidor::where('escala_id', $request->escala_id)
            ->where('servidor_id', $request->servidor_id)
            ->delete();

        Alocacao::where('escala_id', $request->escala_id)
            ->where('servidor_id', $request->servidor_id)
            ->delete();

        return back()->with('success', 'Servidor removido!');
    }

    public function alocarDia(Request $request)
    {
        $request->validate([
            'escala_id' => 'required|exists:escalas,id',
            'servidor_id' => 'required|exists:servidores,id',
        ]);

        if ($request->has('data')) {
            $alocacao = Alocacao::where('escala_id', $request->escala_id)
                ->where('servidor_id', $request->servidor_id)
                ->where('data', $request->data)
                ->first();

            if ($alocacao) {
                $alocacao->delete();
                return response()->json(['removed' => true]);
            }
        }

        if ($request->has('dia')) {
            $escala = Escala::find($request->escala_id);
            $data = sprintf('%04d-%02d-%02d', $escala->ano, $escala->mes, $request->dia);
            
            $alocacao = Alocacao::where('escala_id', $request->escala_id)
                ->where('servidor_id', $request->servidor_id)
                ->where('dia', $request->dia)
                ->first();

            if ($alocacao) {
                $alocacao->delete();
                return response()->json(['removed' => true]);
            }

            $servidor = Servidor::find($request->servidor_id);
            if ($servidor && $servidor->isInativoNaData($data)) {
                $motivo = $servidor->getMotivoInativoNaData($data);
                return response()->json([
                    'error' => true,
                    'message' => "Servidor inativo nesta data: {$motivo}"
                ], 422);
            }

            $tipoExtra = $request->tipo_extra ?? 'diurna';
            $horas = $request->horas ?? 10;
            
            $horasDiurnas = Alocacao::where('escala_id', $request->escala_id)
                ->where('servidor_id', $request->servidor_id)
                ->where('tipo_extra', 'diurna')
                ->sum('horas');
            
            $horasNoturnas = Alocacao::where('escala_id', $request->escala_id)
                ->where('servidor_id', $request->servidor_id)
                ->where('tipo_extra', 'noturna')
                ->sum('horas');
            
            $horasTotal = $horasDiurnas + $horasNoturnas;
            
            if ($tipoExtra === 'diurna') {
                if ($horasDiurnas + $horas > 60) {
                    return response()->json([
                        'error' => true,
                        'message' => 'Limite de 60h diurnas atingido para este servidor.'
                    ], 422);
                }
            } else {
                if ($horasNoturnas + $horas > 20) {
                    return response()->json([
                        'error' => true,
                        'message' => 'Limite de 20h noturnas atingido para este servidor.'
                    ], 422);
                }
            }
            
            if ($horasTotal + $horas > 60) {
                return response()->json([
                    'error' => true,
                    'message' => 'Limite total de 60h atingido para este servidor.'
                ], 422);
            }

            Alocacao::create([
                'escala_id' => $request->escala_id,
                'servidor_id' => $request->servidor_id,
                'equipe_id' => $request->equipe_id,
                'modulo_id' => $request->modulo_id,
                'dia' => $request->dia,
                'data' => $data,
                'horas' => $horas,
                'horas_abono' => $request->horas_abono ?? 0,
                'tipo_extra' => $tipoExtra,
            ]);

            return response()->json(['added' => true]);
        }

        return response()->json(['error' => 'Parâmetros inválidos'], 400);
    }

    public function enviarAprovacao(Request $request)
    {
        $request->validate([
            'escala_id' => 'required|exists:escalas,id',
        ]);

        $escala = Escala::findOrFail($request->escala_id);
        
        $totalHoras = Alocacao::where('escala_id', $escala->id)
            ->sum(\DB::raw('COALESCE(horas, 0) + COALESCE(horas_abono, 0)'));
        
        $valorHora = 50;
        $valorPrevisto = $totalHoras * $valorHora;
        
        $escala->update([
            'status' => 'pendente',
            'data_envio' => now(),
            'valor_previsto' => $valorPrevisto,
        ]);

        return redirect('/diretor')->with('success', 'Escala enviada para aprovação!');
    }

    public function servidores()
    {
        $user = Auth::user();
        $servidores = Servidor::where('unidade_id', $user->unidade_id)->get();
        return view('diretor.servidores', compact('servidores'));
    }

    public function imprimirMural(Request $request)
    {
        $user = Auth::user();
        $unidadeId = $user->unidade_id;
        $mes = (int)$request->get('mes', date('n'));
        $ano = (int)$request->get('ano', date('Y'));

        $unidade = \App\Models\Unidade::find($unidadeId);

        $escala = Escala::where('unidade_id', $unidadeId)
            ->where('mes', $mes)
            ->where('ano', $ano)
            ->first();

        if (!$escala) {
            return response('Escala não encontrada.', 404);
        }

        $alocacoes = Alocacao::select('alocacoes.*', 'servidores.nome as servidor_nome', 'servidores.matricula', 'equipes.nome as equipe_nome', 'modulos.nome as modulo_nome')
            ->join('servidores', 'alocacoes.servidor_id', '=', 'servidores.id')
            ->join('equipes', 'alocacoes.equipe_id', '=', 'equipes.id')
            ->join('modulos', 'alocacoes.modulo_id', '=', 'modulos.id')
            ->where('alocacoes.escala_id', $escala->id)
            ->orderBy('modulos.nome')
            ->orderBy('equipes.nome')
            ->orderBy('servidores.nome')
            ->orderBy('alocacoes.dia')
            ->get();

        $agrupado = [];
        foreach ($alocacoes as $a) {
            $key = $a->modulo_nome . '|' . $a->equipe_nome;
            if (!isset($agrupado[$key])) {
                $agrupado[$key] = [
                    'modulo' => $a->modulo_nome,
                    'equipe' => $a->equipe_nome,
                    'servidores' => []
                ];
            }
            $sKey = $a->servidor_id;
            if (!isset($agrupado[$key]['servidores'][$sKey])) {
                $agrupado[$key]['servidores'][$sKey] = [
                    'nome' => $a->servidor_nome,
                    'matricula' => $a->matricula,
                    'is_lider' => $a->is_lider,
                    'dias' => [],
                    'horas' => 0
                ];
            }
            $agrupado[$key]['servidores'][$sKey]['dias'][] = str_pad($a->dia, 2, '0', STR_PAD_LEFT);
            $agrupado[$key]['servidores'][$sKey]['horas'] += $a->horas + ($a->horas_abono ?? 0);
        }

        foreach ($agrupado as &$grupo) {
            foreach ($grupo['servidores'] as &$srv) {
                sort($srv['dias']);
            }
        }
        unset($grupo, $srv);

        $meses = ['', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 
                  'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];

        return view('diretor.imprimir-mural', compact('unidade', 'escala', 'agrupado', 'meses', 'mes', 'ano'));
    }

    public function alertas(Request $request)
    {
        $user = Auth::user();
        $unidadeId = $user->unidade_id;
        $ano = (int)$request->get('ano', date('Y'));
        $mes = $request->get('mes');
        $tipo = $request->get('tipo');

        $escalas = Escala::where('unidade_id', $unidadeId)
            ->where('ano', $ano)
            ->where('status', 'executada')
            ->get();
        
        if ($mes) {
            $escalas = $escalas->filter(fn($e) => $e->mes == $mes);
        }
        
        $alertasMargemAmarelo = $escalas->filter(function($e) {
            return $e->usa_margem && !$e->excede_margem;
        });
        
        $alertasMargemVermelho = $escalas->filter(function($e) {
            return $e->excede_margem;
        });
        
        if ($tipo === 'amarelo') {
            $alertasMargemVermelho = collect();
        } elseif ($tipo === 'vermelho') {
            $alertasMargemAmarelo = collect();
        }

        $escalasRejeitadas = Escala::where('unidade_id', $unidadeId)
            ->where('ano', $ano)
            ->where('status', 'rejeitada')
            ->count();

        $alertasPrazo = AlertaDiretor::where('unidade_id', $unidadeId)
            ->where('lido', false)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('diretor.alertas', [
            'ano' => $ano,
            'mes' => $mes,
            'tipo' => $tipo,
            'alertasAmarelo' => $alertasMargemAmarelo,
            'alertasVermelho' => $alertasMargemVermelho,
            'escalasRejeitadas' => $escalasRejeitadas,
            'alertasPrazo' => $alertasPrazo,
        ]);
    }

    public function solicitarInclusaoServidor(Request $request)
    {
        $request->validate([
            'matricula' => 'required|string|max:50',
            'nome' => 'required|string|max:255',
            'cargo' => 'nullable|string|max:100',
        ]);

        $user = Auth::user();
        
        if (Servidor::where('matricula', $request->matricula)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Já existe um servidor cadastrado com esta matrícula.'
            ], 400);
        }
        
        if (SolicitacaoServidor::where('matricula', $request->matricula)
            ->where('status', 'pendente')
            ->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Já existe uma solicitação pendente para esta matrícula.'
            ], 400);
        }

        SolicitacaoServidor::create([
            'matricula' => $request->matricula,
            'nome' => $request->nome,
            'cargo' => $request->cargo,
            'unidade_id' => $user->unidade_id,
            'solicitante_id' => $user->id,
            'status' => 'pendente',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Solicitação enviada com sucesso! Aguarde a aprovação do RH.'
        ]);
    }

    public function servidoresModuloEquipe(Request $request)
    {
        $moduloId = $request->get('modulo_id');
        $equipeId = $request->get('equipe_id');
        
        if (!$moduloId || !$equipeId) {
            return response()->json(['servidores' => []]);
        }
        
        $user = Auth::user();
        $unidadeId = $user->unidade_id;
        
        $servidoresIds = ModuloEquipeServidor::where('modulo_id', $moduloId)
            ->where('equipe_id', $equipeId)
            ->pluck('servidor_id');
        
        $modulo = Modulo::where('id', $moduloId)->where('unidade_id', $unidadeId)->first();
        $equipe = Equipe::where('id', $equipeId)->where('unidade_id', $unidadeId)->first();
        
        if (!$modulo || !$equipe) {
            return response()->json(['servidores' => []]);
        }
        
        $servidores = Servidor::whereIn('id', $servidoresIds)
            ->where('unidade_id', $unidadeId)
            ->where('ativo', true)
            ->where('apto_escala_extra', true)
            ->select('id', 'nome', 'matricula')
            ->get();
        
        return response()->json(['servidores' => $servidores]);
    }
}
