# Instruções de Deploy - SIGEEX na Hostinger

## Passo a Passo

### 1. Criar o Banco de Dados MySQL na Hostinger

1. Acesse o **hPanel** da Hostinger
2. Vá em **Bancos de Dados** → **Bancos de Dados MySQL**
3. Crie um novo banco de dados:
   - **Nome do banco**: escolha um nome (ex: sigeex_db)
   - **Usuário**: crie um usuário
   - **Senha**: defina uma senha forte
4. Anote as credenciais criadas

### 2. Importar a Estrutura do Banco

1. No hPanel, vá em **Bancos de Dados** → **phpMyAdmin**
2. Selecione o banco de dados criado
3. Clique na aba **Importar**
4. Faça upload do arquivo `database_mysql.sql` (incluído nesta pasta)
5. Clique em **Executar**

### 3. Configurar as Credenciais

1. Abra o arquivo `config/database.php`
2. Preencha as credenciais do seu banco:

```php
<?php
return [
    'host' => 'localhost',
    'database' => 'SEU_BANCO_DE_DADOS',    // Nome do banco criado
    'username' => 'SEU_USUARIO',            // Usuário do banco
    'password' => 'SUA_SENHA',              // Senha do banco
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci'
];
```

### 4. Fazer Upload via FTP

1. Acesse o **Gerenciador de Arquivos** no hPanel ou use um cliente FTP (FileZilla)
2. Navegue até a pasta `public_html` do seu subdomínio
3. Faça upload de **todos os arquivos** do projeto:
   - `index.php`
   - `.htaccess`
   - `composer.json`
   - Pasta `config/`
   - Pasta `src/`
   - Pasta `views/`

### 5. Estrutura Final de Arquivos

Após o upload, a estrutura deve ficar assim:

```
public_html/sigeex.gestaoderotinas.com.br/
├── .htaccess
├── index.php
├── composer.json
├── config/
│   ├── .htaccess
│   └── database.php
├── src/
│   ├── Config/
│   ├── Controllers/
│   └── Core/
└── views/
    ├── layouts/
    ├── auth/
    ├── superintendente/
    ├── diretor/
    ├── rh/
    └── administrativo/
```

### 6. Configurar PHP (Opcional)

1. No hPanel, vá em **PHP Configuration**
2. Certifique-se de que a versão do PHP é **8.1 ou superior**
3. Verifique se as extensões estão habilitadas:
   - `pdo_mysql`
   - `mbstring`
   - `json`

### 7. Testar o Sistema

1. Acesse: https://sigeex.gestaoderotinas.com.br
2. Faça login com as credenciais padrão:

| Papel           | Email                     | Senha     |
|-----------------|---------------------------|-----------|
| Superintendente | super@sistema.gov.br      | admin123  |
| Diretor         | diretor@sistema.gov.br    | admin123  |
| RH              | rh@sistema.gov.br         | admin123  |
| Administrativo  | admin@sistema.gov.br      | admin123  |

### 8. Alterar Senhas (IMPORTANTE!)

Após o primeiro acesso, altere as senhas padrão para maior segurança.

---

## Solução de Problemas

### Erro 500 (Internal Server Error)
- Verifique as permissões dos arquivos (644 para arquivos, 755 para pastas)
- Verifique o arquivo `.htaccess`
- Veja os logs de erro no hPanel

### Erro de conexão com o banco
- Confira as credenciais em `config/database.php`
- Verifique se o banco foi criado corretamente
- Teste a conexão pelo phpMyAdmin

### Página em branco
- Ative exibição de erros temporariamente adicionando no início do `index.php`:
```php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

---

## Suporte

Em caso de dúvidas, verifique:
- Documentação da Hostinger: https://support.hostinger.com
- Logs de erro no hPanel
