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
        
        $budgetInfo = $this->recalcularBudget($escala);
        
        $escala->update([
            'valor_previsto' => $budgetInfo['valor_previsto'],
            'orcamento_mes' => $budgetInfo['orcamento_mes'],
            'limite_margem' => $budgetInfo['limite_margem'],
            'usa_margem' => $budgetInfo['usa_margem'],
            'excede_margem' => $budgetInfo['excede_margem'],
        ]);
        
        if ($budgetInfo['excede_margem']) {
            return redirect('/rh/escalas')->with('error', 
                'Esta escala excede a margem orçamentária e requer aprovação do Superintendente. ' .
                'Valor previsto: R$ ' . number_format($budgetInfo['valor_previsto'], 2, ',', '.') . ' | ' .
                'Limite com margem: R$ ' . number_format($budgetInfo['limite_margem'], 2, ',', '.')
            );
        }
        
        $escala->update([
            'status' => 'aprovada',
            'aprovado_por' => Auth::id(),
            'data_aprovacao' => now(),
        ]);

        return redirect('/rh/escalas')->with('success', 'Escala aprovada!');
    }
    
    private function recalcularBudget(Escala $escala): array
    {
        $unidadeId = $escala->unidade_id;
        $ano = $escala->ano;
        $mes = $escala->mes;
        
        $totalHoras = Alocacao::where('escala_id', $escala->id)
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
