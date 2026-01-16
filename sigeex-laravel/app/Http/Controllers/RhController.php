<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Escala;
use App\Models\Alocacao;
use App\Models\EscalaEquipeServidor;
use App\Models\DistribuicaoOrcamento;
use App\Models\Servidor;
use App\Models\SolicitacaoServidor;
use App\Models\Unidade;
use App\Models\AlertaDiretor;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class RhController extends Controller
{
    public function dashboard()
    {
        $ano = date('Y');
        
        // Calcular estatísticas separadamente para performance
        $estatisticas = [
            'pendentes' => Escala::where('ano', $ano)->where('status', 'pendente')->count(),
            'aprovadas' => Escala::where('ano', $ano)->where('status', 'aprovada')->count(),
            'executadas' => Escala::where('ano', $ano)->where('status', 'executada')->count(),
            'rejeitadas' => Escala::where('ano', $ano)->where('status', 'rejeitada')->count(),
        ];

        // Buscar apenas as 10 escalas mais recentes
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
            ->limit(10)
            ->get();

        // Calcular horas para cada escala (horas + horas_abono) com COALESCE por campo
        foreach ($escalas as $escala) {
            $escala->total_horas = Alocacao::where('escala_id', $escala->id)
                ->selectRaw('SUM(COALESCE(horas, 0) + COALESCE(horas_abono, 0)) as total')
                ->value('total') ?? 0;
        }

        return view('rh.dashboard', compact('escalas', 'estatisticas', 'ano'));
    }

    public function escalas(Request $request)
    {
        $status = $request->get('status', 'pendente');
        $ano = $request->get('ano', date('Y'));
        $mes = $request->get('mes', '');
        $unidadeId = $request->get('unidade_id', '');

        $query = Escala::with('unidade')->where('ano', $ano);
        
        if ($status !== 'todos') {
            $query->where('status', $status);
        }
        
        if ($mes !== '' && $mes !== null) {
            $query->where('mes', (int)$mes);
        }
        
        if ($unidadeId !== '' && $unidadeId !== null) {
            $query->where('unidade_id', $unidadeId);
        }

        $escalas = $query->orderBy('mes', 'desc')->get();
        
        $unidades = \App\Models\Unidade::orderBy('nome')->get();
        $anos = Escala::select('ano')->distinct()->orderBy('ano', 'desc')->pluck('ano');
        if ($anos->isEmpty()) {
            $anos = collect([date('Y')]);
        }

        return view('rh.escalas', compact('escalas', 'status', 'ano', 'mes', 'unidadeId', 'unidades', 'anos'));
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
            'data_rejeicao' => now(),
            'aprovado_por' => Auth::id(),
            'data_aprovacao' => now(),
        ]);

        $this->criarAlertaCorrecaoImediata($escala);

        return redirect('/rh/escalas')->with('success', 'Escala rejeitada!');
    }
    
    private function criarAlertaCorrecaoImediata(Escala $escala): void
    {
        $meses = ['', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 
                  'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
        $prazoLimite = now()->addHours(24);
        
        AlertaDiretor::create([
            'unidade_id' => $escala->unidade_id,
            'escala_id' => $escala->id,
            'tipo' => 'correcao_imediata',
            'titulo' => 'Escala Rejeitada - Correção Necessária',
            'mensagem' => "A escala de {$meses[$escala->mes]}/{$escala->ano} foi rejeitada pelo RH. Motivo: {$escala->motivo_rejeicao}. Você tem 24 horas para fazer as correções.",
            'mes' => $escala->mes,
            'ano' => $escala->ano,
            'prazo_limite' => $prazoLimite,
        ]);
        
        $this->enviarEmailAlertaCorrecao($escala, 'correcao_imediata', 24);
    }
    
    private function enviarEmailAlertaCorrecao(Escala $escala, string $tipo, int $horas): void
    {
        $diretores = User::where('unidade_id', $escala->unidade_id)
            ->where('perfil', 'diretor')
            ->where('ativo', true)
            ->whereNotNull('email')
            ->get();
        
        foreach ($diretores as $diretor) {
            \Log::info("Alerta de correção enviado para {$diretor->email} - Tipo: {$tipo}");
        }
    }

    public function executarEscala(Request $request)
    {
        $request->validate([
            'escala_id' => 'required|exists:escalas,id',
            'valor_executado' => 'required|numeric|min:0',
        ]);

        $escala = Escala::findOrFail($request->escala_id);
        $valorExecutado = $request->valor_executado;
        
        $budgetInfo = $this->calcularBudgetMes($escala);
        
        $orcamentoMes = $budgetInfo['orcamento_mes'];
        $limiteComMargem = $budgetInfo['limite_margem'];
        
        $passouPrevisto = $valorExecutado > $orcamentoMes;
        $usouMargem = $valorExecutado > $orcamentoMes && $valorExecutado <= $limiteComMargem;
        $excedeuMargem = $valorExecutado > $limiteComMargem;
        
        $escala->update([
            'status' => 'executada',
            'valor_executado' => $valorExecutado,
            'orcamento_mes' => $orcamentoMes,
            'limite_margem' => $limiteComMargem,
            'usa_margem' => $usouMargem,
            'excede_margem' => $excedeuMargem,
        ]);

        $distribuicao = DistribuicaoOrcamento::firstOrCreate(
            ['unidade_id' => $escala->unidade_id, 'ano' => $escala->ano],
            ['valor_distribuido' => 0, 'valor_gasto' => 0]
        );

        $distribuicao->increment('valor_gasto', $valorExecutado);

        if ($passouPrevisto) {
            $this->enviarAlertasMargemExecutada($escala, $orcamentoMes, $limiteComMargem, $valorExecutado, $usouMargem, $excedeuMargem);
        }

        $mensagem = 'Escala marcada como executada!';
        if ($excedeuMargem) {
            $mensagem = 'Escala executada! ALERTA: Valor EXCEDEU a margem orçamentária. Alertas enviados.';
        } elseif ($usouMargem) {
            $mensagem = 'Escala executada! ALERTA: Valor ultrapassou previsão mas está dentro da margem. Alertas enviados.';
        }

        return redirect('/rh/escalas')->with($passouPrevisto ? 'warning' : 'success', $mensagem);
    }
    
    private function calcularBudgetMes(Escala $escala): array
    {
        $unidadeId = $escala->unidade_id;
        $ano = $escala->ano;
        $mes = $escala->mes;
        
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
        $orcamentoMes = $mesesRestantes > 0 ? $orcamentoRestante / $mesesRestantes : 0;
        $limiteComMargem = $orcamentoMes * (1 + $marginPercentual / 100);
        
        return [
            'orcamento_mes' => $orcamentoMes,
            'limite_margem' => $limiteComMargem,
            'margin_percentual' => $marginPercentual,
        ];
    }
    
    private function enviarAlertasMargemExecutada(Escala $escala, float $orcamentoMes, float $limiteComMargem, float $valorExecutado, bool $usouMargem, bool $excedeuMargem)
    {
        $meses = ['', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 
                  'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
        $unidade = $escala->unidade;
        
        $tipoAlerta = $excedeuMargem ? 'EXCEDEU MARGEM' : 'USOU MARGEM';
        $corAlerta = $excedeuMargem ? 'VERMELHO' : 'AMARELO';
        
        $mensagem = "ALERTA DE ORÇAMENTO - SIGEEX\n\n" .
            "Tipo: {$tipoAlerta}\n" .
            "Unidade: {$unidade->nome}\n" .
            "Período: {$meses[$escala->mes]}/{$escala->ano}\n" .
            "Orçamento previsto do mês: R$ " . number_format($orcamentoMes, 2, ',', '.') . "\n" .
            "Limite com margem: R$ " . number_format($limiteComMargem, 2, ',', '.') . "\n" .
            "Valor executado: R$ " . number_format($valorExecutado, 2, ',', '.') . "\n" .
            "Excedente: R$ " . number_format($valorExecutado - $orcamentoMes, 2, ',', '.') . "\n\n" .
            ($excedeuMargem 
                ? "O valor executado EXCEDEU a margem orçamentária permitida." 
                : "O valor executado está DENTRO da margem de tolerância.");
        
        $superintendentes = \App\Models\Usuario::where('papel', 'superintendente')
            ->where('ativo', true)
            ->get();
        
        foreach ($superintendentes as $super) {
            if (!empty($super->email)) {
                try {
                    \Illuminate\Support\Facades\Mail::raw($mensagem, function ($message) use ($super, $tipoAlerta, $unidade) {
                        $message->to($super->email)
                                ->subject("[SIGEEX] {$tipoAlerta} - {$unidade->nome}");
                    });
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Erro ao enviar email para superintendente: ' . $e->getMessage());
                }
            }
        }
        
        $diretores = \App\Models\Usuario::where('papel', 'diretor')
            ->where('unidade_id', $escala->unidade_id)
            ->where('ativo', true)
            ->get();
        
        foreach ($diretores as $diretor) {
            if (!empty($diretor->email)) {
                try {
                    \Illuminate\Support\Facades\Mail::raw($mensagem, function ($message) use ($diretor, $tipoAlerta, $unidade) {
                        $message->to($diretor->email)
                                ->subject("[SIGEEX] {$tipoAlerta} - {$unidade->nome}");
                    });
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Erro ao enviar email para diretor: ' . $e->getMessage());
                }
            }
        }
    }

    public function relatorios()
    {
        return view('rh.relatorios');
    }

    public function servidores()
    {
        $unidades = Unidade::where('ativo', true)->orderBy('nome')->get();
        $solicitacoesPendentes = SolicitacaoServidor::where('status', 'pendente')->count();
        return view('rh.servidores', compact('unidades', 'solicitacoesPendentes'));
    }

    public function buscarServidores(Request $request)
    {
        $termo = $request->get('termo', '');
        
        if (strlen($termo) < 3) {
            return response()->json([]);
        }
        
        $servidores = Servidor::with('unidade')
            ->where(function ($q) use ($termo) {
                $q->where(\DB::raw('LOWER(nome)'), 'like', '%' . strtolower($termo) . '%')
                  ->orWhere(\DB::raw('LOWER(matricula)'), 'like', '%' . strtolower($termo) . '%');
            })
            ->limit(50)
            ->get();
        
        return response()->json($servidores);
    }

    public function alterarStatusServidor(Request $request)
    {
        $request->validate([
            'servidor_id' => 'required|exists:servidores,id',
            'ativo' => 'required|boolean',
        ]);

        $servidor = Servidor::findOrFail($request->servidor_id);
        
        $dados = [
            'ativo' => $request->ativo,
            'apto_escala_extra' => $request->has('apto_escala_extra') ? $request->apto_escala_extra : $servidor->apto_escala_extra,
        ];
        
        if (!$request->ativo) {
            $dados['motivo_inativo'] = $request->motivo_inativo;
            $dados['inativo_indefinido'] = $request->has('inativo_indefinido') && $request->inativo_indefinido;
            
            if (!$dados['inativo_indefinido']) {
                $dados['inativo_inicio'] = $request->inativo_inicio;
                $dados['inativo_fim'] = $request->inativo_fim;
            } else {
                $dados['inativo_inicio'] = null;
                $dados['inativo_fim'] = null;
            }
        } else {
            $dados['motivo_inativo'] = null;
            $dados['inativo_indefinido'] = false;
            $dados['inativo_inicio'] = null;
            $dados['inativo_fim'] = null;
        }
        
        $servidor->update($dados);
        
        return redirect('/rh/servidores')->with('success', 'Status do servidor atualizado!');
    }

    public function solicitacoesServidores()
    {
        $solicitacoes = SolicitacaoServidor::with(['unidade', 'solicitante'])
            ->orderByRaw("CASE status WHEN 'pendente' THEN 1 WHEN 'aprovada' THEN 2 ELSE 3 END")
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('rh.solicitacoes-servidores', compact('solicitacoes'));
    }

    public function aprovarSolicitacaoServidor(Request $request)
    {
        $request->validate([
            'solicitacao_id' => 'required|exists:solicitacao_servidores,id',
        ]);

        $solicitacao = SolicitacaoServidor::findOrFail($request->solicitacao_id);
        
        if (Servidor::where('matricula', $solicitacao->matricula)->exists()) {
            return redirect('/rh/solicitacoes-servidores')
                ->with('error', 'Já existe um servidor com esta matrícula.');
        }
        
        Servidor::create([
            'matricula' => $solicitacao->matricula,
            'nome' => $solicitacao->nome,
            'unidade_id' => $solicitacao->unidade_id,
            'cargo' => $solicitacao->cargo,
            'ativo' => true,
            'apto_escala_extra' => true,
        ]);
        
        $solicitacao->update([
            'status' => 'aprovada',
            'aprovador_id' => Auth::id(),
            'data_aprovacao' => now(),
        ]);
        
        return redirect('/rh/solicitacoes-servidores')
            ->with('success', 'Servidor aprovado e cadastrado com sucesso!');
    }

    public function rejeitarSolicitacaoServidor(Request $request)
    {
        $request->validate([
            'solicitacao_id' => 'required|exists:solicitacao_servidores,id',
            'motivo_rejeicao' => 'required|string|min:5',
        ]);

        $solicitacao = SolicitacaoServidor::findOrFail($request->solicitacao_id);
        
        $solicitacao->update([
            'status' => 'rejeitada',
            'aprovador_id' => Auth::id(),
            'data_aprovacao' => now(),
            'motivo_rejeicao' => $request->motivo_rejeicao,
        ]);
        
        return redirect('/rh/solicitacoes-servidores')
            ->with('success', 'Solicitação rejeitada.');
    }

    public function exportarExcel($id)
    {
        $meses = ['', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 
                  'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
        
        $escala = Escala::with('unidade')->findOrFail($id);
        
        $alocacoes = Alocacao::select('alocacoes.*', 'servidores.nome as servidor_nome', 
                'servidores.matricula', 'equipes.nome as equipe_nome', 'modulos.nome as modulo_nome')
            ->join('servidores', 'alocacoes.servidor_id', '=', 'servidores.id')
            ->leftJoin('equipes', 'alocacoes.equipe_id', '=', 'equipes.id')
            ->leftJoin('modulos', 'alocacoes.modulo_id', '=', 'modulos.id')
            ->where('alocacoes.escala_id', $id)
            ->orderBy('servidores.nome')
            ->orderBy('alocacoes.dia')
            ->get();
        
        $alocacoesAgrupadas = [];
        foreach ($alocacoes as $a) {
            $key = $a->servidor_id . '_' . ($a->modulo_id ?? 0);
            if (!isset($alocacoesAgrupadas[$key])) {
                $alocacoesAgrupadas[$key] = [
                    'servidor_nome' => $a->servidor_nome,
                    'matricula' => $a->matricula,
                    'modulo_nome' => $a->modulo_nome ?? '-',
                    'dias' => [],
                    'horas' => 0
                ];
            }
            $alocacoesAgrupadas[$key]['dias'][] = str_pad($a->dia, 2, '0', STR_PAD_LEFT);
            $alocacoesAgrupadas[$key]['horas'] += ($a->horas ?? 0) + ($a->horas_abono ?? 0);
        }
        
        foreach ($alocacoesAgrupadas as &$ag) {
            sort($ag['dias']);
        }
        unset($ag);
        
        $unidadeNome = $escala->unidade->nome ?? 'Unidade';
        $mesNome = $meses[$escala->mes];
        $ano = $escala->ano;
        $nomeArquivo = "escala_{$escala->id}_{$mesNome}_{$ano}.xls";
        
        $html = "\xEF\xBB\xBF";
        $html .= "<html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:x='urn:schemas-microsoft-com:office:excel'>";
        $html .= "<head><meta charset='UTF-8'>";
        $html .= "<style>";
        $html .= "table { border-collapse: collapse; width: 100%; }";
        $html .= "th, td { border: 1px solid #000; padding: 8px; text-align: left; }";
        $html .= "th { background-color: #4472C4; color: white; font-weight: bold; }";
        $html .= ".header { text-align: center; font-size: 16pt; font-weight: bold; }";
        $html .= ".subheader { text-align: center; font-size: 12pt; }";
        $html .= ".logo-cell { width: 100px; height: 80px; text-align: center; vertical-align: middle; }";
        $html .= ".total-row { background-color: #D9E2F3; font-weight: bold; }";
        $html .= ".numero { text-align: center; }";
        $html .= ".horas { text-align: center; }";
        $html .= "</style>";
        $html .= "</head><body>";
        
        $html .= "<table>";
        $html .= "<tr>";
        $html .= "<td class='logo-cell' colspan='1'>[LOGO SEAP]</td>";
        $html .= "<td class='header' colspan='4'>";
        $html .= htmlspecialchars($unidadeNome) . "<br>";
        $html .= "<span class='subheader'>Escala Extraordinária - {$mesNome}/{$ano}</span>";
        $html .= "</td>";
        $html .= "<td class='logo-cell' colspan='1'>[LOGO UNIDADE]</td>";
        $html .= "</tr>";
        $html .= "</table>";
        
        $html .= "<br>";
        
        $html .= "<table>";
        $html .= "<thead>";
        $html .= "<tr>";
        $html .= "<th class='numero'>Núm.</th>";
        $html .= "<th>Matrícula</th>";
        $html .= "<th>Nome do Servidor</th>";
        $html .= "<th class='horas'>Horas</th>";
        $html .= "<th>Unidade</th>";
        $html .= "<th>Dias</th>";
        $html .= "</tr>";
        $html .= "</thead>";
        $html .= "<tbody>";
        
        $num = 1;
        $totalHoras = 0;
        
        foreach ($alocacoesAgrupadas as $a) {
            $diasStr = implode(', ', $a['dias']);
            $totalHoras += $a['horas'];
            
            $html .= "<tr>";
            $html .= "<td class='numero'>" . str_pad($num, 3, '0', STR_PAD_LEFT) . "</td>";
            $html .= "<td>" . htmlspecialchars($a['matricula']) . "</td>";
            $html .= "<td>" . htmlspecialchars($a['servidor_nome']) . "</td>";
            $html .= "<td class='horas'>" . number_format($a['horas'], 0, ',', '.') . "</td>";
            $html .= "<td>" . htmlspecialchars($a['modulo_nome']) . "</td>";
            $html .= "<td>" . htmlspecialchars($diasStr) . "</td>";
            $html .= "</tr>";
            
            $num++;
        }
        
        $html .= "</tbody>";
        $html .= "</table>";
        
        $html .= "<table>";
        $html .= "<tr><td colspan='6'>&nbsp;</td></tr>";
        $html .= "<tr><td colspan='6'>&nbsp;</td></tr>";
        $html .= "<tr>";
        $html .= "<td colspan='3' style='background-color: #FF0000; color: white; font-weight: bold;'>Autorização de quantitativo a maior é realizado pela SGP</td>";
        $html .= "<td style='background-color: #FF0000; color: white; font-weight: bold; text-align: center;'>" . number_format($totalHoras, 0, ',', '.') . "</td>";
        $html .= "<td colspan='2' style='background-color: #FF0000;'></td>";
        $html .= "</tr>";
        $html .= "</table>";
        
        $html .= "</body></html>";
        
        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel; charset=utf-8')
            ->header('Content-Disposition', "attachment; filename=\"{$nomeArquivo}\"")
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}
