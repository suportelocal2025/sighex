# SGEEX - Sistema de Gestão de Escalas Extraordinárias

## Visão Geral
Sistema web em PHP para gestão de escalas de servidores em unidades prisionais, com controle de orçamento, aprovação de escalas e geração de relatórios.

## Estrutura do Projeto

```
/
├── index.php              # Ponto de entrada e rotas
├── src/
│   ├── Config/
│   │   ├── Database.php   # Conexão PostgreSQL
│   │   └── Schema.php     # Criação de tabelas e seed
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   ├── SuperintendenteController.php
│   │   ├── DiretorController.php
│   │   ├── RhController.php
│   │   └── AdminController.php
│   └── Core/
│       ├── Router.php     # Sistema de rotas
│       ├── Session.php    # Gerenciamento de sessões
│       ├── View.php       # Renderização de views
│       └── Middleware.php # Autenticação e autorização
├── views/
│   ├── layouts/main.php   # Layout principal
│   ├── auth/login.php
│   ├── superintendente/
│   ├── diretor/
│   ├── rh/
│   └── administrativo/
└── composer.json
```

## Papéis de Usuário

1. **Superintendente** - Visão global e gestão estratégica
   - Configurar orçamento anual
   - Distribuir orçamento entre unidades
   - Visualizar dashboards consolidados

2. **Diretor** - Gestor da unidade prisional
   - Montar escalas mensais
   - Alocar servidores em equipes
   - Enviar escalas para aprovação

3. **RH** - Gestor de aprovações e execução
   - Aprovar/rejeitar escalas
   - Marcar escalas como executadas
   - Gerar relatórios

4. **Administrativo** - Suporte operacional
   - Cadastrar unidades
   - Cadastrar servidores

## Credenciais Padrão

| Papel           | Email                     | Senha     |
|-----------------|---------------------------|-----------|
| Superintendente | super@sistema.gov.br      | admin123  |
| RH              | rh@sistema.gov.br         | admin123  |
| Administrativo  | admin@sistema.gov.br      | admin123  |

## Banco de Dados

- PostgreSQL via variáveis de ambiente (DATABASE_URL, PGHOST, etc.)
- Tabelas criadas automaticamente no primeiro acesso

### Tabelas Principais
- `usuarios` - Usuários do sistema
- `unidades` - Unidades prisionais
- `equipes` - Equipes por unidade (A, B, C, D)
- `modulos` - Módulos/setores por unidade
- `servidores` - Policiais penais
- `orcamento_global` - Orçamento anual
- `distribuicao_orcamento` - Distribuição por unidade
- `escalas` - Escalas mensais
- `alocacoes` - Alocações de servidores nas escalas
- `horas_aprovadas` - Horas aprovadas por servidor

## Executando o Projeto

O servidor PHP está configurado para rodar na porta 5000:
```bash
php -S 0.0.0.0:5000 index.php
```

## Tecnologias

- PHP 8.4
- PostgreSQL
- Bootstrap 5.3
- Chart.js (gráficos)
- Bootstrap Icons

## Funcionalidades Implementadas

- [x] Autenticação com papéis
- [x] Dashboard do Superintendente
- [x] Configuração de orçamento
- [x] Distribuição de orçamento
- [x] Dashboard do Diretor
- [x] Montagem de escala mensal
- [x] Envio de escala para aprovação
- [x] Dashboard do RH
- [x] Aprovação/Rejeição de escalas
- [x] Marcar escala como executada
- [x] Gestão de unidades
- [x] Gestão de servidores
- [x] Importação via CSV
- [x] Relatórios com exportação
