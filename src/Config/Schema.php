<?php
namespace App\Config;

class Schema {
    private Database $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function createTables(): void {
        $driver = $this->db->getDriver();
        
        if ($driver === 'mysql') {
            $this->createTablesMysql();
        } else {
            $this->createTablesPostgres();
        }
    }
    
    private function createTablesMysql(): void {
        $sqls = [
            "CREATE TABLE IF NOT EXISTS usuarios (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL UNIQUE,
                senha VARCHAR(255) NOT NULL,
                papel ENUM('superintendente', 'diretor', 'rh', 'administrativo') NOT NULL,
                unidade_id INT NULL,
                ativo TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            "CREATE TABLE IF NOT EXISTS unidades (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(255) NOT NULL,
                local VARCHAR(255),
                responsavel_id INT NULL,
                orcamento_anual DECIMAL(15,2) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            "CREATE TABLE IF NOT EXISTS modulos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                unidade_id INT NOT NULL,
                nome VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (unidade_id) REFERENCES unidades(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            "CREATE TABLE IF NOT EXISTS equipes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                unidade_id INT NOT NULL,
                nome VARCHAR(50) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (unidade_id) REFERENCES unidades(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            "CREATE TABLE IF NOT EXISTS servidores (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(255) NOT NULL,
                matricula VARCHAR(50) NOT NULL UNIQUE,
                unidade_id INT NULL,
                ativo_extra TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (unidade_id) REFERENCES unidades(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            "CREATE TABLE IF NOT EXISTS orcamento_global (
                id INT AUTO_INCREMENT PRIMARY KEY,
                ano INT NOT NULL UNIQUE,
                valor_total DECIMAL(15,2) NOT NULL DEFAULT 0,
                percentual_reserva DECIMAL(5,2) NOT NULL DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            "CREATE TABLE IF NOT EXISTS distribuicao_orcamento (
                id INT AUTO_INCREMENT PRIMARY KEY,
                unidade_id INT NOT NULL,
                ano INT NOT NULL,
                valor DECIMAL(15,2) NOT NULL DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uk_unidade_ano (unidade_id, ano),
                FOREIGN KEY (unidade_id) REFERENCES unidades(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            "CREATE TABLE IF NOT EXISTS log_distribuicao (
                id INT AUTO_INCREMENT PRIMARY KEY,
                unidade_id INT NOT NULL,
                ano INT NOT NULL,
                valor_anterior DECIMAL(15,2),
                valor_novo DECIMAL(15,2),
                tipo VARCHAR(20) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (unidade_id) REFERENCES unidades(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            "CREATE TABLE IF NOT EXISTS escalas (
                id INT AUTO_INCREMENT PRIMARY KEY,
                unidade_id INT NOT NULL,
                mes INT NOT NULL,
                ano INT NOT NULL,
                status ENUM('rascunho', 'pendente', 'aprovada', 'rejeitada', 'executada') DEFAULT 'rascunho',
                motivo_rejeicao TEXT,
                valor_executado DECIMAL(15,2),
                total_horas DECIMAL(10,2) DEFAULT 0,
                enviado_em TIMESTAMP NULL,
                aprovado_em TIMESTAMP NULL,
                executado_em TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uk_unidade_mes_ano (unidade_id, mes, ano),
                FOREIGN KEY (unidade_id) REFERENCES unidades(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            "CREATE TABLE IF NOT EXISTS alocacoes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                escala_id INT NOT NULL,
                servidor_id INT NOT NULL,
                equipe_id INT NOT NULL,
                modulo_id INT NOT NULL,
                dia INT NOT NULL,
                horas DECIMAL(5,2) NOT NULL DEFAULT 0,
                horas_abono DECIMAL(5,2) DEFAULT 0,
                is_lider TINYINT(1) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uk_escala_servidor_dia (escala_id, servidor_id, dia),
                FOREIGN KEY (escala_id) REFERENCES escalas(id) ON DELETE CASCADE,
                FOREIGN KEY (servidor_id) REFERENCES servidores(id) ON DELETE CASCADE,
                FOREIGN KEY (equipe_id) REFERENCES equipes(id) ON DELETE CASCADE,
                FOREIGN KEY (modulo_id) REFERENCES modulos(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            "CREATE TABLE IF NOT EXISTS horas_aprovadas (
                id INT AUTO_INCREMENT PRIMARY KEY,
                escala_id INT NOT NULL,
                servidor_id INT NOT NULL,
                total_horas DECIMAL(10,2) NOT NULL DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (escala_id) REFERENCES escalas(id) ON DELETE CASCADE,
                FOREIGN KEY (servidor_id) REFERENCES servidores(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            "CREATE TABLE IF NOT EXISTS escala_equipe_servidores (
                id INT AUTO_INCREMENT PRIMARY KEY,
                escala_id INT NOT NULL,
                equipe_id INT NOT NULL,
                servidor_id INT NOT NULL,
                is_lider TINYINT(1) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uk_escala_servidor (escala_id, servidor_id),
                FOREIGN KEY (escala_id) REFERENCES escalas(id) ON DELETE CASCADE,
                FOREIGN KEY (equipe_id) REFERENCES equipes(id) ON DELETE CASCADE,
                FOREIGN KEY (servidor_id) REFERENCES servidores(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        ];
        
        foreach ($sqls as $sql) {
            try {
                $this->db->getConnection()->exec($sql);
            } catch (\PDOException $e) {
                if (strpos($e->getMessage(), 'already exists') === false && 
                    strpos($e->getMessage(), 'Duplicate') === false) {
                    throw $e;
                }
            }
        }
    }
    
    private function createTablesPostgres(): void {
        $sql = "
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
        
        CREATE TABLE IF NOT EXISTS unidades (
            id SERIAL PRIMARY KEY,
            nome VARCHAR(255) NOT NULL,
            local VARCHAR(255),
            responsavel_id INTEGER,
            orcamento_anual DECIMAL(15,2) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        
        CREATE TABLE IF NOT EXISTS modulos (
            id SERIAL PRIMARY KEY,
            unidade_id INTEGER NOT NULL REFERENCES unidades(id) ON DELETE CASCADE,
            nome VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        
        CREATE TABLE IF NOT EXISTS equipes (
            id SERIAL PRIMARY KEY,
            unidade_id INTEGER NOT NULL REFERENCES unidades(id) ON DELETE CASCADE,
            nome VARCHAR(50) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        
        CREATE TABLE IF NOT EXISTS servidores (
            id SERIAL PRIMARY KEY,
            nome VARCHAR(255) NOT NULL,
            matricula VARCHAR(50) UNIQUE NOT NULL,
            unidade_id INTEGER REFERENCES unidades(id) ON DELETE SET NULL,
            ativo_extra BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        
        CREATE TABLE IF NOT EXISTS orcamento_global (
            id SERIAL PRIMARY KEY,
            ano INTEGER NOT NULL UNIQUE,
            valor_total DECIMAL(15,2) NOT NULL DEFAULT 0,
            percentual_reserva DECIMAL(5,2) NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        
        CREATE TABLE IF NOT EXISTS distribuicao_orcamento (
            id SERIAL PRIMARY KEY,
            unidade_id INTEGER NOT NULL REFERENCES unidades(id) ON DELETE CASCADE,
            ano INTEGER NOT NULL,
            valor DECIMAL(15,2) NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(unidade_id, ano)
        );
        
        CREATE TABLE IF NOT EXISTS log_distribuicao (
            id SERIAL PRIMARY KEY,
            unidade_id INTEGER NOT NULL REFERENCES unidades(id) ON DELETE CASCADE,
            ano INTEGER NOT NULL,
            valor_anterior DECIMAL(15,2),
            valor_novo DECIMAL(15,2),
            tipo VARCHAR(20) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        
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
        
        CREATE TABLE IF NOT EXISTS horas_aprovadas (
            id SERIAL PRIMARY KEY,
            escala_id INTEGER NOT NULL REFERENCES escalas(id) ON DELETE CASCADE,
            servidor_id INTEGER NOT NULL REFERENCES servidores(id) ON DELETE CASCADE,
            total_horas DECIMAL(10,2) NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        
        CREATE TABLE IF NOT EXISTS escala_equipe_servidores (
            id SERIAL PRIMARY KEY,
            escala_id INTEGER NOT NULL REFERENCES escalas(id) ON DELETE CASCADE,
            equipe_id INTEGER NOT NULL REFERENCES equipes(id) ON DELETE CASCADE,
            servidor_id INTEGER NOT NULL REFERENCES servidores(id) ON DELETE CASCADE,
            is_lider BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(escala_id, servidor_id)
        );
        
        ALTER TABLE usuarios ADD CONSTRAINT fk_usuario_unidade 
            FOREIGN KEY (unidade_id) REFERENCES unidades(id) ON DELETE SET NULL;
        
        ALTER TABLE unidades ADD CONSTRAINT fk_unidade_responsavel 
            FOREIGN KEY (responsavel_id) REFERENCES usuarios(id) ON DELETE SET NULL;
        ";
        
        try {
            $this->db->getConnection()->exec($sql);
        } catch (\PDOException $e) {
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
            $driver = $this->db->getDriver();
            if ($driver === 'mysql') {
                $this->db->query(
                    "INSERT IGNORE INTO orcamento_global (ano, valor_total, percentual_reserva) VALUES (:ano, 0, 10)",
                    ['ano' => date('Y')]
                );
            } else {
                $this->db->query(
                    "INSERT INTO orcamento_global (ano, valor_total, percentual_reserva) VALUES (:ano, 0, 10) ON CONFLICT (ano) DO NOTHING",
                    ['ano' => date('Y')]
                );
            }
        }
    }
}
