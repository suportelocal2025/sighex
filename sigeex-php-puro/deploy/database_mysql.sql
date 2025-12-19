-- =====================================================
-- SIGEEX - Script de Criação do Banco de Dados MySQL
-- Versão: 2.0 - Dezembro 2025
-- =====================================================
-- Importe este arquivo no phpMyAdmin da Hostinger
-- =====================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Limpar tabelas existentes (opcional - descomente se necessário)
-- DROP TABLE IF EXISTS horas_aprovadas;
-- DROP TABLE IF EXISTS escala_equipe_servidores;
-- DROP TABLE IF EXISTS alocacoes;
-- DROP TABLE IF EXISTS escalas;
-- DROP TABLE IF EXISTS log_distribuicao;
-- DROP TABLE IF EXISTS distribuicao_orcamento;
-- DROP TABLE IF EXISTS orcamento_global;
-- DROP TABLE IF EXISTS servidores;
-- DROP TABLE IF EXISTS equipes;
-- DROP TABLE IF EXISTS modulos;
-- DROP TABLE IF EXISTS unidades;
-- DROP TABLE IF EXISTS usuarios;

-- =====================================================
-- TABELAS PRINCIPAIS
-- =====================================================

-- Tabela de usuários
CREATE TABLE IF NOT EXISTS `usuarios` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nome` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `senha` VARCHAR(255) NOT NULL,
    `papel` ENUM('superintendente', 'diretor', 'rh', 'administrativo') NOT NULL,
    `unidade_id` INT NULL,
    `ativo` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de unidades prisionais
CREATE TABLE IF NOT EXISTS `unidades` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nome` VARCHAR(255) NOT NULL,
    `local` VARCHAR(255),
    `responsavel_id` INT NULL,
    `orcamento_anual` DECIMAL(15,2) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de módulos/setores por unidade
CREATE TABLE IF NOT EXISTS `modulos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `unidade_id` INT NOT NULL,
    `nome` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`unidade_id`) REFERENCES `unidades`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de equipes (A, B, C, D por unidade)
CREATE TABLE IF NOT EXISTS `equipes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `unidade_id` INT NOT NULL,
    `nome` VARCHAR(50) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`unidade_id`) REFERENCES `unidades`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de servidores/policiais penais
CREATE TABLE IF NOT EXISTS `servidores` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nome` VARCHAR(255) NOT NULL,
    `matricula` VARCHAR(50) NOT NULL UNIQUE,
    `unidade_id` INT NULL,
    `ativo_extra` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`unidade_id`) REFERENCES `unidades`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de configuração de orçamento global
CREATE TABLE IF NOT EXISTS `orcamento_global` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `ano` INT NOT NULL UNIQUE,
    `valor_total` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `percentual_reserva` DECIMAL(5,2) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de distribuição de orçamento para unidades
CREATE TABLE IF NOT EXISTS `distribuicao_orcamento` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `unidade_id` INT NOT NULL,
    `ano` INT NOT NULL,
    `valor` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_unidade_ano` (`unidade_id`, `ano`),
    FOREIGN KEY (`unidade_id`) REFERENCES `unidades`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Log de distribuição de orçamento (histórico de aportes)
CREATE TABLE IF NOT EXISTS `log_distribuicao` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `unidade_id` INT NOT NULL,
    `ano` INT NOT NULL,
    `valor_anterior` DECIMAL(15,2),
    `valor_novo` DECIMAL(15,2),
    `tipo` VARCHAR(20) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`unidade_id`) REFERENCES `unidades`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de escalas mensais
CREATE TABLE IF NOT EXISTS `escalas` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `unidade_id` INT NOT NULL,
    `mes` INT NOT NULL,
    `ano` INT NOT NULL,
    `status` ENUM('rascunho', 'pendente', 'aprovada', 'rejeitada', 'executada') DEFAULT 'rascunho',
    `motivo_rejeicao` TEXT,
    `valor_executado` DECIMAL(15,2),
    `total_horas` DECIMAL(10,2) DEFAULT 0,
    `enviado_em` TIMESTAMP NULL,
    `aprovado_em` TIMESTAMP NULL,
    `executado_em` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_unidade_mes_ano` (`unidade_id`, `mes`, `ano`),
    FOREIGN KEY (`unidade_id`) REFERENCES `unidades`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Alocações de servidores nas escalas (dias trabalhados)
CREATE TABLE IF NOT EXISTS `alocacoes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `escala_id` INT NOT NULL,
    `servidor_id` INT NOT NULL,
    `equipe_id` INT NOT NULL,
    `modulo_id` INT NOT NULL,
    `dia` INT NOT NULL,
    `horas` DECIMAL(5,2) NOT NULL DEFAULT 0,
    `horas_abono` DECIMAL(5,2) DEFAULT 0,
    `is_lider` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_escala_servidor_dia` (`escala_id`, `servidor_id`, `dia`),
    FOREIGN KEY (`escala_id`) REFERENCES `escalas`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`servidor_id`) REFERENCES `servidores`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`equipe_id`) REFERENCES `equipes`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`modulo_id`) REFERENCES `modulos`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Horas aprovadas por servidor (consolidação)
CREATE TABLE IF NOT EXISTS `horas_aprovadas` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `escala_id` INT NOT NULL,
    `servidor_id` INT NOT NULL,
    `total_horas` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`escala_id`) REFERENCES `escalas`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`servidor_id`) REFERENCES `servidores`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Vínculo de servidores com equipes na escala
CREATE TABLE IF NOT EXISTS `escala_equipe_servidores` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `escala_id` INT NOT NULL,
    `equipe_id` INT NOT NULL,
    `servidor_id` INT NOT NULL,
    `is_lider` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_escala_servidor` (`escala_id`, `servidor_id`),
    FOREIGN KEY (`escala_id`) REFERENCES `escalas`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`equipe_id`) REFERENCES `equipes`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`servidor_id`) REFERENCES `servidores`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- DADOS INICIAIS (Usuários Padrão)
-- =====================================================

-- Inserir usuários padrão (senha: admin123)
-- Hash bcrypt para 'admin123'
INSERT INTO `usuarios` (`nome`, `email`, `senha`, `papel`) VALUES
('Superintendente', 'super@sistema.gov.br', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'superintendente'),
('RH Sistema', 'rh@sistema.gov.br', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'rh'),
('Administrativo', 'admin@sistema.gov.br', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'administrativo')
ON DUPLICATE KEY UPDATE nome = nome;

-- Inserir orçamento para o ano atual
INSERT INTO `orcamento_global` (`ano`, `valor_total`, `percentual_reserva`) VALUES
(YEAR(CURDATE()), 0, 10)
ON DUPLICATE KEY UPDATE ano = ano;

-- =====================================================
-- ÍNDICES ADICIONAIS PARA PERFORMANCE
-- =====================================================

CREATE INDEX IF NOT EXISTS idx_escalas_status ON escalas(status);
CREATE INDEX IF NOT EXISTS idx_escalas_unidade_ano ON escalas(unidade_id, ano);
CREATE INDEX IF NOT EXISTS idx_alocacoes_escala ON alocacoes(escala_id);
CREATE INDEX IF NOT EXISTS idx_servidores_unidade ON servidores(unidade_id);
CREATE INDEX IF NOT EXISTS idx_log_distribuicao_unidade ON log_distribuicao(unidade_id, ano);

-- =====================================================
-- FIM DO SCRIPT
-- =====================================================
