# Tutorial: Deploy do SIGEEX Laravel na Hostinger

## Informacoes do Projeto
- **Subdominio:** sigeex-laravel.gestaoderotinas.com.br
- **Diretorio no servidor:** public_html/sigeex-laravel (ou diretorio do subdominio)

---

## PASSO 1: Preparar o Banco de Dados MySQL

### 1.1 Acessar phpMyAdmin
1. Acesse o painel da Hostinger (hPanel)
2. Va em **Databases > phpMyAdmin**
3. Selecione o banco de dados criado para o SIGEEX Laravel

### 1.2 Importar as Tabelas
1. No phpMyAdmin, clique em **Import**
2. Selecione o arquivo `database_mysql.sql` (incluido nesta pasta deploy)
3. Clique em **Go** para executar

### 1.3 Anotar as Credenciais do Banco
Anote as seguintes informacoes (disponiveis no hPanel > Databases):
- Host: localhost (geralmente)
- Nome do banco: (ex: u123456789_sigeex_laravel)
- Usuario: (ex: u123456789_sigeex)
- Senha: (a senha que voce definiu)

---

## PASSO 2: Preparar os Arquivos Localmente

### 2.1 Baixar o Projeto
Baixe todos os arquivos da pasta `sigeex-laravel/` do Replit.

### 2.2 Instalar Dependencias (no seu computador)
Se tiver PHP e Composer instalados localmente:
```bash
cd sigeex-laravel
composer install --optimize-autoloader --no-dev
```

Se nao tiver Composer local, pode fazer isso via SSH na Hostinger (Passo 4).

### 2.3 Configurar o .env para Producao
1. Copie o arquivo `.env.production` para `.env`
2. Edite com as credenciais do seu banco:

```env
APP_NAME=SIGEEX
APP_ENV=production
APP_KEY=base64:GERAR_NOVA_CHAVE
APP_DEBUG=false
APP_URL=https://sigeex-laravel.gestaoderotinas.com.br

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=seu_banco_aqui
DB_USERNAME=seu_usuario_aqui
DB_PASSWORD=sua_senha_aqui
```

---

## PASSO 3: Upload dos Arquivos

### 3.1 Acessar o Gerenciador de Arquivos
1. No hPanel, va em **Files > File Manager**
2. Navegue ate o diretorio do subdominio (geralmente `public_html/sigeex-laravel` ou pasta especifica do subdominio)

### 3.2 Estrutura de Arquivos
**IMPORTANTE:** Na Hostinger, a pasta `public` do Laravel deve apontar para o diretorio web.

#### Opcao A: Subdominio com diretorio proprio
Se o subdominio aponta para uma pasta especifica (ex: `domains/sigeex-laravel.gestaoderotinas.com.br/public_html`):

1. Faca upload de TODOS os arquivos do Laravel para essa pasta
2. Mova o conteudo da pasta `public/` para a raiz do subdominio
3. Edite o `index.php` movido para ajustar os caminhos

#### Opcao B: Usar .htaccess para redirecionar (Recomendado)
1. Faca upload de toda a pasta `sigeex-laravel` para `public_html/`
2. Configure o subdominio para apontar para `public_html/sigeex-laravel/public`

### 3.3 Upload via File Manager ou FTP
1. Compacte a pasta `sigeex-laravel` em um arquivo ZIP
2. Faca upload do ZIP
3. Extraia no servidor
4. Delete o arquivo ZIP

---

## PASSO 4: Configuracoes no Servidor

### 4.1 Acessar via SSH (Recomendado)
1. No hPanel, va em **Advanced > SSH Access**
2. Conecte-se via terminal ou PuTTY

### 4.2 Navegar ate o Projeto
```bash
cd public_html/sigeex-laravel
```

### 4.3 Instalar Dependencias (se nao fez localmente)
```bash
composer install --optimize-autoloader --no-dev
```

### 4.4 Gerar Chave da Aplicacao
```bash
php artisan key:generate
```

### 4.5 Configurar Permissoes
```bash
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

### 4.6 Limpar e Otimizar Cache
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 4.7 Executar Migrations (Alternativa ao SQL)
Se preferir usar migrations ao inves de importar SQL:
```bash
php artisan migrate --force
php artisan db:seed --force
```

---

## PASSO 5: Configurar o Subdominio

### 5.1 Apontar o Subdominio para a pasta public
1. No hPanel, va em **Domains > Subdomains**
2. Encontre `sigeex-laravel.gestaoderotinas.com.br`
3. Edite o **Document Root** para: `public_html/sigeex-laravel/public`

### 5.2 Configurar SSL (HTTPS)
1. Va em **Security > SSL**
2. Instale o certificado SSL gratuito para o subdominio
3. Ative o redirecionamento HTTPS

---

## PASSO 6: Verificar Funcionamento

### 6.1 Testar o Acesso
Acesse: https://sigeex-laravel.gestaoderotinas.com.br/login

### 6.2 Credenciais Padrao
| Papel | Email | Senha |
|-------|-------|-------|
| Superintendente | super@sistema.gov.br | admin123 |
| Diretor | diretor@sistema.gov.br | admin123 |
| RH | rh@sistema.gov.br | admin123 |
| Administrativo | admin@sistema.gov.br | admin123 |

### 6.3 Verificar Erros
Se houver erros, verifique:
```bash
tail -f storage/logs/laravel.log
```

---

## Solucao de Problemas

### Erro 500 - Internal Server Error
1. Verifique permissoes das pastas `storage` e `bootstrap/cache`
2. Verifique se o arquivo `.env` existe e esta configurado
3. Execute: `php artisan config:clear`

### Pagina em Branco
1. Ative debug temporariamente: `APP_DEBUG=true` no `.env`
2. Verifique o log: `storage/logs/laravel.log`

### Erro de Conexao com Banco
1. Verifique as credenciais no `.env`
2. Confirme que o banco existe no phpMyAdmin
3. Teste a conexao: `php artisan tinker` depois `DB::connection()->getPdo()`

### Rotas nao Funcionam (404)
1. Verifique se o `.htaccess` esta na pasta `public`
2. Confirme que mod_rewrite esta ativo
3. Execute: `php artisan route:clear`

---

## Checklist Final

- [ ] Banco de dados criado e tabelas importadas
- [ ] Arquivos enviados para o servidor
- [ ] Arquivo `.env` configurado com credenciais corretas
- [ ] Chave da aplicacao gerada (`php artisan key:generate`)
- [ ] Permissoes configuradas (storage e bootstrap/cache)
- [ ] Cache otimizado (config:cache, route:cache, view:cache)
- [ ] Subdominio apontando para pasta `public`
- [ ] SSL/HTTPS configurado
- [ ] Login testado com credenciais padrao

---

## Arquivos Incluidos nesta Pasta

1. `TUTORIAL_DEPLOY_HOSTINGER.md` - Este tutorial
2. `database_mysql.sql` - Script SQL para criar tabelas no MySQL
3. `.env.production` - Modelo de arquivo .env para producao
4. `.htaccess` - Arquivo de configuracao Apache (ja incluido em public/)
