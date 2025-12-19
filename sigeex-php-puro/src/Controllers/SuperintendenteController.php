<?php
namespace App\Controllers;

use App\Core\View;
use App\Core\Session;
use App\Config\Database;

class SuperintendenteController {
    private Database $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function orcamento(): void {
        $ano = (int)($_GET['ano'] ?? date('Y'));
        
        $orcamento = $this->db->fetch(
            "SELECT * FROM orcamento_global WHERE ano = :ano",
            ['ano' => $ano]
        );
        
        if (!$orcamento) {
            $orcamento = ['ano' => $ano, 'valor_total' => 0, 'percentual_reserva' => 10];
        }
        
        $reservaTecnica = ($orcamento['valor_total'] * $orcamento['percentual_reserva']) / 100;
        $valorDisponivel = $orcamento['valor_total'] - $reservaTecnica;
        
        View::layout('main', 'superintendente/orcamento', [
            'titulo' => 'Configuração de Orçamento',
            'orcamento' => $orcamento,
            'ano' => $ano,
            'reservaTecnica' => $reservaTecnica,
            'valorDisponivel' => $valorDisponivel
        ]);
    }
    
    public function salvarOrcamento(): void {
        $ano = (int)($_POST['ano'] ?? date('Y'));
        $valorTotal = (float)str_replace(['.', ','], ['', '.'], $_POST['valor_total'] ?? '0');
        $percentualReserva = (float)($_POST['percentual_reserva'] ?? 10);
        
        if ($percentualReserva < 0 || $percentualReserva > 100) {
            Session::flash('error', 'Percentual de reserva deve estar entre 0 e 100');
            View::redirect('/superintendente/orcamento?ano=' . $ano);
            return;
        }
        
        $existing = $this->db->fetch(
            "SELECT id FROM orcamento_global WHERE ano = :ano",
            ['ano' => $ano]
        );
        
        if ($existing) {
            $this->db->update('orcamento_global', [
                'valor_total' => $valorTotal,
                'percentual_reserva' => $percentualReserva,
                'updated_at' => date('Y-m-d H:i:s')
            ], 'ano = :ano', ['ano' => $ano]);
        } else {
            $this->db->query(
                "INSERT INTO orcamento_global (ano, valor_total, percentual_reserva) VALUES (:ano, :valor, :reserva)",
                ['ano' => $ano, 'valor' => $valorTotal, 'reserva' => $percentualReserva]
            );
        }
        
        Session::flash('success', 'Orçamento salvo com sucesso!');
        View::redirect('/superintendente/orcamento?ano=' . $ano);
    }
    
    public function distribuicao(): void {
        $ano = (int)($_GET['ano'] ?? date('Y'));
        
        $orcamento = $this->db->fetch(
            "SELECT * FROM orcamento_global WHERE ano = :ano",
            ['ano' => $ano]
        );
        
        if (!$orcamento) {
            $orcamento = ['valor_total' => 0, 'percentual_reserva' => 10];
        }
        
        $reservaTecnica = ($orcamento['valor_total'] * $orcamento['percentual_reserva']) / 100;
        $valorDisponivel = $orcamento['valor_total'] - $reservaTecnica;
        
        $unidades = $this->db->fetchAll("
            SELECT u.id, u.nome, COALESCE(d.valor, 0) as valor_distribuido
            FROM unidades u
            LEFT JOIN distribuicao_orcamento d ON u.id = d.unidade_id AND d.ano = :ano
            ORDER BY u.nome
        ", ['ano' => $ano]);
        
        $totalDistribuido = array_sum(array_column($unidades, 'valor_distribuido'));
        
        View::layout('main', 'superintendente/distribuicao', [
            'titulo' => 'Distribuição de Orçamento',
            'unidades' => $unidades,
            'ano' => $ano,
            'valorDisponivel' => $valorDisponivel,
            'totalDistribuido' => $totalDistribuido,
            'saldoRestante' => $valorDisponivel - $totalDistribuido
        ]);
    }
    
    public function salvarDistribuicao(): void {
        $ano = (int)($_POST['ano'] ?? date('Y'));
        $distribuicoes = $_POST['distribuicao'] ?? [];
        
        $orcamento = $this->db->fetch(
            "SELECT * FROM orcamento_global WHERE ano = :ano",
            ['ano' => $ano]
        );
        
        if (!$orcamento) {
            Session::flash('error', 'Configure o orçamento antes de distribuir');
            View::redirect('/superintendente/distribuicao?ano=' . $ano);
            return;
        }
        
        $reservaTecnica = ($orcamento['valor_total'] * $orcamento['percentual_reserva']) / 100;
        $valorDisponivel = $orcamento['valor_total'] - $reservaTecnica;
        
        $total = 0;
        foreach ($distribuicoes as $valor) {
            $total += (float)str_replace(['.', ','], ['', '.'], $valor);
        }
        
        if ($total > $valorDisponivel) {
            Session::flash('error', 'O total distribuído excede o valor disponível');
            View::redirect('/superintendente/distribuicao?ano=' . $ano);
            return;
        }
        
        foreach ($distribuicoes as $unidadeId => $valor) {
            $valorFloat = (float)str_replace(['.', ','], ['', '.'], $valor);
            
            $existing = $this->db->fetch(
                "SELECT id, valor FROM distribuicao_orcamento WHERE unidade_id = :uid AND ano = :ano",
                ['uid' => $unidadeId, 'ano' => $ano]
            );
            
            if ($existing) {
                if ((float)$existing['valor'] != $valorFloat) {
                    $this->db->query(
                        "INSERT INTO log_distribuicao (unidade_id, ano, valor_anterior, valor_novo, tipo) VALUES (:uid, :ano, :anterior, :novo, 'alteracao')",
                        ['uid' => $unidadeId, 'ano' => $ano, 'anterior' => $existing['valor'], 'novo' => $valorFloat]
                    );
                    $this->db->update('distribuicao_orcamento', [
                        'valor' => $valorFloat,
                        'updated_at' => date('Y-m-d H:i:s')
                    ], 'id = :id', ['id' => $existing['id']]);
                }
            } else if ($valorFloat > 0) {
                $this->db->query(
                    "INSERT INTO distribuicao_orcamento (unidade_id, ano, valor) VALUES (:uid, :ano, :valor)",
                    ['uid' => $unidadeId, 'ano' => $ano, 'valor' => $valorFloat]
                );
                $this->db->query(
                    "INSERT INTO log_distribuicao (unidade_id, ano, valor_anterior, valor_novo, tipo) VALUES (:uid, :ano, 0, :novo, 'adicao')",
                    ['uid' => $unidadeId, 'ano' => $ano, 'novo' => $valorFloat]
                );
            }
        }
        
        Session::flash('success', 'Distribuição salva com sucesso!');
        View::redirect('/superintendente/distribuicao?ano=' . $ano);
    }
    
    public function relatorios(): void {
        View::layout('main', 'superintendente/relatorios', [
            'titulo' => 'Relatórios'
        ]);
    }
    
    public function historicoDistribuicao(): void {
        $unidadeId = (int)($_GET['unidade_id'] ?? 0);
        $ano = (int)($_GET['ano'] ?? date('Y'));
        
        if ($unidadeId <= 0 || $ano < 2000 || $ano > 2100) {
            View::json(['success' => false, 'message' => 'Parâmetros inválidos']);
            return;
        }
        
        $unidade = $this->db->fetch("SELECT nome FROM unidades WHERE id = :id", ['id' => $unidadeId]);
        
        if (!$unidade) {
            View::json(['success' => false, 'message' => 'Unidade não encontrada']);
            return;
        }
        
        $historico = $this->db->fetchAll("
            SELECT 
                id,
                valor_anterior,
                valor_novo,
                tipo,
                created_at
            FROM log_distribuicao 
            WHERE unidade_id = :uid AND ano = :ano
            ORDER BY created_at DESC
        ", ['uid' => $unidadeId, 'ano' => $ano]);
        
        $historicoFormatado = array_map(function($h) {
            return [
                'id' => (int)$h['id'],
                'valor_anterior' => (float)$h['valor_anterior'],
                'valor_novo' => (float)$h['valor_novo'],
                'tipo' => in_array($h['tipo'], ['adicao', 'alteracao']) ? $h['tipo'] : 'desconhecido',
                'created_at' => $h['created_at']
            ];
        }, $historico);
        
        View::json([
            'success' => true,
            'unidade' => htmlspecialchars($unidade['nome'], ENT_QUOTES, 'UTF-8'),
            'ano' => $ano,
            'historico' => $historicoFormatado
        ]);
    }
}
