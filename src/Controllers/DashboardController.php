<?php
namespace App\Controllers;

use App\Core\View;
use App\Core\Session;
use App\Config\Database;

class DashboardController {
    private Database $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function index(): void {
        $papel = Session::getUserPapel();
        
        switch ($papel) {
            case 'superintendente':
                $this->superintendenteDashboard();
                break;
            case 'diretor':
                $this->diretorDashboard();
                break;
            case 'rh':
                $this->rhDashboard();
                break;
            case 'administrativo':
                $this->administrativoDashboard();
                break;
            default:
                View::redirect('/login');
        }
    }
    
    private function superintendenteDashboard(): void {
        $ano = (int)($_GET['ano'] ?? date('Y'));
        $periodo = $_GET['periodo'] ?? 'ano';
        
        $orcamento = $this->db->fetch(
            "SELECT * FROM orcamento_global WHERE ano = :ano",
            ['ano' => $ano]
        );
        
        if (!$orcamento) {
            $orcamento = ['valor_total' => 0, 'percentual_reserva' => 10];
        }
        
        $reservaTecnica = ($orcamento['valor_total'] * $orcamento['percentual_reserva']) / 100;
        $valorDisponivel = $orcamento['valor_total'] - $reservaTecnica;
        
        $totalDistribuido = $this->db->fetch(
            "SELECT COALESCE(SUM(valor), 0) as total FROM distribuicao_orcamento WHERE ano = :ano",
            ['ano' => $ano]
        )['total'];
        
        $totalGasto = $this->db->fetch(
            "SELECT COALESCE(SUM(valor_executado), 0) as total FROM escalas WHERE ano = :ano AND status = 'executada'",
            ['ano' => $ano]
        )['total'];
        
        $totalUnidades = $this->db->fetch("SELECT COUNT(*) as total FROM unidades")['total'];
        
        $unidadesStats = $this->db->fetchAll("
            SELECT 
                u.id, u.nome,
                COALESCE(d.valor, 0) as orcamento_distribuido,
                COALESCE(SUM(CASE WHEN e.status = 'executada' THEN e.valor_executado ELSE 0 END), 0) as gasto_total,
                COALESCE(SUM(CASE WHEN e.status IN ('aprovada', 'executada') THEN e.total_horas ELSE 0 END), 0) as horas_total
            FROM unidades u
            LEFT JOIN distribuicao_orcamento d ON u.id = d.unidade_id AND d.ano = :ano1
            LEFT JOIN escalas e ON u.id = e.unidade_id AND e.ano = :ano2
            GROUP BY u.id, u.nome, d.valor
            ORDER BY u.nome
        ", ['ano1' => $ano, 'ano2' => $ano]);
        
        View::layout('main', 'superintendente/dashboard', [
            'titulo' => 'Dashboard do Superintendente',
            'ano' => $ano,
            'periodo' => $periodo,
            'orcamento' => $orcamento,
            'reservaTecnica' => $reservaTecnica,
            'valorDisponivel' => $valorDisponivel,
            'totalDistribuido' => $totalDistribuido,
            'totalGasto' => $totalGasto,
            'totalUnidades' => $totalUnidades,
            'unidadesStats' => $unidadesStats
        ]);
    }
    
    private function diretorDashboard(): void {
        $unidadeId = Session::getUserUnidadeId();
        $ano = (int)($_GET['ano'] ?? date('Y'));
        
        if (!$unidadeId) {
            Session::flash('error', 'Você não está vinculado a nenhuma unidade');
            View::layout('main', 'diretor/dashboard', [
                'titulo' => 'Dashboard do Diretor',
                'unidade' => null,
                'stats' => null
            ]);
            return;
        }
        
        $unidade = $this->db->fetch(
            "SELECT u.*, d.valor as orcamento_anual 
             FROM unidades u 
             LEFT JOIN distribuicao_orcamento d ON u.id = d.unidade_id AND d.ano = :ano
             WHERE u.id = :id",
            ['id' => $unidadeId, 'ano' => $ano]
        );
        
        $totalGasto = $this->db->fetch(
            "SELECT COALESCE(SUM(valor_executado), 0) as total FROM escalas WHERE unidade_id = :uid AND ano = :ano AND status = 'executada'",
            ['uid' => $unidadeId, 'ano' => $ano]
        )['total'];
        
        $horasAprovadas = $this->db->fetch(
            "SELECT COALESCE(SUM(total_horas), 0) as total FROM escalas WHERE unidade_id = :uid AND ano = :ano AND status IN ('aprovada', 'executada')",
            ['uid' => $unidadeId, 'ano' => $ano]
        )['total'];
        
        $escalaMesAtual = $this->db->fetch(
            "SELECT * FROM escalas WHERE unidade_id = :uid AND mes = :mes AND ano = :ano",
            ['uid' => $unidadeId, 'mes' => date('n'), 'ano' => $ano]
        );
        
        $gastosMensais = $this->db->fetchAll(
            "SELECT mes, COALESCE(SUM(valor_executado), 0) as gasto, COALESCE(SUM(total_horas), 0) as horas
             FROM escalas WHERE unidade_id = :uid AND ano = :ano AND status = 'executada'
             GROUP BY mes ORDER BY mes",
            ['uid' => $unidadeId, 'ano' => $ano]
        );
        
        $stats = [
            'orcamento_anual' => $unidade['orcamento_anual'] ?? 0,
            'total_gasto' => $totalGasto,
            'disponivel' => ($unidade['orcamento_anual'] ?? 0) - $totalGasto,
            'horas_aprovadas' => $horasAprovadas,
            'gastos_mensais' => $gastosMensais,
            'escala_mes_atual' => $escalaMesAtual
        ];
        
        View::layout('main', 'diretor/dashboard', [
            'titulo' => 'Dashboard do Diretor',
            'unidade' => $unidade,
            'stats' => $stats,
            'ano' => $ano
        ]);
    }
    
    private function rhDashboard(): void {
        $ano = (int)($_GET['ano'] ?? date('Y'));
        $status = $_GET['status'] ?? 'todos';
        
        $whereStatus = '';
        if ($status !== 'todos') {
            $whereStatus = "AND e.status = '{$status}'";
        }
        
        $escalas = $this->db->fetchAll("
            SELECT e.*, u.nome as unidade_nome
            FROM escalas e
            JOIN unidades u ON e.unidade_id = u.id
            WHERE e.ano = :ano {$whereStatus}
            ORDER BY CASE WHEN e.enviado_em IS NULL THEN 1 ELSE 0 END, e.enviado_em DESC, e.mes DESC
        ", ['ano' => $ano]);
        
        $estatisticas = [
            'pendentes' => $this->db->fetch("SELECT COUNT(*) as total FROM escalas WHERE ano = :ano AND status = 'pendente'", ['ano' => $ano])['total'],
            'aprovadas' => $this->db->fetch("SELECT COUNT(*) as total FROM escalas WHERE ano = :ano AND status = 'aprovada'", ['ano' => $ano])['total'],
            'executadas' => $this->db->fetch("SELECT COUNT(*) as total FROM escalas WHERE ano = :ano AND status = 'executada'", ['ano' => $ano])['total'],
            'rejeitadas' => $this->db->fetch("SELECT COUNT(*) as total FROM escalas WHERE ano = :ano AND status = 'rejeitada'", ['ano' => $ano])['total'],
        ];
        
        View::layout('main', 'rh/dashboard', [
            'titulo' => 'Dashboard do RH',
            'escalas' => $escalas,
            'estatisticas' => $estatisticas,
            'ano' => $ano,
            'statusFiltro' => $status
        ]);
    }
    
    private function administrativoDashboard(): void {
        $totalUnidades = $this->db->fetch("SELECT COUNT(*) as total FROM unidades")['total'];
        $totalServidores = $this->db->fetch("SELECT COUNT(*) as total FROM servidores")['total'];
        $servidoresAtivos = $this->db->fetch("SELECT COUNT(*) as total FROM servidores WHERE ativo_extra = 1")['total'];
        
        View::layout('main', 'administrativo/dashboard', [
            'titulo' => 'Dashboard Administrativo',
            'totalUnidades' => $totalUnidades,
            'totalServidores' => $totalServidores,
            'servidoresAtivos' => $servidoresAtivos
        ]);
    }
}
