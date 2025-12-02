-- Inserir usuários padrão do SIGEEX
-- Senha: admin123 (hash gerado com password_hash do PHP)

INSERT INTO usuarios (nome, email, senha, papel, ativo) VALUES 
('Superintendente', 'super@sistema.gov.br', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'superintendente', 1),
('Diretor', 'diretor@sistema.gov.br', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'diretor', 1),
('RH Sistema', 'rh@sistema.gov.br', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'rh', 1),
('Administrativo', 'admin@sistema.gov.br', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'administrativo', 1);

-- Inserir orçamento para o ano atual
INSERT IGNORE INTO orcamento_global (ano, valor_total, percentual_reserva) VALUES (2025, 0, 10);
