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
    
    private function getFeriados(int $ano): array {
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
             WHERE a.escala_id = :eid
             ORDER BY a.dia, s.nome",
            ['eid' => $escala['id']]
        );
        
        $horasPorServidor = $this->db->fetchAll(
            "SELECT servidor_id, SUM(horas + horas_abono) as total_horas 
             FROM alocacoes WHERE escala_id = :eid GROUP BY servidor_id",
            ['eid' => $escala['id']]
        );
        $horasMap = [];
        foreach ($horasPorServidor as $h) {
            $horasMap[$h['servidor_id']] = (float)$h['total_horas'];
        }
        
        $diasNoMes = cal_days_in_month(CAL_GREGORIAN, $mes, $ano);
        $feriados = $this->getFeriados($ano);
        
        $diasInfo = [];
        for ($d = 1; $d <= $diasNoMes; $d++) {
            $data = sprintf('%04d-%02d-%02d', $ano, $mes, $d);
            $diaSemana = date('w', strtotime($data));
            $diasInfo[$d] = [
                'data' => $data,
                'diaSemana' => $diaSemana,
                'nomeDia' => ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'][$diaSemana],
                'isFimDeSemana' => in_array($diaSemana, [0, 6]),
                'isFeriado' => isset($feriados[$data]),
                'nomeFeriado' => $feriados[$data] ?? null
            ];
        }
        
        View::layout('main', 'diretor/escala-mensal', [
            'titulo' => 'Escala Mensal - ' . $unidade['nome'],
            'unidade' => $unidade,
            'escala' => $escala,
            'equipes' => $equipes,
            'modulos' => $modulos,
            'servidores' => $servidores,
            'alocacoes' => $alocacoes,
            'horasMap' => $horasMap,
            'mes' => $mes,
            'ano' => $ano,
            'diasNoMes' => $diasNoMes,
            'diasInfo' => $diasInfo,
            'feriados' => $feriados,
            'limiteHoras' => 60
        ]);
    }
    
    public function salvarAlocacao(): void {
        $escalaId = (int)($_POST['escala_id'] ?? 0);
        $servidorId = (int)($_POST['servidor_id'] ?? 0);
        $equipeId = (int)($_POST['equipe_id'] ?? 0);
        $moduloId = (int)($_POST['modulo_id'] ?? 0);
        $dias = $_POST['dias'] ?? [];
        $horas = (float)($_POST['horas'] ?? 12);
        $horasAbono = (float)($_POST['horas_abono'] ?? 0);
        $isLider = isset($_POST['is_lider']) && $_POST['is_lider'] == '1';
        $forcarMover = isset($_POST['forcar_mover']) && $_POST['forcar_mover'] == '1';
        
        if (is_string($dias)) {
            $dias = explode(',', $dias);
        }
        $dias = array_filter(array_map('intval', $dias));
        
        $escala = $this->db->fetch("SELECT * FROM escalas WHERE id = :id", ['id' => $escalaId]);
        if (!$escala || !in_array($escala['status'], ['rascunho', 'rejeitada'])) {
            View::json(['success' => false, 'message' => 'Escala não pode ser editada']);
            return;
        }
        
        $horasAtuais = $this->db->fetch(
            "SELECT COALESCE(SUM(horas + horas_abono), 0) as total FROM alocacoes WHERE escala_id = :eid AND servidor_id = :sid",
            ['eid' => $escalaId, 'sid' => $servidorId]
        )['total'];
        
        $horasNovas = count($dias) * ($horas + $horasAbono);
        $totalProjetado = $horasAtuais + $horasNovas;
        
        if ($totalProjetado > 60) {
            View::json([
                'success' => false, 
                'message' => "Limite de 60 horas excedido! O servidor já possui {$horasAtuais}h alocadas. Máximo disponível: " . (60 - $horasAtuais) . "h"
            ]);
            return;
        }
        
        $conflitos = [];
        foreach ($dias as $dia) {
            $existing = $this->db->fetch(
                "SELECT a.*, eq.nome as equipe_nome, m.nome as modulo_nome 
                 FROM alocacoes a 
                 LEFT JOIN equipes eq ON a.equipe_id = eq.id
                 LEFT JOIN modulos m ON a.modulo_id = m.id
                 WHERE a.escala_id = :eid AND a.servidor_id = :sid AND a.dia = :dia",
                ['eid' => $escalaId, 'sid' => $servidorId, 'dia' => $dia]
            );
            
            if ($existing) {
                if ($existing['equipe_id'] != $equipeId || $existing['modulo_id'] != $moduloId) {
                    $conflitos[] = [
                        'dia' => $dia,
                        'alocacao_id' => $existing['id'],
                        'equipe_atual' => $existing['equipe_nome'],
                        'modulo_atual' => $existing['modulo_nome']
                    ];
                }
            }
        }
        
        if (!empty($conflitos) && !$forcarMover) {
            View::json([
                'success' => false,
                'conflito' => true,
                'conflitos' => $conflitos,
                'message' => 'Servidor já alocado em outro local para alguns dias'
            ]);
            return;
        }
        
        foreach ($dias as $dia) {
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
        }
        
        $totalHoras = $this->db->fetch(
            "SELECT COALESCE(SUM(horas + horas_abono), 0) as total FROM alocacoes WHERE escala_id = :eid",
            ['eid' => $escalaId]
        )['total'];
        
        $this->db->update('escalas', ['total_horas' => $totalHoras], 'id = :id', ['id' => $escalaId]);
        
        $horasServidor = $this->db->fetch(
            "SELECT COALESCE(SUM(horas + horas_abono), 0) as total FROM alocacoes WHERE escala_id = :eid AND servidor_id = :sid",
            ['eid' => $escalaId, 'sid' => $servidorId]
        )['total'];
        
        View::json(['success' => true, 'total_horas' => $totalHoras, 'horas_servidor' => $horasServidor]);
    }
    
    public function removerAlocacao(): void {
        $alocacaoId = (int)($_POST['id'] ?? 0);
        $servidorId = (int)($_POST['servidor_id'] ?? 0);
        $escalaId = (int)($_POST['escala_id'] ?? 0);
        $dia = (int)($_POST['dia'] ?? 0);
        
        if ($alocacaoId > 0) {
            $alocacao = $this->db->fetch(
                "SELECT a.*, e.status FROM alocacoes a JOIN escalas e ON a.escala_id = e.id WHERE a.id = :id",
                ['id' => $alocacaoId]
            );
            
            if (!$alocacao || !in_array($alocacao['status'], ['rascunho', 'rejeitada'])) {
                View::json(['success' => false, 'message' => 'Alocação não pode ser removida']);
                return;
            }
            
            $this->db->delete('alocacoes', 'id = :id', ['id' => $alocacaoId]);
            $escalaId = $alocacao['escala_id'];
            $servidorId = $alocacao['servidor_id'];
        } elseif ($servidorId > 0 && $escalaId > 0 && $dia > 0) {
            $escala = $this->db->fetch("SELECT status FROM escalas WHERE id = :id", ['id' => $escalaId]);
            if (!$escala || !in_array($escala['status'], ['rascunho', 'rejeitada'])) {
                View::json(['success' => false, 'message' => 'Escala não pode ser editada']);
                return;
            }
            
            $this->db->query(
                "DELETE FROM alocacoes WHERE escala_id = :eid AND servidor_id = :sid AND dia = :dia",
                ['eid' => $escalaId, 'sid' => $servidorId, 'dia' => $dia]
            );
        }
        
        $totalHoras = $this->db->fetch(
            "SELECT COALESCE(SUM(horas + horas_abono), 0) as total FROM alocacoes WHERE escala_id = :eid",
            ['eid' => $escalaId]
        )['total'];
        
        $this->db->update('escalas', ['total_horas' => $totalHoras], 'id = :id', ['id' => $escalaId]);
        
        $horasServidor = 0;
        if ($servidorId > 0) {
            $horasServidor = $this->db->fetch(
                "SELECT COALESCE(SUM(horas + horas_abono), 0) as total FROM alocacoes WHERE escala_id = :eid AND servidor_id = :sid",
                ['eid' => $escalaId, 'sid' => $servidorId]
            )['total'];
        }
        
        View::json(['success' => true, 'total_horas' => $totalHoras, 'horas_servidor' => $horasServidor]);
    }
    
    public function verificarAlocacao(): void {
        $escalaId = (int)($_GET['escala_id'] ?? 0);
        $servidorId = (int)($_GET['servidor_id'] ?? 0);
        
        $alocacoes = $this->db->fetchAll(
            "SELECT a.*, eq.nome as equipe_nome, m.nome as modulo_nome 
             FROM alocacoes a 
             LEFT JOIN equipes eq ON a.equipe_id = eq.id
             LEFT JOIN modulos m ON a.modulo_id = m.id
             WHERE a.escala_id = :eid AND a.servidor_id = :sid
             ORDER BY a.dia",
            ['eid' => $escalaId, 'sid' => $servidorId]
        );
        
        $totalHoras = $this->db->fetch(
            "SELECT COALESCE(SUM(horas + horas_abono), 0) as total FROM alocacoes WHERE escala_id = :eid AND servidor_id = :sid",
            ['eid' => $escalaId, 'sid' => $servidorId]
        )['total'];
        
        View::json([
            'success' => true,
            'alocacoes' => $alocacoes,
            'total_horas' => $totalHoras,
            'disponivel' => 60 - $totalHoras
        ]);
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
        
        if (!in_array($escala['status'], ['rascunho', 'rejeitada'])) {
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
    
    public function reabrirEscala(): void {
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
        
        if ($escala['status'] != 'rejeitada') {
            Session::flash('error', 'Apenas escalas rejeitadas podem ser editadas');
            View::redirect('/diretor/escala-mensal?mes=' . $mes . '&ano=' . $ano);
            return;
        }
        
        $this->db->update('escalas', [
            'status' => 'rascunho',
            'motivo_rejeicao' => null
        ], 'id = :id', ['id' => $escala['id']]);
        
        Session::flash('success', 'Escala reaberta para edição. Faça as correções e envie novamente.');
        View::redirect('/diretor/escala-mensal?mes=' . $mes . '&ano=' . $ano);
    }
    
    public function listarServidoresDisponiveis(): void {
        try {
            $unidadeId = Session::getUserUnidadeId();
            $escalaId = (int)($_GET['escala_id'] ?? 0);
            $equipeId = (int)($_GET['equipe_id'] ?? 0);
            
            if (!$unidadeId) {
                $user = Session::getUser();
                View::json([
                    'success' => false, 
                    'message' => 'Unidade não encontrada. Faça login novamente.', 
                    'debug' => ['user' => $user],
                    'servidores' => []
                ]);
                return;
            }
            
            $servidores = $this->db->fetchAll(
                "SELECT s.id, s.nome, s.matricula, 
                        ees.equipe_id as equipe_atual_id,
                        e.nome as equipe_atual
                 FROM servidores s
                 LEFT JOIN escala_equipe_servidores ees ON ees.servidor_id = s.id AND ees.escala_id = :eid
                 LEFT JOIN equipes e ON e.id = ees.equipe_id
                 WHERE s.unidade_id = :uid
                 ORDER BY s.nome",
                ['uid' => $unidadeId, 'eid' => $escalaId]
            );
            
            View::json(['success' => true, 'servidores' => $servidores ?: [], 'unidade_id' => $unidadeId]);
        } catch (\Exception $e) {
            View::json(['success' => false, 'message' => $e->getMessage(), 'servidores' => []]);
        }
    }
    
    public function adicionarServidorEquipe(): void {
        $unidadeId = Session::getUserUnidadeId();
        $escalaId = (int)($_POST['escala_id'] ?? 0);
        $equipeId = (int)($_POST['equipe_id'] ?? 0);
        $servidorIds = $_POST['servidor_ids'] ?? [];
        
        if (!is_array($servidorIds)) {
            $servidorIds = [$servidorIds];
        }
        
        $escala = $this->db->fetch("SELECT * FROM escalas WHERE id = :id AND unidade_id = :uid", 
            ['id' => $escalaId, 'uid' => $unidadeId]);
        
        if (!$escala || !in_array($escala['status'], ['rascunho', 'rejeitada'])) {
            View::json(['success' => false, 'message' => 'Escala não pode ser editada']);
            return;
        }
        
        $adicionados = 0;
        $conflitos = [];
        
        foreach ($servidorIds as $servidorId) {
            $servidorId = (int)$servidorId;
            
            $jaVinculado = $this->db->fetch(
                "SELECT ees.*, e.nome as equipe_nome 
                 FROM escala_equipe_servidores ees
                 JOIN equipes e ON e.id = ees.equipe_id
                 WHERE ees.escala_id = :eid AND ees.servidor_id = :sid",
                ['eid' => $escalaId, 'sid' => $servidorId]
            );
            
            if ($jaVinculado) {
                if ($jaVinculado['equipe_id'] != $equipeId) {
                    $servidor = $this->db->fetch("SELECT nome FROM servidores WHERE id = :id", ['id' => $servidorId]);
                    $conflitos[] = [
                        'servidor' => $servidor['nome'],
                        'equipe_atual' => $jaVinculado['equipe_nome']
                    ];
                }
                continue;
            }
            
            $this->db->query(
                "INSERT INTO escala_equipe_servidores (escala_id, equipe_id, servidor_id) VALUES (:eid, :eqid, :sid)",
                ['eid' => $escalaId, 'eqid' => $equipeId, 'sid' => $servidorId]
            );
            $adicionados++;
        }
        
        if (count($conflitos) > 0) {
            View::json([
                'success' => $adicionados > 0,
                'message' => "$adicionados servidor(es) adicionado(s). " . count($conflitos) . " já vinculado(s) a outras equipes.",
                'conflitos' => $conflitos
            ]);
        } else {
            View::json(['success' => true, 'message' => "$adicionados servidor(es) adicionado(s) à equipe"]);
        }
    }
    
    public function removerServidorEquipe(): void {
        $unidadeId = Session::getUserUnidadeId();
        $escalaId = (int)($_POST['escala_id'] ?? 0);
        $servidorId = (int)($_POST['servidor_id'] ?? 0);
        
        $escala = $this->db->fetch("SELECT * FROM escalas WHERE id = :id AND unidade_id = :uid", 
            ['id' => $escalaId, 'uid' => $unidadeId]);
        
        if (!$escala || !in_array($escala['status'], ['rascunho', 'rejeitada'])) {
            View::json(['success' => false, 'message' => 'Escala não pode ser editada']);
            return;
        }
        
        $this->db->query(
            "DELETE FROM alocacoes WHERE escala_id = :eid AND servidor_id = :sid",
            ['eid' => $escalaId, 'sid' => $servidorId]
        );
        
        $this->db->query(
            "DELETE FROM escala_equipe_servidores WHERE escala_id = :eid AND servidor_id = :sid",
            ['eid' => $escalaId, 'sid' => $servidorId]
        );
        
        View::json(['success' => true, 'message' => 'Servidor removido da equipe']);
    }
    
    public function listarServidoresEquipe(): void {
        $unidadeId = Session::getUserUnidadeId();
        $escalaId = (int)($_GET['escala_id'] ?? 0);
        $equipeId = (int)($_GET['equipe_id'] ?? 0);
        
        $servidores = $this->db->fetchAll(
            "SELECT s.*, ees.is_lider,
                    COALESCE(SUM(a.horas + a.horas_abono), 0) as total_horas
             FROM escala_equipe_servidores ees
             JOIN servidores s ON s.id = ees.servidor_id
             LEFT JOIN alocacoes a ON a.escala_id = ees.escala_id AND a.servidor_id = ees.servidor_id
             WHERE ees.escala_id = :eid AND ees.equipe_id = :eqid
             GROUP BY s.id, ees.is_lider
             ORDER BY s.nome",
            ['eid' => $escalaId, 'eqid' => $equipeId]
        );
        
        View::json(['success' => true, 'servidores' => $servidores]);
    }
    
    public function atualizarLiderEquipe(): void {
        $escalaId = (int)($_POST['escala_id'] ?? 0);
        $servidorId = (int)($_POST['servidor_id'] ?? 0);
        $isLider = (bool)($_POST['is_lider'] ?? false);
        
        $unidadeId = Session::getUserUnidadeId();
        $escala = $this->db->fetch("SELECT * FROM escalas WHERE id = :id AND unidade_id = :uid", 
            ['id' => $escalaId, 'uid' => $unidadeId]);
        
        if (!$escala || !in_array($escala['status'], ['rascunho', 'rejeitada'])) {
            View::json(['success' => false, 'message' => 'Escala não pode ser editada']);
            return;
        }
        
        $this->db->query(
            "UPDATE escala_equipe_servidores SET is_lider = :lider WHERE escala_id = :eid AND servidor_id = :sid",
            ['lider' => $isLider ? 't' : 'f', 'eid' => $escalaId, 'sid' => $servidorId]
        );
        
        $this->db->query(
            "UPDATE alocacoes SET is_lider = :lider WHERE escala_id = :eid AND servidor_id = :sid",
            ['lider' => $isLider ? 't' : 'f', 'eid' => $escalaId, 'sid' => $servidorId]
        );
        
        View::json(['success' => true]);
    }
}
