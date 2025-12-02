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
            $diaSemana = (int)date('w', strtotime($data));
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
        try {
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
            
            if (empty($dias)) {
                View::json(['success' => false, 'message' => 'Nenhum dia selecionado']);
                return;
            }
            
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
                        'habono' => $horasAbono, 'lider' => $isLider ? 't' : 'f'
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
        } catch (\Exception $e) {
            View::json(['success' => false, 'message' => 'Erro ao salvar: ' . $e->getMessage()]);
        }
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
            
            if (!$unidadeId) {
                View::json([
                    'success' => false, 
                    'message' => 'Unidade não encontrada. Faça login novamente.', 
                    'servidores' => []
                ]);
                return;
            }
            
            $servidores = $this->db->fetchAll(
                "SELECT id, nome, matricula FROM servidores WHERE unidade_id = :uid ORDER BY nome",
                ['uid' => $unidadeId]
            );
            
            $result = [];
            foreach ($servidores ?: [] as $s) {
                $result[] = [
                    'id' => $s['id'],
                    'nome' => $s['nome'],
                    'matricula' => $s['matricula'],
                    'equipe_atual_id' => null,
                    'equipe_atual' => null
                ];
            }
            
            View::json(['success' => true, 'servidores' => $result, 'unidade_id' => $unidadeId]);
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
    
    public function imprimirMural(): void {
        $unidadeId = Session::getUserUnidadeId();
        $mes = (int)($_GET['mes'] ?? date('m'));
        $ano = (int)($_GET['ano'] ?? date('Y'));
        
        $unidade = $this->db->fetch("SELECT * FROM unidades WHERE id = :id", ['id' => $unidadeId]);
        
        $escala = $this->db->fetch(
            "SELECT * FROM escalas WHERE unidade_id = :uid AND mes = :mes AND ano = :ano",
            ['uid' => $unidadeId, 'mes' => $mes, 'ano' => $ano]
        );
        
        if (!$escala) {
            echo "Escala não encontrada.";
            return;
        }
        
        $alocacoes = $this->db->fetchAll("
            SELECT a.*, s.nome as servidor_nome, s.matricula, 
                   eq.nome as equipe_nome, m.nome as modulo_nome
            FROM alocacoes a
            JOIN servidores s ON a.servidor_id = s.id
            JOIN equipes eq ON a.equipe_id = eq.id
            JOIN modulos m ON a.modulo_id = m.id
            WHERE a.escala_id = :eid
            ORDER BY m.nome, eq.nome, s.nome, a.dia
        ", ['eid' => $escala['id']]);
        
        $agrupado = [];
        foreach ($alocacoes as $a) {
            $key = $a['modulo_nome'] . '|' . $a['equipe_nome'];
            if (!isset($agrupado[$key])) {
                $agrupado[$key] = [
                    'modulo' => $a['modulo_nome'],
                    'equipe' => $a['equipe_nome'],
                    'servidores' => []
                ];
            }
            $sKey = $a['servidor_id'];
            if (!isset($agrupado[$key]['servidores'][$sKey])) {
                $agrupado[$key]['servidores'][$sKey] = [
                    'nome' => $a['servidor_nome'],
                    'matricula' => $a['matricula'],
                    'is_lider' => $a['is_lider'],
                    'dias' => [],
                    'horas' => 0
                ];
            }
            $agrupado[$key]['servidores'][$sKey]['dias'][] = str_pad($a['dia'], 2, '0', STR_PAD_LEFT);
            $agrupado[$key]['servidores'][$sKey]['horas'] += $a['horas'] + $a['horas_abono'];
        }
        
        foreach ($agrupado as &$grupo) {
            foreach ($grupo['servidores'] as &$srv) {
                sort($srv['dias']);
            }
        }
        unset($grupo, $srv);
        
        $meses = ['', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 
                  'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
        
        ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Escala - <?= htmlspecialchars($unidade['nome']) ?> - <?= $meses[$mes] ?>/<?= $ano ?></title>
    <style>
        @page { margin: 1.5cm; size: A4 portrait; }
        * { box-sizing: border-box; }
        body { 
            font-family: Arial, sans-serif; 
            font-size: 11pt; 
            line-height: 1.4;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #1a4480;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .header h1 {
            margin: 0 0 5px 0;
            font-size: 18pt;
            color: #1a4480;
        }
        .header h2 {
            margin: 0;
            font-size: 14pt;
            font-weight: normal;
            color: #666;
        }
        .grupo {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        .grupo-header {
            background: #1a4480;
            color: white;
            padding: 8px 12px;
            font-weight: bold;
            font-size: 11pt;
            border-radius: 4px 4px 0 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px 10px;
            text-align: left;
        }
        th {
            background: #f5f5f5;
            font-weight: bold;
            font-size: 10pt;
        }
        td { font-size: 10pt; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .lider { color: #d4a00a; font-weight: bold; }
        .dias { font-family: monospace; font-size: 9pt; }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9pt;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 30px; font-size: 14pt; cursor: pointer;">
            Imprimir / Salvar PDF
        </button>
    </div>
    
    <div class="header">
        <h1><?= htmlspecialchars($unidade['nome']) ?></h1>
        <h2>Escala Extraordinária - <?= $meses[$mes] ?>/<?= $ano ?></h2>
    </div>
    
    <?php if (empty($agrupado)): ?>
        <p style="text-align: center; color: #666;">Nenhuma alocação encontrada para esta escala.</p>
    <?php else: ?>
        <?php foreach ($agrupado as $grupo): ?>
        <div class="grupo">
            <div class="grupo-header">
                <?= htmlspecialchars($grupo['modulo']) ?> - <?= htmlspecialchars($grupo['equipe']) ?>
            </div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 35%">Nome</th>
                        <th style="width: 15%">Matrícula</th>
                        <th style="width: 35%">Dias</th>
                        <th style="width: 15%" class="text-center">Total Horas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($grupo['servidores'] as $srv): ?>
                    <tr>
                        <td>
                            <?= htmlspecialchars($srv['nome']) ?>
                            <?php if ($srv['is_lider']): ?>
                                <span class="lider">★</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($srv['matricula']) ?></td>
                        <td class="dias"><?= implode(', ', $srv['dias']) ?></td>
                        <td class="text-center"><?= number_format($srv['horas'], 0) ?>h</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <div class="footer">
        Documento gerado em <?= date('d/m/Y H:i') ?> - SGEEX - Sistema de Gestão de Escalas Extraordinárias
    </div>
</body>
</html>
        <?php
    }
}
