<?php
/**
 * Configuração do Banco de Dados MySQL para Hostinger
 * 
 * IMPORTANTE: Este arquivo só é usado quando as variáveis de ambiente
 * PostgreSQL (PGHOST, PGDATABASE, etc.) NÃO estão definidas.
 * 
 * No ambiente Replit, as variáveis PostgreSQL são definidas automaticamente,
 * então este arquivo é ignorado durante o desenvolvimento.
 * 
 * Para deploy na Hostinger, preencha as credenciais abaixo.
 */
return [
    'host' => 'localhost',
    'database' => 'SEU_BANCO_DE_DADOS',    // Preencha com o nome do banco criado na Hostinger
    'username' => 'SEU_USUARIO',            // Preencha com o usuário do banco
    'password' => 'SUA_SENHA',              // Preencha com a senha do banco
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci'
];
