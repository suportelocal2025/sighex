# Instruções de Deploy - SIGEEX

## Hospedagem na Hostinger

### Passo 1: Preparar os Arquivos

1. Faça download do arquivo `sigeex_deploy.zip` do Replit
2. Extraia o conteúdo do ZIP localmente (opcional, para verificar)

### Passo 2: Upload dos Arquivos

1. Acesse o **Gerenciador de Arquivos** da Hostinger
2. Navegue até a pasta `public_html` do domínio/subdomínio
3. Faça upload do arquivo `sigeex_deploy.zip`
4. Extraia o ZIP diretamente no servidor
5. **IMPORTANTE**: Renomeie `htaccess.txt` para `.htaccess`

### Passo 3: Criar o Banco de Dados

1. Acesse **Bancos de Dados MySQL** no painel da Hostinger
2. Crie um novo banco de dados (ex: `u123456789_sigeex`)
3. Crie um usuário para o banco de dados
4. Anote: nome do banco, usuário e senha

### Passo 4: Importar a Estrutura do Banco

1. Acesse o **phpMyAdmin**
2. Selecione o banco de dados criado
3. Vá em **Importar**
4. Selecione o arquivo `deploy/database_mysql.sql`
5. Clique em **Executar**

### Passo 5: Configurar a Conexão

Edite o arquivo `src/Config/Database.php` e configure as credenciais MySQL:

```php
// Procure esta seção no arquivo:
$host = 'localhost';
$dbname = 'u123456789_sigeex';     // Seu nome de banco
$user = 'u123456789_sigeex';        // Seu usuário
$password = 'SuaSenhaAqui';         // Sua senha
```

### Passo 6: Configurar PHP

No painel da Hostinger, em **Configurações PHP**:
- Versão do PHP: **8.0** ou superior
- Extensões habilitadas: `pdo`, `pdo_mysql`, `mbstring`, `json`

### Passo 7: Verificar .htaccess

Certifique-se de que o arquivo `.htaccess` está na raiz (`public_html`) com o seguinte conteúdo:

```apache
RewriteEngine On
RewriteBase /

# Redirecionar para HTTPS
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Redirecionar todas as requisições para index.php
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [L,QSA]

# Segurança
<Files .htaccess>
    Order Allow,Deny
    Deny from all
</Files>
```

### Passo 8: Testar

1. Acesse a URL do sistema (ex: `https://sigeex.gestaoderotinas.com.br`)
2. Faça login com as credenciais padrão:

| Papel | Email | Senha |
|-------|-------|-------|
| Superintendente | super@sistema.gov.br | admin123 |
| RH | rh@sistema.gov.br | admin123 |
| Administrativo | admin@sistema.gov.br | admin123 |

3. **IMPORTANTE**: Troque as senhas padrão após o primeiro acesso!

---

## Estrutura de Arquivos

```
public_html/
├── .htaccess              # Renomear de htaccess.txt
├── index.php              # Ponto de entrada
├── composer.json
├── src/
│   ├── Config/
│   │   ├── Database.php   # CONFIGURAR CREDENCIAIS AQUI
│   │   └── Schema.php
│   ├── Controllers/
│   └── Core/
├── views/
├── config/
├── deploy/
│   ├── database_mysql.sql # Script para importar no phpMyAdmin
│   └── INSTRUCOES_DEPLOY.md
└── docs/
    └── REQUISITOS_TECNICOS.md
```

---

## Resolução de Problemas

### Erro 500 - Internal Server Error
1. Verifique se a versão do PHP é 8.0+
2. Verifique se o `.htaccess` foi criado corretamente
3. Confira as permissões dos arquivos (644 para arquivos, 755 para pastas)

### Erro de Conexão com Banco
1. Verifique as credenciais em `src/Config/Database.php`
2. Confirme que o banco foi criado e o usuário tem permissões
3. Verifique se o host é `localhost` (padrão da Hostinger)

### Página em Branco
1. Habilite a exibição de erros temporariamente:
   - No painel Hostinger, vá em **Configurações PHP**
   - Ative `display_errors`
2. Verifique os logs de erro do PHP

### Login não Funciona
1. Verifique se a tabela `usuarios` foi criada
2. Execute no phpMyAdmin:
```sql
SELECT * FROM usuarios;
```
3. Se vazia, reimporte o arquivo `database_mysql.sql`

---

## Suporte

- **Documentação**: Ver arquivo `docs/REQUISITOS_TECNICOS.md`
- **Desenvolvimento**: https://sigh-ex--gspimenta.replit.app
- **Produção**: https://sigeex.gestaoderotinas.com.br
