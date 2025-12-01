<?php
namespace App\Controllers;

use App\Core\View;
use App\Core\Session;
use App\Config\Database;

class DiretorController {
    private Database $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function escalaMensal(): void {
        $unidadeId = Session::getUserUnidadeId();
        $mes = (int)($_GET['mes'] ?? date('n'));
        $ano = (int)($_GET['ano'] ?? date('Y'));
        
        if (!$unidadeId) {
            Session::flash('error', 'Você não está vinculado a nenhuma unidade');
            View::redirect('/');
            return;
        }
        
        $unidade = $this->db->fetch("SELECT * FROM unidades WHERE id = :id", ['id' => $unidadeId]);
        
        $escala = $this->db->fetch(
            "SELECT * FROM escalas WHERE unidade_id = :uid AND mes = :mes AND ano = :ano",
            ['uid' => $unidadeId, 'mes' => $mes, 'ano' => $ano]
        );
        
        if (!$escala) {
            $this->db->query(
                "INSERT INTO escalas (unidade_id, mes, ano, status) VALUES (:uid, :mes, :ano, 'rascunho')",
                ['uid' => $unidadeId, 'mes' => $mes, 'ano' => $ano]
            );
            $escala = $this->db->fetch(
                "SELECT * FROM escalas WHERE unidade_id = :uid AND mes = :mes AND ano = :ano",
                ['uid' => $unidadeId, 'mes' => $mes, 'ano' => $ano]
            );
        }
        
        $equipes = $this->db->fetchAll(
            "SELECT * FROM equipes WHERE unidade_id = :uid ORDER BY nome",
            ['uid' => $unidadeId]
        );
        
        $modulos = $this->db->fetchAll(
            "SELECT * FROM modulos WHERE unidade_id = :uid ORDER BY nome",
            ['uid' => $unidadeId]
        );
        
        $servidores = $this->db->fetchAll(
            "SELECT * FROM servidores WHERE unidade_id = :uid AND ativo_extra = true ORDER BY nome",
            ['uid' => $unidadeId]
        );
        
        $alocacoes = $this->db->fetchAll(
            "SELECT a.*, s.nome as servidor_nome, s.matricula 
             FROM alocacoes a 
             JOIN servidores s ON a.servidor_id = s.id
             WHERE a.escala_id = :eid",
            ['eid' => $escala['id']]
        );
        
        $diasNoMes = cal_days_in_month(CAL_GREGORIAN, $mes, $ano);
        
        View::layout('main', 'diretor/escala-mensal', [
            'titulo' => 'Escala Mensal',
            'unidade' => $unidade,
            'escala' => $escala,
            'equipes' => $equipes,
            'modulos' => $modulos,
            'servidores' => $servidores,
            'alocacoes' => $alocacoes,
            'mes' => $mes,
            'ano' => $ano,
            'diasNoMes' => $diasNoMes
        ]);
    }
    
    public function salvarAlocacao(): void {
        $escalaId = (int)($_POST['escala_id'] ?? 0);
        $servidorId = (int)($_POST['servidor_id'] ?? 0);
        $equipeId = (int)($_POST['equipe_id'] ?? 0);
        $moduloId = (int)($_POST['modulo_id'] ?? 0);
        $dia = (int)($_POST['dia'] ?? 0);
        $horas = (float)($_POST['horas'] ?? 0);
        $horasAbono = (float)($_POST['horas_abono'] ?? 0);
        $isLider = isset($_POST['is_lider']) && $_POST['is_lider'] == '1';
        
        $escala = $this->db->fetch("SELECT * FROM escalas WHERE id = :id", ['id' => $escalaId]);
        if (!$escala || $escala['status'] != 'rascunho') {
            View::json(['success' => false, 'message' => 'Escala não pode ser editada']);
            return;
        }
        
        $existing = $this->db->fetch(
            "SELECT id FROM alocacoes WHERE escala_id = :eid AND servidor_id = :sid AND dia = :dia",
            ['eid' => $escalaId, 'sid' => $servidorId, 'dia' => $dia]
        );
        
        if ($existing) {
            $this->db->update('alocacoes', [
                'equipe_id' => $equipeId,
                'modulo_id' => $moduloId,
                'horas' => $horas,
                'horas_abono' => $horasAbono,
                'is_lider' => $isLider
            ], 'id = :id', ['id' => $existing['id']]);
        } else {
            $this->db->query(
                "INSERT INTO alocacoes (escala_id, servidor_id, equipe_id, modulo_id, dia, horas, horas_abono, is_lider) 
                 VALUES (:eid, :sid, :eqid, :mid, :dia, :horas, :habono, :lider)",
                [
                    'eid' => $escalaId, 'sid' => $servidorId, 'eqid' => $equipeId,
                    'mid' => $moduloId, 'dia' => $dia, 'horas' => $horas,
                    'habono' => $horasAbono, 'lider' => $isLider
                ]
            );
        }
        
        $totalHoras = $this->db->fetch(
            "SELECT COALESCE(SUM(horas + horas_abono), 0) as total FROM alocacoes WHERE escala_id = :eid",
            ['eid' => $escalaId]
        )['total'];
        
        $this->db->update('escalas', ['total_horas' => $totalHoras], 'id = :id', ['id' => $escalaId]);
        
        View::json(['success' => true, 'total_horas' => $totalHoras]);
    }
    
    public function removerAlocacao(): void {
        $alocacaoId = (int)($_POST['id'] ?? 0);
        
        $alocacao = $this->db->fetch(
            "SELECT a.*, e.status FROM alocacoes a JOIN escalas e ON a.escala_id = e.id WHERE a.id = :id",
            ['id' => $alocacaoId]
        );
        
        if (!$alocacao || $alocacao['status'] != 'rascunho') {
            View::json(['success' => false, 'message' => 'Alocação não pode ser removida']);
            return;
        }
        
        $this->db->delete('alocacoes', 'id = :id', ['id' => $alocacaoId]);
        
        $totalHoras = $this->db->fetch(
            "SELECT COALESCE(SUM(horas + horas_abono), 0) as total FROM alocacoes WHERE escala_id = :eid",
            ['eid' => $alocacao['escala_id']]
        )['total'];
        
        $this->db->update('escalas', ['total_horas' => $totalHoras], 'id = :id', ['id' => $alocacao['escala_id']]);
        
        View::json(['success' => true, 'total_horas' => $totalHoras]);
    }
    
    public function enviarEscala(): void {
        $unidadeId = Session::getUserUnidadeId();
        $mes = (int)($_GET['mes'] ?? date('n'));
        $ano = (int)($_GET['ano'] ?? date('Y'));
        
        $escala = $this->db->fetch(
            "SELECT * FROM escalas WHERE unidade_id = :uid AND mes = :mes AND ano = :ano",
            ['uid' => $unidadeId, 'mes' => $mes, 'ano' => $ano]
        );
        
        if (!$escala) {
            Session::flash('error', 'Escala não encontrada');
            View::redirect('/diretor/escala-mensal?mes=' . $mes . '&ano=' . $ano);
            return;
        }
        
        $resumoEquipes = $this->db->fetchAll("
            SELECT eq.nome as equipe, SUM(a.horas + a.horas_abono) as total_horas
            FROM alocacoes a
            JOIN equipes eq ON a.equipe_id = eq.id
            WHERE a.escala_id = :eid
            GROUP BY eq.id, eq.nome
            ORDER BY eq.nome
        ", ['eid' => $escala['id']]);
        
        $resumoModulos = $this->db->fetchAll("
            SELECT m.nome as modulo, SUM(a.horas + a.horas_abono) as total_horas
            FROM alocacoes a
            JOIN modulos m ON a.modulo_id = m.id
            WHERE a.escala_id = :eid
            GROUP BY m.id, m.nome
            ORDER BY m.nome
        ", ['eid' => $escala['id']]);
        
        View::layout('main', 'diretor/enviar-escala', [
            'titulo' => 'Enviar Escala para Aprovação',
            'escala' => $escala,
            'resumoEquipes' => $resumoEquipes,
            'resumoModulos' => $resumoModulos,
            'mes' => $mes,
            'ano' => $ano
        ]);
    }
    
    public function confirmarEnvioEscala(): void {
        $escalaId = (int)($_POST['escala_id'] ?? 0);
        
        $escala = $this->db->fetch("SELECT * FROM escalas WHERE id = :id", ['id' => $escalaId]);
        
        if (!$escala) {
            Session::flash('error', 'Escala não encontrada');
            View::redirect('/');
            return;
        }
        
        if ($escala['status'] != 'rascunho') {
            Session::flash('error', 'Esta escala não pode ser enviada');
            View::redirect('/diretor/enviar-escala?mes=' . $escala['mes'] . '&ano=' . $escala['ano']);
            return;
        }
        
        $this->db->update('escalas', [
            'status' => 'pendente',
            'enviado_em' => date('Y-m-d H:i:s'),
            'motivo_rejeicao' => null
        ], 'id = :id', ['id' => $escalaId]);
        
        Session::flash('success', 'Escala enviada para aprovação do RH!');
        View::redirect('/diretor/enviar-escala?mes=' . $escala['mes'] . '&ano=' . $escala['ano']);
    }
    
    public function servidores(): void {
        $unidadeId = Session::getUserUnidadeId();
        
        $servidores = $this->db->fetchAll(
            "SELECT * FROM servidores WHERE unidade_id = :uid ORDER BY nome",
            ['uid' => $unidadeId]
        );
        
        View::layout('main', 'diretor/servidores', [
            'titulo' => 'Servidores da Unidade',
            'servidores' => $servidores
        ]);
    }
}
