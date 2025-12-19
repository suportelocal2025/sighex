-- =====================================================
-- SIGEEX Laravel - Script MySQL para Hostinger
-- Banco de dados: sigeex-laravel.gestaoderotinas.com.br
-- =====================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------
-- Table: usuarios
-- -----------------------------------------------------
DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `senha` VARCHAR(255) NOT NULL,
  `papel` ENUM('superintendente', 'diretor', 'rh', 'administrativo') NOT NULL DEFAULT 'diretor',
  `unidade_id` BIGINT UNSIGNED NULL,
  `ativo` TINYINT(1) NOT NULL DEFAULT 1,
  `remember_token` VARCHAR(100) NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuarios_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table: unidades
-- -----------------------------------------------------
DROP TABLE IF EXISTS `unidades`;
CREATE TABLE `unidades` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(255) NOT NULL,
  `codigo` VARCHAR(50) NOT NULL,
  `endereco` TEXT NULL,
  `cidade` VARCHAR(255) NULL,
  `ativo` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unidades_codigo_unique` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table: equipes
-- -----------------------------------------------------
DROP TABLE IF EXISTS `equipes`;
CREATE TABLE `equipes` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `unidade_id` BIGINT UNSIGNED NOT NULL,
  `nome` VARCHAR(255) NOT NULL,
  `descricao` TEXT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `equipes_unidade_id_foreign` (`unidade_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table: modulos
-- -----------------------------------------------------
DROP TABLE IF EXISTS `modulos`;
CREATE TABLE `modulos` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `unidade_id` BIGINT UNSIGNED NOT NULL,
  `nome` VARCHAR(255) NOT NULL,
  `descricao` TEXT NULL,
  `capacidade` INT NULL,
  `ativo` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `modulos_unidade_id_foreign` (`unidade_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table: servidores
-- -----------------------------------------------------
DROP TABLE IF EXISTS `servidores`;
CREATE TABLE `servidores` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `unidade_id` BIGINT UNSIGNED NOT NULL,
  `nome` VARCHAR(255) NOT NULL,
  `matricula` VARCHAR(50) NOT NULL,
  `cargo` VARCHAR(255) NULL,
  `email` VARCHAR(255) NULL,
  `telefone` VARCHAR(20) NULL,
  `ativo` TINYINT(1) NOT NULL DEFAULT 1,
  `disponivel_extra` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `servidores_matricula_unique` (`matricula`),
  KEY `servidores_unidade_id_foreign` (`unidade_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table: orcamento_global
-- -----------------------------------------------------
DROP TABLE IF EXISTS `orcamento_global`;
CREATE TABLE `orcamento_global` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ano` INT NOT NULL,
  `valor_total` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `reserva_tecnica_percentual` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orcamento_global_ano_unique` (`ano`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table: distribuicao_orcamento
-- -----------------------------------------------------
DROP TABLE IF EXISTS `distribuicao_orcamento`;
CREATE TABLE `distribuicao_orcamento` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `unidade_id` BIGINT UNSIGNED NOT NULL,
  `ano` INT NOT NULL,
  `valor_distribuido` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `valor_gasto` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `distribuicao_orcamento_unidade_id_foreign` (`unidade_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table: log_distribuicao
-- -----------------------------------------------------
DROP TABLE IF EXISTS `log_distribuicao`;
CREATE TABLE `log_distribuicao` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `unidade_id` BIGINT UNSIGNED NOT NULL,
  `usuario_id` BIGINT UNSIGNED NOT NULL,
  `ano` INT NOT NULL,
  `valor_anterior` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `valor_novo` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `observacao` TEXT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `log_distribuicao_unidade_id_foreign` (`unidade_id`),
  KEY `log_distribuicao_usuario_id_foreign` (`usuario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table: escalas
-- -----------------------------------------------------
DROP TABLE IF EXISTS `escalas`;
CREATE TABLE `escalas` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `unidade_id` BIGINT UNSIGNED NOT NULL,
  `equipe_id` BIGINT UNSIGNED NOT NULL,
  `modulo_id` BIGINT UNSIGNED NULL,
  `mes` INT NOT NULL,
  `ano` INT NOT NULL,
  `status` ENUM('rascunho', 'pendente', 'aprovada', 'rejeitada', 'executada') NOT NULL DEFAULT 'rascunho',
  `motivo_rejeicao` TEXT NULL,
  `valor_executado` DECIMAL(15,2) NULL,
  `aprovado_por` BIGINT UNSIGNED NULL,
  `aprovado_em` TIMESTAMP NULL,
  `executado_por` BIGINT UNSIGNED NULL,
  `executado_em` TIMESTAMP NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `escalas_unidade_id_foreign` (`unidade_id`),
  KEY `escalas_equipe_id_foreign` (`equipe_id`),
  KEY `escalas_modulo_id_foreign` (`modulo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table: alocacoes
-- -----------------------------------------------------
DROP TABLE IF EXISTS `alocacoes`;
CREATE TABLE `alocacoes` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `escala_id` BIGINT UNSIGNED NOT NULL,
  `servidor_id` BIGINT UNSIGNED NOT NULL,
  `data` DATE NOT NULL,
  `horas` INT NOT NULL DEFAULT 12,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `alocacoes_escala_id_foreign` (`escala_id`),
  KEY `alocacoes_servidor_id_foreign` (`servidor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table: escala_equipe_servidores
-- -----------------------------------------------------
DROP TABLE IF EXISTS `escala_equipe_servidores`;
CREATE TABLE `escala_equipe_servidores` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `escala_id` BIGINT UNSIGNED NOT NULL,
  `equipe_id` BIGINT UNSIGNED NOT NULL,
  `servidor_id` BIGINT UNSIGNED NOT NULL,
  `is_lider` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `escala_equipe_servidores_escala_id_foreign` (`escala_id`),
  KEY `escala_equipe_servidores_equipe_id_foreign` (`equipe_id`),
  KEY `escala_equipe_servidores_servidor_id_foreign` (`servidor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table: horas_aprovadas
-- -----------------------------------------------------
DROP TABLE IF EXISTS `horas_aprovadas`;
CREATE TABLE `horas_aprovadas` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `servidor_id` BIGINT UNSIGNED NOT NULL,
  `escala_id` BIGINT UNSIGNED NOT NULL,
  `mes` INT NOT NULL,
  `ano` INT NOT NULL,
  `horas` INT NOT NULL DEFAULT 0,
  `valor` DECIMAL(15,2) NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `horas_aprovadas_servidor_id_foreign` (`servidor_id`),
  KEY `horas_aprovadas_escala_id_foreign` (`escala_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table: sessions (Laravel)
-- -----------------------------------------------------
DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` VARCHAR(255) NOT NULL,
  `user_id` BIGINT UNSIGNED NULL,
  `ip_address` VARCHAR(45) NULL,
  `user_agent` TEXT NULL,
  `payload` LONGTEXT NOT NULL,
  `last_activity` INT NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table: cache (Laravel)
-- -----------------------------------------------------
DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache` (
  `key` VARCHAR(255) NOT NULL,
  `value` MEDIUMTEXT NOT NULL,
  `expiration` INT NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table: cache_locks (Laravel)
-- -----------------------------------------------------
DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE `cache_locks` (
  `key` VARCHAR(255) NOT NULL,
  `owner` VARCHAR(255) NOT NULL,
  `expiration` INT NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table: migrations (Laravel)
-- -----------------------------------------------------
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` VARCHAR(255) NOT NULL,
  `batch` INT NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- FOREIGN KEYS
-- =====================================================

ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_unidade_id_foreign` FOREIGN KEY (`unidade_id`) REFERENCES `unidades` (`id`) ON DELETE SET NULL;

ALTER TABLE `equipes`
  ADD CONSTRAINT `equipes_unidade_id_foreign` FOREIGN KEY (`unidade_id`) REFERENCES `unidades` (`id`) ON DELETE CASCADE;

ALTER TABLE `modulos`
  ADD CONSTRAINT `modulos_unidade_id_foreign` FOREIGN KEY (`unidade_id`) REFERENCES `unidades` (`id`) ON DELETE CASCADE;

ALTER TABLE `servidores`
  ADD CONSTRAINT `servidores_unidade_id_foreign` FOREIGN KEY (`unidade_id`) REFERENCES `unidades` (`id`) ON DELETE CASCADE;

ALTER TABLE `distribuicao_orcamento`
  ADD CONSTRAINT `distribuicao_orcamento_unidade_id_foreign` FOREIGN KEY (`unidade_id`) REFERENCES `unidades` (`id`) ON DELETE CASCADE;

ALTER TABLE `log_distribuicao`
  ADD CONSTRAINT `log_distribuicao_unidade_id_foreign` FOREIGN KEY (`unidade_id`) REFERENCES `unidades` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `log_distribuicao_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

ALTER TABLE `escalas`
  ADD CONSTRAINT `escalas_unidade_id_foreign` FOREIGN KEY (`unidade_id`) REFERENCES `unidades` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `escalas_equipe_id_foreign` FOREIGN KEY (`equipe_id`) REFERENCES `equipes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `escalas_modulo_id_foreign` FOREIGN KEY (`modulo_id`) REFERENCES `modulos` (`id`) ON DELETE SET NULL;

ALTER TABLE `alocacoes`
  ADD CONSTRAINT `alocacoes_escala_id_foreign` FOREIGN KEY (`escala_id`) REFERENCES `escalas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `alocacoes_servidor_id_foreign` FOREIGN KEY (`servidor_id`) REFERENCES `servidores` (`id`) ON DELETE CASCADE;

ALTER TABLE `escala_equipe_servidores`
  ADD CONSTRAINT `escala_equipe_servidores_escala_id_foreign` FOREIGN KEY (`escala_id`) REFERENCES `escalas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `escala_equipe_servidores_equipe_id_foreign` FOREIGN KEY (`equipe_id`) REFERENCES `equipes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `escala_equipe_servidores_servidor_id_foreign` FOREIGN KEY (`servidor_id`) REFERENCES `servidores` (`id`) ON DELETE CASCADE;

ALTER TABLE `horas_aprovadas`
  ADD CONSTRAINT `horas_aprovadas_servidor_id_foreign` FOREIGN KEY (`servidor_id`) REFERENCES `servidores` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `horas_aprovadas_escala_id_foreign` FOREIGN KEY (`escala_id`) REFERENCES `escalas` (`id`) ON DELETE CASCADE;

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- DADOS INICIAIS
-- =====================================================

-- Unidade padrao
INSERT INTO `unidades` (`id`, `nome`, `codigo`, `endereco`, `cidade`, `ativo`, `created_at`, `updated_at`) VALUES
(1, 'Unidade Prisional Modelo', 'UPM-001', 'Rua das Flores, 123', 'Goiania', 1, NOW(), NOW());

-- Equipes da unidade (A, B, C, D)
INSERT INTO `equipes` (`id`, `unidade_id`, `nome`, `descricao`, `created_at`, `updated_at`) VALUES
(1, 1, 'Equipe A', 'Equipe de servico A', NOW(), NOW()),
(2, 1, 'Equipe B', 'Equipe de servico B', NOW(), NOW()),
(3, 1, 'Equipe C', 'Equipe de servico C', NOW(), NOW()),
(4, 1, 'Equipe D', 'Equipe de servico D', NOW(), NOW());

-- Modulos da unidade
INSERT INTO `modulos` (`id`, `unidade_id`, `nome`, `descricao`, `capacidade`, `ativo`, `created_at`, `updated_at`) VALUES
(1, 1, 'Modulo 1', 'Modulo principal', 100, 1, NOW(), NOW()),
(2, 1, 'Modulo 2', 'Modulo secundario', 80, 1, NOW(), NOW()),
(3, 1, 'Modulo 3', 'Modulo de seguranca maxima', 50, 1, NOW(), NOW()),
(4, 1, 'Portaria', 'Controle de acesso', NULL, 1, NOW(), NOW());

-- Usuarios do sistema (senha: admin123)
-- Hash bcrypt para 'admin123': $2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/X4p8Nh8IgGCEz.N2m
INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `papel`, `unidade_id`, `ativo`, `created_at`, `updated_at`) VALUES
(1, 'Superintendente SIGEEX', 'super@sistema.gov.br', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/X4p8Nh8IgGCEz.N2m', 'superintendente', NULL, 1, NOW(), NOW()),
(2, 'Diretor Unidade Modelo', 'diretor@sistema.gov.br', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/X4p8Nh8IgGCEz.N2m', 'diretor', 1, 1, NOW(), NOW()),
(3, 'Recursos Humanos', 'rh@sistema.gov.br', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/X4p8Nh8IgGCEz.N2m', 'rh', NULL, 1, NOW(), NOW()),
(4, 'Administrador Sistema', 'admin@sistema.gov.br', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/X4p8Nh8IgGCEz.N2m', 'administrativo', NULL, 1, NOW(), NOW());

-- Orcamento global para o ano atual
INSERT INTO `orcamento_global` (`id`, `ano`, `valor_total`, `reserva_tecnica_percentual`, `created_at`, `updated_at`) VALUES
(1, YEAR(NOW()), 1000000.00, 10.00, NOW(), NOW());

-- Distribuicao de orcamento para a unidade
INSERT INTO `distribuicao_orcamento` (`id`, `unidade_id`, `ano`, `valor_distribuido`, `valor_gasto`, `created_at`, `updated_at`) VALUES
(1, 1, YEAR(NOW()), 100000.00, 0.00, NOW(), NOW());

-- =====================================================
-- FIM DO SCRIPT
-- =====================================================
