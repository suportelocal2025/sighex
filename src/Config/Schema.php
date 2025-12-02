<?php
namespace App\Config;

class Schema {
    private Database $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function createTables(): void {
        $sql = "
        -- Tabela de usuários
        CREATE TABLE IF NOT EXISTS usuarios (
            id SERIAL PRIMARY KEY,
            nome VARCHAR(255) NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            senha VARCHAR(255) NOT NULL,
            papel VARCHAR(50) NOT NULL CHECK (papel IN ('superintendente', 'diretor', 'rh', 'administrativo')),
            unidade_id INTEGER,
            ativo BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        
        -- Tabela de unidades prisionais
        CREATE TABLE IF NOT EXISTS unidades (
            id SERIAL PRIMARY KEY,
            nome VARCHAR(255) NOT NULL,
            local VARCHAR(255),
            responsavel_id INTEGER,
            orcamento_anual DECIMAL(15,2) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        
        -- Tabela de módulos/setores por unidade
        CREATE TABLE IF NOT EXISTS modulos (
            id SERIAL PRIMARY KEY,
            unidade_id INTEGER NOT NULL REFERENCES unidades(id) ON DELETE CASCADE,
            nome VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        
        -- Tabela de equipes (A, B, C, D por unidade)
        CREATE TABLE IF NOT EXISTS equipes (
            id SERIAL PRIMARY KEY,
            unidade_id INTEGER NOT NULL REFERENCES unidades(id) ON DELETE CASCADE,
            nome VARCHAR(50) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        
        -- Tabela de servidores/policiais penais
        CREATE TABLE IF NOT EXISTS servidores (
            id SERIAL PRIMARY KEY,
            nome VARCHAR(255) NOT NULL,
            matricula VARCHAR(50) UNIQUE NOT NULL,
            unidade_id INTEGER REFERENCES unidades(id) ON DELETE SET NULL,
            ativo_extra BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        
        -- Tabela de configuração de orçamento global
        CREATE TABLE IF NOT EXISTS orcamento_global (
            id SERIAL PRIMARY KEY,
            ano INTEGER NOT NULL UNIQUE,
            valor_total DECIMAL(15,2) NOT NULL DEFAULT 0,
            percentual_reserva DECIMAL(5,2) NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        
        -- Tabela de distribuição de orçamento para unidades
        CREATE TABLE IF NOT EXISTS distribuicao_orcamento (
            id SERIAL PRIMARY KEY,
            unidade_id INTEGER NOT NULL REFERENCES unidades(id) ON DELETE CASCADE,
            ano INTEGER NOT NULL,
            valor DECIMAL(15,2) NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(unidade_id, ano)
        );
        
        -- Log de distribuição de orçamento
        CREATE TABLE IF NOT EXISTS log_distribuicao (
            id SERIAL PRIMARY KEY,
            unidade_id INTEGER NOT NULL REFERENCES unidades(id) ON DELETE CASCADE,
            ano INTEGER NOT NULL,
            valor_anterior DECIMAL(15,2),
            valor_novo DECIMAL(15,2),
            tipo VARCHAR(20) NOT NULL, -- 'adicao' ou 'alteracao'
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        
        -- Tabela de escalas mensais
        CREATE TABLE IF NOT EXISTS escalas (
            id SERIAL PRIMARY KEY,
            unidade_id INTEGER NOT NULL REFERENCES unidades(id) ON DELETE CASCADE,
            mes INTEGER NOT NULL CHECK (mes BETWEEN 1 AND 12),
            ano INTEGER NOT NULL,
            status VARCHAR(20) DEFAULT 'rascunho' CHECK (status IN ('rascunho', 'pendente', 'aprovada', 'rejeitada', 'executada')),
            motivo_rejeicao TEXT,
            valor_executado DECIMAL(15,2),
            total_horas DECIMAL(10,2) DEFAULT 0,
            enviado_em TIMESTAMP,
            aprovado_em TIMESTAMP,
            executado_em TIMESTAMP,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(unidade_id, mes, ano)
        );
        
        -- Alocações de servidores nas escalas
        CREATE TABLE IF NOT EXISTS alocacoes (
            id SERIAL PRIMARY KEY,
            escala_id INTEGER NOT NULL REFERENCES escalas(id) ON DELETE CASCADE,
            servidor_id INTEGER NOT NULL REFERENCES servidores(id) ON DELETE CASCADE,
            equipe_id INTEGER NOT NULL REFERENCES equipes(id) ON DELETE CASCADE,
            modulo_id INTEGER NOT NULL REFERENCES modulos(id) ON DELETE CASCADE,
            dia INTEGER NOT NULL CHECK (dia BETWEEN 1 AND 31),
            horas DECIMAL(5,2) NOT NULL DEFAULT 0,
            horas_abono DECIMAL(5,2) DEFAULT 0,
            is_lider BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(escala_id, servidor_id, dia)
        );
        
        -- Horas aprovadas por servidor
        CREATE TABLE IF NOT EXISTS horas_aprovadas (
            id SERIAL PRIMARY KEY,
            escala_id INTEGER NOT NULL REFERENCES escalas(id) ON DELETE CASCADE,
            servidor_id INTEGER NOT NULL REFERENCES servidores(id) ON DELETE CASCADE,
            total_horas DECIMAL(10,2) NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        
        -- Vínculo de servidores com equipes na escala
        CREATE TABLE IF NOT EXISTS escala_equipe_servidores (
            id SERIAL PRIMARY KEY,
            escala_id INTEGER NOT NULL REFERENCES escalas(id) ON DELETE CASCADE,
            equipe_id INTEGER NOT NULL REFERENCES equipes(id) ON DELETE CASCADE,
            servidor_id INTEGER NOT NULL REFERENCES servidores(id) ON DELETE CASCADE,
            is_lider BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(escala_id, servidor_id)
        );
        
        -- Adicionar FK de usuario para unidade
        ALTER TABLE usuarios ADD CONSTRAINT fk_usuario_unidade 
            FOREIGN KEY (unidade_id) REFERENCES unidades(id) ON DELETE SET NULL;
        
        -- Adicionar FK de unidade para responsavel
        ALTER TABLE unidades ADD CONSTRAINT fk_unidade_responsavel 
            FOREIGN KEY (responsavel_id) REFERENCES usuarios(id) ON DELETE SET NULL;
        ";
        
        try {
            $this->db->getConnection()->exec($sql);
        } catch (\PDOException $e) {
            // Ignorar erros de constraint já existente
            if (strpos($e->getMessage(), 'already exists') === false) {
                throw $e;
            }
        }
    }
    
    public function seedDefaultData(): void {
        // Verificar se já existe um superintendente
        $exists = $this->db->fetch("SELECT id FROM usuarios WHERE papel = 'superintendente' LIMIT 1");
        
        if (!$exists) {
            // Criar usuário superintendente padrão
            $senha = password_hash('admin123', PASSWORD_DEFAULT);
            $this->db->query(
                "INSERT INTO usuarios (nome, email, senha, papel) VALUES (:nome, :email, :senha, :papel)",
                [
                    'nome' => 'Superintendente',
                    'email' => 'super@sistema.gov.br',
                    'senha' => $senha,
                    'papel' => 'superintendente'
                ]
            );
            
            // Criar usuário RH padrão
            $this->db->query(
                "INSERT INTO usuarios (nome, email, senha, papel) VALUES (:nome, :email, :senha, :papel)",
                [
                    'nome' => 'RH Sistema',
                    'email' => 'rh@sistema.gov.br',
                    'senha' => $senha,
                    'papel' => 'rh'
                ]
            );
            
            // Criar usuário Administrativo padrão
            $this->db->query(
                "INSERT INTO usuarios (nome, email, senha, papel) VALUES (:nome, :email, :senha, :papel)",
                [
                    'nome' => 'Administrativo',
                    'email' => 'admin@sistema.gov.br',
                    'senha' => $senha,
                    'papel' => 'administrativo'
                ]
            );
            
            // Criar orçamento para o ano atual
            $this->db->query(
                "INSERT INTO orcamento_global (ano, valor_total, percentual_reserva) VALUES (:ano, 0, 10) ON CONFLICT (ano) DO NOTHING",
                ['ano' => date('Y')]
            );
        }
    }
}
