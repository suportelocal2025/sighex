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
| Diretor         | diretor@sistema.gov.br    | admin123  |
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
- [x] Montagem de escala mensal com calendário interativo
  - **Alocação direta por clique**: clique no dia para alocar imediatamente
  - Visualização de todos os dias do mês (1-30/31)
  - Cores diferenciadas: sábados (amarelo claro), domingos (laranja claro), feriados (vermelho claro)
  - Limite de 60 horas por servidor
  - Detecção de conflito quando servidor já está alocado
  - Opção de mover servidor para novo local
  - Edição de escalas rejeitadas com botão "Editar e Corrigir"
- [x] Envio de escala para aprovação
- [x] Dashboard do RH
- [x] Aprovação/Rejeição de escalas
- [x] Marcar escala como executada
- [x] Gestão de unidades com módulos/raios/setores
- [x] Gestão de servidores
- [x] Importação via CSV
- [x] Relatórios com exportação

## Fluxo de Montagem de Escala (Diretor)

1. **Selecione a Equipe** (A, B, C ou D)
2. **Selecione o Módulo/Raio** onde os servidores trabalharão
3. Clique em **"Add Servidor"** para abrir o modal de seleção
4. No modal, selecione os servidores que farão parte da equipe
   - Servidores já vinculados a outras equipes aparecem desabilitados
   - Clique nos servidores desejados e depois em "Adicionar Selecionados"
5. Os servidores selecionados aparecem no calendário
6. Para cada servidor, marque o checkbox **"Líder"** se ele for mesário/líder de equipe
7. **Clique nos dias** do calendário para alocar (dia fica azul escuro)
8. Para remover uma alocação, clique no dia já alocado e confirme
9. Para remover um servidor da equipe, clique no botão X ao lado do nome
10. Ao finalizar, clique em **"Enviar para Aprovação"**

### Tabela de Vínculo Servidor-Equipe
- `escala_equipe_servidores` - Vincula servidores às equipes por escala
- Um servidor só pode estar em uma equipe por escala (validação de conflito)
- O checkbox "Líder" é por servidor/equipe e aplica a todas as alocações
