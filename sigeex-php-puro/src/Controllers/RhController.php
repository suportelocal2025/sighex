<?php
namespace App\Controllers;

use App\Core\View;
use App\Core\Session;
use App\Config\Database;

class RhController {
    private Database $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function escalas(): void {
        $ano = (int)($_GET['ano'] ?? date('Y'));
        $status = $_GET['status'] ?? 'todos';
        
        $whereStatus = '';
        $params = ['ano' => $ano];
        if ($status !== 'todos') {
            $whereStatus = "AND e.status = :status";
            $params['status'] = $status;
        }
        
        $escalas = $this->db->fetchAll("
            SELECT e.*, u.nome as unidade_nome
            FROM escalas e
            JOIN unidades u ON e.unidade_id = u.id
            WHERE e.ano = :ano {$whereStatus}
            ORDER BY 
                CASE e.status 
                    WHEN 'pendente' THEN 1 
                    WHEN 'aprovada' THEN 2 
                    WHEN 'executada' THEN 3 
                    WHEN 'rejeitada' THEN 4 
                    ELSE 5 
                END,
                e.mes DESC
        ", $params);
        
        View::layout('main', 'rh/escalas', [
            'titulo' => 'Gestão de Escalas',
            'escalas' => $escalas,
            'ano' => $ano,
            'statusFiltro' => $status
        ]);
    }
    
    public function detalharEscala(string $id): void {
        $escala = $this->db->fetch("
            SELECT e.*, u.nome as unidade_nome
            FROM escalas e
            JOIN unidades u ON e.unidade_id = u.id
            WHERE e.id = :id
        ", ['id' => $id]);
        
        if (!$escala) {
            Session::flash('error', 'Escala não encontrada');
            View::redirect('/rh/escalas');
            return;
        }
        
        $alocacoes = $this->db->fetchAll("
            SELECT a.*, s.nome as servidor_nome, s.matricula, eq.nome as equipe_nome, m.nome as modulo_nome
            FROM alocacoes a
            JOIN servidores s ON a.servidor_id = s.id
            JOIN equipes eq ON a.equipe_id = eq.id
            JOIN modulos m ON a.modulo_id = m.id
            WHERE a.escala_id = :eid
            ORDER BY eq.nome, s.nome, a.dia
        ", ['eid' => $id]);
        
        $resumoPorServidor = $this->db->fetchAll("
            SELECT s.nome, s.matricula, SUM(a.horas) as horas, SUM(a.horas_abono) as abono, 
                   SUM(a.horas + a.horas_abono) as total
            FROM alocacoes a
            JOIN servidores s ON a.servidor_id = s.id
            WHERE a.escala_id = :eid
            GROUP BY s.id, s.nome, s.matricula
            ORDER BY s.nome
        ", ['eid' => $id]);
        
        View::layout('main', 'rh/detalhar-escala', [
            'titulo' => 'Detalhes da Escala',
            'escala' => $escala,
            'alocacoes' => $alocacoes,
            'resumoPorServidor' => $resumoPorServidor
        ]);
    }
    
    public function exportarEscalaExcel(string $id): void {
        $meses = ['', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 
                  'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
        
        $escala = $this->db->fetch("
            SELECT e.*, u.nome as unidade_nome
            FROM escalas e
            JOIN unidades u ON e.unidade_id = u.id
            WHERE e.id = :id
        ", ['id' => $id]);
        
        if (!$escala) {
            Session::flash('error', 'Escala não encontrada');
            View::redirect('/rh/escalas');
            return;
        }
        
        $alocacoes = $this->db->fetchAll("
            SELECT a.*, s.nome as servidor_nome, s.matricula, eq.nome as equipe_nome, m.nome as modulo_nome
            FROM alocacoes a
            JOIN servidores s ON a.servidor_id = s.id
            JOIN equipes eq ON a.equipe_id = eq.id
            JOIN modulos m ON a.modulo_id = m.id
            WHERE a.escala_id = :eid
            ORDER BY s.nome, a.dia
        ", ['eid' => $id]);
        
        $alocacoesAgrupadas = [];
        foreach ($alocacoes as $a) {
            $key = $a['servidor_id'] . '_' . $a['modulo_id'];
            if (!isset($alocacoesAgrupadas[$key])) {
                $alocacoesAgrupadas[$key] = [
                    'servidor_nome' => $a['servidor_nome'],
                    'matricula' => $a['matricula'],
                    'modulo_nome' => $a['modulo_nome'],
                    'dias' => [],
                    'horas' => 0
                ];
            }
            $alocacoesAgrupadas[$key]['dias'][] = str_pad($a['dia'], 2, '0', STR_PAD_LEFT);
            $alocacoesAgrupadas[$key]['horas'] += $a['horas'] + $a['horas_abono'];
        }
        
        foreach ($alocacoesAgrupadas as &$ag) {
            sort($ag['dias']);
        }
        unset($ag);
        
        $unidadeNome = $escala['unidade_nome'];
        $mesNome = $meses[$escala['mes']];
        $ano = $escala['ano'];
        $nomeArquivo = "escala_{$escala['id']}_{$mesNome}_{$ano}.xls";
        
        header("Content-Type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=\"{$nomeArquivo}\"");
        header("Pragma: no-cache");
        header("Expires: 0");
        
        echo "\xEF\xBB\xBF";
        
        echo "<html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:x='urn:schemas-microsoft-com:office:excel'>";
        echo "<head><meta charset='UTF-8'>";
        echo "<style>";
        echo "table { border-collapse: collapse; width: 100%; }";
        echo "th, td { border: 1px solid #000; padding: 8px; text-align: left; }";
        echo "th { background-color: #4472C4; color: white; font-weight: bold; }";
        echo ".header { text-align: center; font-size: 16pt; font-weight: bold; }";
        echo ".subheader { text-align: center; font-size: 12pt; }";
        echo ".logo-cell { width: 100px; height: 80px; text-align: center; vertical-align: middle; }";
        echo ".total-row { background-color: #D9E2F3; font-weight: bold; }";
        echo ".numero { text-align: center; }";
        echo ".horas { text-align: center; }";
        echo "</style>";
        echo "</head><body>";
        
        echo "<table>";
        echo "<tr>";
        echo "<td class='logo-cell' colspan='1'>[LOGO SEAP]</td>";
        echo "<td class='header' colspan='4'>";
        echo htmlspecialchars($unidadeNome) . "<br>";
        echo "<span class='subheader'>Escala Extraordinária - {$mesNome}/{$ano}</span>";
        echo "</td>";
        echo "<td class='logo-cell' colspan='1'>[LOGO UNIDADE]</td>";
        echo "</tr>";
        echo "</table>";
        
        echo "<br>";
        
        echo "<table>";
        echo "<thead>";
        echo "<tr>";
        echo "<th class='numero'>Núm.</th>";
        echo "<th>Matrícula</th>";
        echo "<th>Nome do Servidor</th>";
        echo "<th class='horas'>Horas</th>";
        echo "<th>Unidade</th>";
        echo "<th>Dias</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        
        $num = 1;
        $totalHoras = 0;
        
        foreach ($alocacoesAgrupadas as $a) {
            $diasStr = implode(', ', $a['dias']);
            $totalHoras += $a['horas'];
            
            echo "<tr>";
            echo "<td class='numero'>" . str_pad($num, 3, '0', STR_PAD_LEFT) . "</td>";
            echo "<td>" . htmlspecialchars($a['matricula']) . "</td>";
            echo "<td>" . htmlspecialchars($a['servidor_nome']) . "</td>";
            echo "<td class='horas'>" . number_format($a['horas'], 0, ',', '.') . "</td>";
            echo "<td>" . htmlspecialchars($a['modulo_nome']) . "</td>";
            echo "<td>" . htmlspecialchars($diasStr) . "</td>";
            echo "</tr>";
            
            $num++;
        }
        
        echo "</tbody>";
        echo "</table>";
        
        echo "<table>";
        echo "<tr><td colspan='6'>&nbsp;</td></tr>";
        echo "<tr><td colspan='6'>&nbsp;</td></tr>";
        echo "<tr>";
        echo "<td colspan='3' style='background-color: #FF0000; color: white; font-weight: bold;'>Autorização de quantitativo a maior é realizado pela SGP</td>";
        echo "<td style='background-color: #FF0000; color: white; font-weight: bold; text-align: center;'>" . number_format($totalHoras, 0, ',', '.') . "</td>";
        echo "<td colspan='2' style='background-color: #FF0000;'></td>";
        echo "</tr>";
        echo "</table>";
        
        echo "</body></html>";
        exit;
    }
    
    public function aprovarEscala(): void {
        $escalaId = (int)($_POST['escala_id'] ?? 0);
        
        $escala = $this->db->fetch("SELECT * FROM escalas WHERE id = :id", ['id' => $escalaId]);
        
        if (!$escala || $escala['status'] != 'pendente') {
            View::json(['success' => false, 'message' => 'Escala não pode ser aprovada']);
            return;
        }
        
        $this->db->update('escalas', [
            'status' => 'aprovada',
            'aprovado_em' => date('Y-m-d H:i:s')
        ], 'id = :id', ['id' => $escalaId]);
        
        $alocacoes = $this->db->fetchAll("
            SELECT servidor_id, SUM(horas + horas_abono) as total_horas
            FROM alocacoes WHERE escala_id = :eid
            GROUP BY servidor_id
        ", ['eid' => $escalaId]);
        
        foreach ($alocacoes as $alocacao) {
            $this->db->query(
                "INSERT INTO horas_aprovadas (escala_id, servidor_id, total_horas) VALUES (:eid, :sid, :horas)",
                ['eid' => $escalaId, 'sid' => $alocacao['servidor_id'], 'horas' => $alocacao['total_horas']]
            );
        }
        
        View::json(['success' => true, 'message' => 'Escala aprovada com sucesso!']);
    }
    
    public function rejeitarEscala(): void {
        $escalaId = (int)($_POST['escala_id'] ?? 0);
        $motivo = trim($_POST['motivo'] ?? '');
        
        if (empty($motivo)) {
            View::json(['success' => false, 'message' => 'Motivo da rejeição é obrigatório']);
            return;
        }
        
        $escala = $this->db->fetch("SELECT * FROM escalas WHERE id = :id", ['id' => $escalaId]);
        
        if (!$escala || $escala['status'] != 'pendente') {
            View::json(['success' => false, 'message' => 'Escala não pode ser rejeitada']);
            return;
        }
        
        $this->db->update('escalas', [
            'status' => 'rejeitada',
            'motivo_rejeicao' => $motivo
        ], 'id = :id', ['id' => $escalaId]);
        
        View::json(['success' => true, 'message' => 'Escala rejeitada']);
    }
    
    public function executarEscala(): void {
        $escalaId = (int)($_POST['escala_id'] ?? 0);
        $valorExecutado = (float)str_replace(['.', ','], ['', '.'], $_POST['valor_executado'] ?? '0');
        
        $escala = $this->db->fetch("SELECT * FROM escalas WHERE id = :id", ['id' => $escalaId]);
        
        if (!$escala || $escala['status'] != 'aprovada') {
            View::json(['success' => false, 'message' => 'Escala não pode ser marcada como executada']);
            return;
        }
        
        $this->db->update('escalas', [
            'status' => 'executada',
            'valor_executado' => $valorExecutado,
            'executado_em' => date('Y-m-d H:i:s')
        ], 'id = :id', ['id' => $escalaId]);
        
        View::json(['success' => true, 'message' => 'Escala marcada como executada!']);
    }
    
    public function relatorios(): void {
        $ano = (int)($_GET['ano'] ?? date('Y'));
        
        $unidades = $this->db->fetchAll("SELECT id, nome FROM unidades ORDER BY nome");
        
        View::layout('main', 'rh/relatorios', [
            'titulo' => 'Relatórios',
            'unidades' => $unidades,
            'ano' => $ano
        ]);
    }
    
    public function gerarRelatorio(): void {
        $tipo = $_GET['tipo'] ?? 'horas';
        $ano = (int)($_GET['ano'] ?? date('Y'));
        $mes = $_GET['mes'] ?? null;
        $unidadeId = $_GET['unidade_id'] ?? null;
        $formato = $_GET['formato'] ?? 'html';
        
        $params = ['ano' => $ano];
        $whereMes = '';
        $whereUnidade = '';
        
        if ($mes && $mes !== 'todos') {
            $whereMes = ' AND e.mes = :mes';
            $params['mes'] = (int)$mes;
        }
        
        if ($unidadeId && $unidadeId !== 'todas') {
            $whereUnidade = ' AND e.unidade_id = :uid';
            $params['uid'] = (int)$unidadeId;
        }
        
        if ($tipo === 'horas') {
            $dados = $this->db->fetchAll("
                SELECT u.nome as unidade, s.nome as servidor, s.matricula, 
                       e.mes, ha.total_horas
                FROM horas_aprovadas ha
                JOIN escalas e ON ha.escala_id = e.id
                JOIN unidades u ON e.unidade_id = u.id
                JOIN servidores s ON ha.servidor_id = s.id
                WHERE e.ano = :ano AND e.status IN ('aprovada', 'executada') {$whereMes} {$whereUnidade}
                ORDER BY u.nome, s.nome, e.mes
            ", $params);
        } else {
            $dados = $this->db->fetchAll("
                SELECT u.nome as unidade, e.mes, e.valor_executado, e.total_horas
                FROM escalas e
                JOIN unidades u ON e.unidade_id = u.id
                WHERE e.ano = :ano AND e.status = 'executada' {$whereMes} {$whereUnidade}
                ORDER BY u.nome, e.mes
            ", $params);
        }
        
        if ($formato === 'csv') {
            $this->exportarCSV($dados, $tipo);
        } elseif ($formato === 'excel') {
            $this->exportarExcel($dados, $tipo, $ano);
        } elseif ($formato === 'pdf') {
            $this->exportarPDF($dados, $tipo, $ano);
        } else {
            View::layout('main', 'rh/relatorio-resultado', [
                'titulo' => 'Resultado do Relatório',
                'dados' => $dados,
                'tipo' => $tipo,
                'ano' => $ano
            ]);
        }
    }
    
    private function exportarCSV(array $dados, string $tipo): void {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="relatorio_' . $tipo . '_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        if (!empty($dados)) {
            fputcsv($output, array_keys($dados[0]), ';');
            foreach ($dados as $row) {
                fputcsv($output, $row, ';');
            }
        }
        
        fclose($output);
        exit;
    }
    
    private function exportarExcel(array $dados, string $tipo, int $ano): void {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="relatorio_' . $tipo . '_' . $ano . '.xls"');
        
        echo "<table border='1'>";
        if (!empty($dados)) {
            echo "<tr>";
            foreach (array_keys($dados[0]) as $header) {
                echo "<th>" . htmlspecialchars($header) . "</th>";
            }
            echo "</tr>";
            foreach ($dados as $row) {
                echo "<tr>";
                foreach ($row as $cell) {
                    echo "<td>" . htmlspecialchars($cell ?? '') . "</td>";
                }
                echo "</tr>";
            }
        }
        echo "</table>";
        exit;
    }
    
    private function exportarPDF(array $dados, string $tipo, int $ano): void {
        View::layout('main', 'rh/relatorio-resultado', [
            'titulo' => 'Resultado do Relatório',
            'dados' => $dados,
            'tipo' => $tipo,
            'ano' => $ano,
            'printMode' => true
        ]);
    }
}
