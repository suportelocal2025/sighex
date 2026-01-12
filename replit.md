# SIGEEX - Sistema de Gestão de Escalas Extraordinárias

## Visão Geral
Sistema web em PHP para gestão de escalas de servidores em unidades prisionais, com controle de orçamento, aprovação de escalas e geração de relatórios. O sistema suporta múltiplos ambientes de banco de dados (PostgreSQL para desenvolvimento no Replit e MySQL para produção na Hostinger).

**URL de Produção:** https://sigeex.gestaoderotinas.com.br  
**URL de Desenvolvimento:** https://sigh-ex--gspimenta.replit.app

## Versões Disponíveis

### 1. PHP Puro (Original)
- Localização: `/sigeex-php-puro/`
- MVC manual sem frameworks
- Sistema completo e funcional

### 2. Laravel (Nova versão)
- Localização: `/sigeex-laravel/`
- Laravel 12.x com Eloquent ORM
- Blade templates
- Middleware de autenticação e autorização
- Sistema de migrations

## Estrutura do Projeto

```
/
├── index.php                    # Ponto de entrada e rotas
├── htaccess.txt                 # Configuração Apache (renomear para .htaccess)
├── composer.json                # Dependências PHP
├── src/
│   ├── Config/
│   │   ├── Database.php         # Conexão PostgreSQL/MySQL (dual-database)
│   │   └── Schema.php           # Criação de tabelas e seed
│   ├── Controllers/
│   │   ├── AuthController.php   # Autenticação e login
│   │   ├── DashboardController.php
│   │   ├── SuperintendenteController.php
│   │   ├── DiretorController.php
│   │   ├── RhController.php
│   │   └── AdminController.php
│   └── Core/
│       ├── Router.php           # Sistema de rotas
│       ├── Session.php          # Gerenciamento de sessões
│       ├── View.php             # Renderização de views
│       └── Middleware.php       # Autenticação e autorização
├── views/
│   ├── layouts/main.php         # Layout principal
│   ├── auth/login.php
│   ├── superintendente/
│   │   ├── dashboard.php
│   │   ├── orcamento.php
│   │   ├── distribuicao.php
│   │   └── relatorios.php
│   ├── diretor/
│   │   ├── dashboard.php        # Com alertas de status das escalas
│   │   ├── escala-mensal.php    # Calendário interativo
│   │   ├── enviar-escala.php
│   │   └── servidores.php
│   ├── rh/
│   │   ├── dashboard.php
│   │   ├── detalhar-escala.php  # Com exportação Excel
│   │   ├── escalas.php
│   │   ├── relatorios.php
│   │   └── relatorio-resultado.php
│   └── administrativo/
│       ├── dashboard.php
│       ├── unidades.php
│       ├── form-unidade.php
│       └── servidores.php
├── deploy/
│   ├── database_mysql.sql       # Script SQL para MySQL/Hostinger
│   └── INSTRUCOES_DEPLOY.md     # Guia de deploy
├── config/
│   └── database.php             # Configuração alternativa
└── attached_assets/             # Arquivos anexados
```

## Arquitetura Dual-Database

O sistema detecta automaticamente o driver de banco de dados:
- **PostgreSQL**: Quando a variável `PGHOST` está definida (ambiente Replit)
- **MySQL**: Quando `PGHOST` não existe (ambiente Hostinger)

### Conexão MySQL (Hostinger)
Configurar no `src/Config/Database.php`:
```php
$host = 'localhost';
$dbname = 'u123456789_sigeex';
$user = 'u123456789_sigeex';
$password = 'SuaSenhaAqui';
```

## Papéis de Usuário

### 1. Superintendente - Visão global e gestão estratégica
- Configurar orçamento anual e reserva técnica
- Distribuir orçamento entre unidades
- Visualizar histórico de aportes por unidade (data, hora, valor anterior, valor novo)
- Visualizar dashboards consolidados
- Gerar relatórios por período

### 2. Diretor/Gestor - Gestor da unidade prisional
- Montar escalas mensais com calendário interativo
- Alocar servidores em equipes (A, B, C, D) e módulos
- Definir líderes de equipe
- Enviar escalas para aprovação
- **Alertas visuais** no dashboard: escalas rejeitadas, aprovadas e pendentes
- Visualizar horas executadas
- Imprimir escalas (P/Mural)

### 3. RH - Gestor de aprovações e execução
- Aprovar/rejeitar escalas com motivo
- Marcar escalas como executadas com valor financeiro
- Gerar relatórios de horas e valores
- **Exportar para Excel** (.xls compatível com Office 2003-2007+)
- Visualizar detalhes de escalas com calendário

### 4. Administrativo - Suporte operacional
- Cadastrar e gerenciar unidades prisionais
- Cadastrar e gerenciar módulos/raios/setores
- Cadastrar e gerenciar servidores/policiais penais
- **Gerenciar usuários do sistema** (criar, editar, excluir, resetar senha)
- Importação via CSV

## Credenciais Padrão

| Papel           | Email                     | Senha     |
|-----------------|---------------------------|-----------|
| Superintendente | super@sistema.gov.br      | admin123  |
| Diretor         | diretor@sistema.gov.br    | admin123  |
| RH              | rh@sistema.gov.br         | admin123  |
| Administrativo  | admin@sistema.gov.br      | admin123  |

## Banco de Dados

### Tabelas Principais
| Tabela | Descrição |
|--------|-----------|
| `usuarios` | Usuários do sistema com papéis |
| `unidades` | Unidades prisionais |
| `equipes` | Equipes por unidade (A, B, C, D) |
| `modulos` | Módulos/setores por unidade |
| `servidores` | Policiais penais |
| `orcamento_global` | Orçamento anual com reserva técnica |
| `distribuicao_orcamento` | Distribuição por unidade |
| `log_distribuicao` | Histórico de aportes |
| `escalas` | Escalas mensais com status |
| `alocacoes` | Alocações de servidores nas escalas |
| `escala_equipe_servidores` | Vínculo servidor-equipe |
| `horas_aprovadas` | Horas aprovadas por servidor |

### Status das Escalas
- `rascunho` - Em montagem pelo diretor
- `pendente` - Enviada para aprovação do RH
- `aprovada` - Aprovada pelo RH
- `rejeitada` - Rejeitada pelo RH (com motivo)
- `executada` - Executada com valor financeiro lançado

## Executando o Projeto

### Desenvolvimento (Replit)
```bash
php -S 0.0.0.0:5000 index.php
```

### Produção (Hostinger)
1. Fazer upload de todos os arquivos para `public_html`
2. Renomear `htaccess.txt` para `.htaccess`
3. Importar `deploy/database_mysql.sql` no phpMyAdmin
4. Configurar credenciais MySQL em `src/Config/Database.php`

## Tecnologias

- **Backend**: PHP 8.x (sem frameworks externos)
- **Banco de Dados**: PostgreSQL (Replit) / MySQL (Hostinger)
- **Frontend**: HTML5, CSS3, JavaScript ES6+
- **UI Framework**: Bootstrap 5.3
- **Ícones**: Bootstrap Icons
- **Gráficos**: Chart.js
- **Exportação**: HTML Table com MIME type Excel

## Funcionalidades Implementadas

### Autenticação e Autorização
- [x] Login com email e senha
- [x] Controle de acesso por papéis
- [x] Sessão segura com gerenciamento de estado
- [x] Logout

### Superintendente
- [x] Dashboard com visão consolidada
- [x] Configuração de orçamento anual
- [x] Definição de reserva técnica (%)
- [x] Distribuição de orçamento para unidades
- [x] Histórico de aportes com data/hora
- [x] Gráficos de gastos por unidade
- [x] Cálculo de valor disponível (total - reserva - distribuído)

### Diretor/Gestor
- [x] Dashboard com cards: Orçamento, Gasto, Disponível, **Horas Executadas**
- [x] **Alertas de status**: escalas rejeitadas, aprovadas e pendentes
- [x] Montagem de escala mensal com calendário interativo
- [x] Alocação direta por clique no dia
- [x] Cores diferenciadas: sábados (amarelo), domingos (laranja), feriados (vermelho)
- [x] Limite de 60 horas por servidor
- [x] Detecção de conflito de alocação
- [x] Seleção de servidores via modal
- [x] Definição de líder de equipe
- [x] Edição de escalas rejeitadas
- [x] Visualização de escalas aprovadas/executadas/pendentes (modo leitura)
- [x] **Opção "TODAS AS EQUIPES"** para visualização consolidada
- [x] Imprimir P/Mural - Layout otimizado para impressão
- [x] Envio para aprovação

### RH
- [x] Dashboard com lista de escalas
- [x] Filtro por status
- [x] Aprovar escalas
- [x] Rejeitar escalas com motivo obrigatório
- [x] Marcar como executada com valor financeiro
- [x] Detalhar escala com calendário
- [x] **Exportar para Excel** (.xls) com:
  - Espaços para logos (SEAP e Unidade)
  - Texto de autorização em vermelho
  - Totalizador de horas
  - Formato compatível com Office 2003-2007+

### Administrativo
- [x] Gestão de unidades prisionais
- [x] Criação automática de 4 equipes (A, B, C, D)
- [x] Gestão de módulos/raios/setores
- [x] Gestão de servidores
- [x] Importação de servidores via CSV
- [x] Ativar/desativar servidor para escala extra
- [x] **Gestão de usuários do sistema**:
  - Listar usuários com perfil e unidade vinculada
  - Criar usuário com definição de perfil
  - Vincular diretor a unidade existente
  - Editar dados de usuários
  - Resetar senha de usuário
  - Excluir usuário
  - Ativar/desativar usuário

## Fluxo de Montagem de Escala (Diretor)

1. Selecione a **Equipe** (A, B, C ou D)
2. Selecione o **Módulo/Raio** onde os servidores trabalharão
3. Clique em **"Add Servidor"** para abrir o modal de seleção
4. No modal, selecione os servidores (checkbox)
   - Servidores já vinculados a outras equipes aparecem desabilitados
5. Clique em **"Adicionar Selecionados"**
6. Marque o checkbox **"Líder"** para mesários/líderes
7. **Clique nos dias** do calendário para alocar (dia fica azul escuro)
8. Para remover alocação, clique no dia alocado e confirme
9. Para remover servidor da equipe, clique no botão X
10. Clique em **"Enviar para Aprovação"**

### Visualização de Escalas (Não Editáveis)
- Escalas aprovadas, executadas ou pendentes abrem em modo leitura
- Opção "TODAS AS EQUIPES" pré-selecionada
- Calendário carrega automaticamente com todos os servidores
- Badge colorido indica a equipe de cada servidor
- Botão "Imprimir P/Mural" disponível

## Visual e Interface

- Cards compactos e uniformes no dashboard
- Gráficos Chart.js responsivos
- Calendário com cores temáticas
- Alertas visuais para status das escalas
- Layout de impressão otimizado
- Interface responsiva (Bootstrap 5)

## Changelog Recente

### Dezembro 2025
- Adicionado card "Horas Executadas" no dashboard do diretor (substituiu "Horas Aprovadas")
- Adicionados alertas visuais para escalas rejeitadas, aprovadas e pendentes
- Implementada opção "TODAS AS EQUIPES" para visualização consolidada
- Carregamento automático do calendário para escalas não editáveis
- Badge indicando equipe de cada servidor na visualização "TODAS"
- Exportação Excel com formato compatível Office 2003-2007
- Correção do cálculo de "Valor Disponível" no dashboard do superintendente
- Suporte a visualização/impressão de escalas em qualquer status
- **Gestão de usuários** no módulo Administrativo (CRUD completo)

### Laravel Edition - Janeiro 2026
- **Aba Escalas no Superintendente**: Visualização completa de escalas com mesmo fluxo do RH (todas, pendentes, aprovadas, executadas)
- **Sistema de Margem Orçamentária Mensal**:
  - Campo `margin_percentual` na tabela `distribuicao_orcamento`
  - Configurável por unidade (padrão 10%)
  - Cálculo de orçamento mensal = anual / 12
  - Lógica de carry-forward: saldos positivos E negativos propagam para meses seguintes
- **Infográfico de 12 meses no Dashboard do Diretor**:
  - Cards visuais para cada mês do ano
  - Barras de progresso coloridas (verde/amarelo/vermelho)
  - Indicador visual de violação de margem
  - Valores de orçamento, gasto e saldo por mês
- **Alertas de Violação de Margem no Superintendente**:
  - Banner de alerta quando unidades ultrapassam limite + margem
  - Tabela detalhada com unidade, mês, limite, gasto e excedente
  - Botão "Enviar por Email" para notificação
- **Notificações por Email**:
  - Classe Mailable `MarginViolationAlert`
  - Template Markdown para emails de alerta
  - Recálculo server-side das violações (segurança)

### Laravel Edition - Dezembro 2025
- **Dashboard do Diretor responsivo**: Cards de orçamento, gasto, disponível e horas executadas agora adaptam-se corretamente em diferentes tamanhos de tela
- **Calendário Escala Mensal completo**: Implementação idêntica à versão PHP puro com:
  - Cores diferenciadas: Sábados (amarelo), Domingos (laranja), Feriados (vermelho), Dias alocados (azul escuro)
  - Seleção de equipe e módulo
  - Modal para adicionar servidores à equipe
  - Clique nos dias do calendário para alocar/desalocar
  - Legenda visual de cores
  - Cálculo automático de horas por servidor
  - Limite de 60 horas por servidor
- **Gestão de Setores/Módulos/Raios**: Modal para criar e botão para excluir módulos na tela de edição de unidade
- **Migration de alocacoes**: Novas colunas (equipe_id, modulo_id, dia, horas_abono, is_lider) para suportar funcionalidade completa
