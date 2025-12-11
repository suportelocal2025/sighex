# SIGEEX - Documento de Requisitos Técnicos
**Sistema de Gestão de Escalas Extraordinárias**

**Versão:** 2.0  
**Data:** Dezembro 2025  
**Status:** Em Produção

---

## 1. Introdução

### 1.1 Propósito
Este documento especifica os requisitos funcionais e não funcionais do SIGEEX, um sistema web para gestão de escalas extraordinárias de servidores em unidades prisionais.

### 1.2 Escopo
O sistema abrange:
- Gestão de orçamento anual e distribuição para unidades
- Montagem de escalas mensais com alocação de servidores
- Fluxo de aprovação/rejeição de escalas
- Controle de execução financeira
- Geração de relatórios e exportação de dados

### 1.3 Definições e Acrônimos
| Termo | Definição |
|-------|-----------|
| SIGEEX | Sistema de Gestão de Escalas Extraordinárias |
| SEAP | Secretaria de Estado de Administração Penitenciária |
| Servidor | Policial penal que realiza escala extraordinária |
| Escala | Distribuição mensal de servidores em dias/horários |
| Módulo | Setor/raio dentro de uma unidade prisional |
| Equipe | Grupo de trabalho (A, B, C, D) |

---

## 2. Requisitos Funcionais

### 2.1 Requisitos Gerais do Sistema

| ID | Requisito | Prioridade |
|----|-----------|------------|
| RF-G-01 | O sistema deve fornecer uma interface web responsiva para acesso via navegadores de desktop | Alta |
| RF-G-02 | O sistema deve implementar um mecanismo de autenticação seguro baseado em email e senha | Alta |
| RF-G-03 | O acesso às funcionalidades deve ser estritamente controlado por papéis (Roles) | Alta |
| RF-G-04 | Todas as operações de criação, edição e exclusão de dados críticos devem ser persistidas | Alta |
| RF-G-05 | O sistema deve suportar múltiplos drivers de banco de dados (PostgreSQL e MySQL) | Média |

### 2.2 Módulo de Autenticação (Login)

| ID | Requisito | Status |
|----|-----------|--------|
| RF-L-01 | Deve haver campos para email e senha | ✅ Implementado |
| RF-L-02 | Deve haver um botão "Entrar" para submeter as credenciais | ✅ Implementado |
| RF-L-03 | O sistema deve validar as credenciais contra a base de dados | ✅ Implementado |
| RF-L-04 | Em caso de sucesso, redirecionar para o dashboard respectivo | ✅ Implementado |
| RF-L-05 | Em caso de falha, exibir mensagem de erro clara | ✅ Implementado |

### 2.3 Módulo Superintendente

#### 2.3.1 Dashboard
| ID | Requisito | Status |
|----|-----------|--------|
| RF-S-OV-01 | Exibir cards com: Orçamento Total, Reserva Técnica, Disponível, Repassados, Gastos, Total Unidades | ✅ Implementado |
| RF-S-OV-02 | Gráfico de barras: Gasto (R$) e Horas Executadas por unidade | ✅ Implementado |
| RF-S-OV-03 | Gráfico de pizza: proporção de gastos entre unidades | ✅ Implementado |
| RF-S-OV-04 | Tabela com status detalhado de cada unidade | ✅ Implementado |
| RF-S-OV-05 | Filtros por Mês, Trimestre e Ano | 🔄 Parcial |

#### 2.3.2 Orçamento
| ID | Requisito | Status |
|----|-----------|--------|
| RF-S-B-01 | Permitir inserção do Valor Total Anual | ✅ Implementado |
| RF-S-B-02 | Permitir definição do Percentual de Reserva Técnica (0-100%) | ✅ Implementado |
| RF-S-B-03 | Calcular e exibir Valor Disponível para distribuição | ✅ Implementado |
| RF-S-B-04 | Salvar configurações e notificar com toast | ✅ Implementado |

#### 2.3.3 Distribuição
| ID | Requisito | Status |
|----|-----------|--------|
| RF-S-D-01 | Listar unidades com campo para valor a distribuir | ✅ Implementado |
| RF-S-D-02 | Exibir total distribuído e saldo restante | ✅ Implementado |
| RF-S-D-03 | Validar que distribuição não exceda disponível | ✅ Implementado |
| RF-S-D-04 | Salvar log com data/hora da distribuição | ✅ Implementado |

#### 2.3.4 Relatórios
| ID | Requisito | Status |
|----|-----------|--------|
| RF-S-R-01 | Gerar relatórios de Gastos por Unidade | ✅ Implementado |
| RF-S-R-02 | Exportar relatórios em PDF e Excel | 🔄 Parcial |

### 2.4 Módulo Diretor/Gestor

#### 2.4.1 Dashboard
| ID | Requisito | Status |
|----|-----------|--------|
| RF-D-OV-01 | Exibir cards: Orçamento Anual, Total Gasto, Disponível, Horas Executadas | ✅ Implementado |
| RF-D-OV-02 | Exibir alerta visual se uso do orçamento > 80% | ✅ Implementado |
| RF-D-OV-03 | Gráfico de barras: Gasto e Saldo mês a mês | ✅ Implementado |
| RF-D-OV-04 | Alertas de status das escalas (rejeitadas, aprovadas, pendentes) | ✅ Implementado |

#### 2.4.2 Escala Mensal
| ID | Requisito | Status |
|----|-----------|--------|
| RF-D-MS-01 | Interface de calendário/tabela para alocar servidores | ✅ Implementado |
| RF-D-MS-02 | Escala organizada por Módulo/Setor e Equipe | ✅ Implementado |
| RF-D-MS-03 | Permitir adicionar/remover servidores de equipe | ✅ Implementado |
| RF-D-MS-04 | Detectar conflitos de alocação | ✅ Implementado |
| RF-D-MS-05 | Permitir designação de Líder de Equipe | ✅ Implementado |
| RF-D-MS-06 | Calcular total de horas por servidor em tempo real | ✅ Implementado |
| RF-D-MS-07 | Salvar progresso da montagem da escala | ✅ Implementado |
| RF-D-MS-08 | Gerar visualização para impressão (P/Mural) | ✅ Implementado |
| RF-D-MS-09 | Opção "TODAS AS EQUIPES" para visualização consolidada | ✅ Implementado |
| RF-D-MS-10 | Carregamento automático para escalas não editáveis | ✅ Implementado |

#### 2.4.3 Enviar Escala
| ID | Requisito | Status |
|----|-----------|--------|
| RF-D-ES-01 | Exibir resumo de horas por equipe e módulo | ✅ Implementado |
| RF-D-ES-02 | Exibir total de horas do mês | ✅ Implementado |
| RF-D-ES-03 | Botão "Enviar para Aprovação" com status "pendente" | ✅ Implementado |
| RF-D-ES-04 | Exibir status atual da submissão | ✅ Implementado |
| RF-D-ES-05 | Se rejeitada, exibir motivo do RH | ✅ Implementado |
| RF-D-ES-06 | Bloquear envio se já houver escala pendente/aprovada | ✅ Implementado |

#### 2.4.4 Servidores
| ID | Requisito | Status |
|----|-----------|--------|
| RF-D-S-01 | Listar servidores da unidade com nome e matrícula | ✅ Implementado |

### 2.5 Módulo RH

#### 2.5.1 Escalas
| ID | Requisito | Status |
|----|-----------|--------|
| RF-RH-S-01 | Listar submissões com Unidade, Mês, Total Horas, Data Envio, Status | ✅ Implementado |
| RF-RH-S-02 | Filtrar escalas por status | ✅ Implementado |
| RF-RH-S-03 | Botão "Aprovar" com mudança de status | ✅ Implementado |
| RF-RH-S-04 | Botão "Rejeitar" com motivo obrigatório | ✅ Implementado |
| RF-RH-S-05 | Botão "Marcar como Executada" com valor financeiro | ✅ Implementado |
| RF-RH-S-06 | Botão "Detalhar" com visualização completa | ✅ Implementado |

#### 2.5.2 Relatórios
| ID | Requisito | Status |
|----|-----------|--------|
| RF-RH-R-01 | Relatório de Escala Aprovada por servidor/unidade | ✅ Implementado |
| RF-RH-R-02 | Relatório de Escala Executada com valores (R$) | ✅ Implementado |
| RF-RH-R-03 | Exportação para Excel (.xls) com formatação | ✅ Implementado |

### 2.6 Módulo Administrativo

#### 2.6.1 Unidades
| ID | Requisito | Status |
|----|-----------|--------|
| RF-A-U-01 | Listar unidades com detalhes | ✅ Implementado |
| RF-A-U-02 | Formulário para cadastrar unidade e diretor | ✅ Implementado |
| RF-A-U-03 | Criar 4 equipes padrão (A, B, C, D) automaticamente | ✅ Implementado |
| RF-A-U-04 | Adicionar/remover Módulos/Setores | ✅ Implementado |
| RF-A-U-05 | Editar e excluir unidades | ✅ Implementado |

#### 2.6.2 Servidores
| ID | Requisito | Status |
|----|-----------|--------|
| RF-A-S-01 | Listar servidores com Nome, Matrícula, Lotação, Status Extra | ✅ Implementado |
| RF-A-S-02 | Formulário para cadastrar servidor | ✅ Implementado |
| RF-A-S-03 | Importação via CSV | ✅ Implementado |
| RF-A-S-04 | Ativar/desativar servidor para escala extra | ✅ Implementado |

---

## 3. Requisitos Não Funcionais

### 3.1 Usabilidade

| ID | Requisito | Métrica |
|----|-----------|---------|
| RNF-U-01 | Interface intuitiva com fluxos claros | Feedback visual imediato (toasts) |
| RNF-U-02 | Layout responsivo para desktop | Bootstrap 5 grid system |
| RNF-U-03 | Calendário com cores diferenciadas | Sábados, domingos, feriados |
| RNF-U-04 | Alertas visuais para status críticos | Cards coloridos no dashboard |

### 3.2 Performance

| ID | Requisito | Métrica |
|----|-----------|---------|
| RNF-P-01 | Telas principais devem carregar em < 3 segundos | Tempo de resposta |
| RNF-P-02 | Operações AJAX sem bloqueio de interface | Async/await |
| RNF-P-03 | Índices de banco otimizados | Consultas principais < 100ms |

### 3.3 Segurança

| ID | Requisito | Implementação |
|----|-----------|---------------|
| RNF-S-01 | Senhas armazenadas com hash bcrypt | password_hash() PHP |
| RNF-S-02 | Acesso não autenticado bloqueado | Middleware de sessão |
| RNF-S-03 | Controle de acesso por papel | Session::getPapel() |
| RNF-S-04 | Proteção contra SQL Injection | PDO prepared statements |
| RNF-S-05 | HTTPS obrigatório em produção | Configuração servidor |

### 3.4 Compatibilidade

| ID | Requisito | Suporte |
|----|-----------|---------|
| RNF-C-01 | Navegadores modernos | Chrome, Firefox, Safari, Edge |
| RNF-C-02 | Banco de dados dual | PostgreSQL (dev), MySQL (prod) |
| RNF-C-03 | PHP 8.x | PHP 8.0+ |
| RNF-C-04 | Exportação Excel | Office 2003-2007+ (.xls) |

### 3.5 Manutenibilidade

| ID | Requisito | Implementação |
|----|-----------|---------------|
| RNF-M-01 | Arquitetura MVC | Controllers, Views, Models |
| RNF-M-02 | Código documentado | PHPDoc, comentários |
| RNF-M-03 | Separação de responsabilidades | Classes especializadas |
| RNF-M-04 | Configuração externalizada | src/Config/ |

### 3.6 Disponibilidade

| ID | Requisito | Meta |
|----|-----------|------|
| RNF-D-01 | Uptime em produção | 99.5% |
| RNF-D-02 | Backup automático do banco | Via Hostinger |
| RNF-D-03 | Recuperação de desastres | Script SQL de recriação |

---

## 4. Arquitetura Técnica

### 4.1 Stack Tecnológico

| Camada | Tecnologia |
|--------|------------|
| Backend | PHP 8.x (vanilla, sem framework) |
| Frontend | HTML5, CSS3, JavaScript ES6+ |
| UI Framework | Bootstrap 5.3 |
| Gráficos | Chart.js |
| Ícones | Bootstrap Icons |
| Banco de Dados | PostgreSQL / MySQL |

### 4.2 Estrutura de Diretórios

```
/
├── index.php              # Entry point e roteamento
├── src/
│   ├── Config/            # Configurações (Database, Schema)
│   ├── Controllers/       # Lógica de negócio
│   └── Core/              # Classes base (Router, Session, View)
├── views/                 # Templates PHP/HTML
│   ├── layouts/           # Layout principal
│   ├── auth/              # Telas de autenticação
│   ├── superintendente/   # Views do superintendente
│   ├── diretor/           # Views do diretor
│   ├── rh/                # Views do RH
│   └── administrativo/    # Views administrativas
└── deploy/                # Scripts de deploy
```

### 4.3 Modelo de Dados

```
usuarios (1) ─── (N) unidades
unidades (1) ─┬─ (N) equipes
              ├─ (N) modulos
              ├─ (N) servidores
              └─ (N) escalas
escalas (1) ──┬─ (N) alocacoes
              └─ (N) escala_equipe_servidores
```

### 4.4 Fluxo de Status das Escalas

```
┌──────────┐     ┌──────────┐     ┌──────────┐     ┌──────────┐
│ Rascunho │ ──► │ Pendente │ ──► │ Aprovada │ ──► │Executada │
└──────────┘     └────┬─────┘     └──────────┘     └──────────┘
                      │
                      ▼
                ┌──────────┐
                │Rejeitada │ ──► (volta para edição)
                └──────────┘
```

---

## 5. Requisitos de Infraestrutura

### 5.1 Ambiente de Desenvolvimento (Replit)

| Recurso | Especificação |
|---------|---------------|
| Servidor Web | PHP built-in server (porta 5000) |
| Banco de Dados | PostgreSQL (Neon-backed) |
| Variáveis | DATABASE_URL, PGHOST, PGUSER, PGPASSWORD |

### 5.2 Ambiente de Produção (Hostinger)

| Recurso | Especificação |
|---------|---------------|
| Servidor Web | Apache 2.4 com mod_rewrite |
| PHP | 8.0+ |
| Banco de Dados | MySQL 8.0 |
| SSL | Certificado Let's Encrypt |
| Domínio | sigeex.gestaoderotinas.com.br |

---

## 6. Matriz de Rastreabilidade

| Requisito | Arquivo(s) Implementação |
|-----------|--------------------------|
| RF-L-01 a RF-L-05 | AuthController.php, login.php |
| RF-S-* | SuperintendenteController.php, superintendente/*.php |
| RF-D-* | DiretorController.php, DashboardController.php, diretor/*.php |
| RF-RH-* | RhController.php, rh/*.php |
| RF-A-* | AdminController.php, administrativo/*.php |
| RNF-S-* | Middleware.php, Session.php, Database.php |

---

## 7. Histórico de Versões

| Versão | Data | Alterações |
|--------|------|------------|
| 1.0 | Nov/2025 | Versão inicial |
| 1.5 | Dez/2025 | Adicionado suporte MySQL, exportação Excel |
| 2.0 | Dez/2025 | Alertas de status, "TODAS AS EQUIPES", horas executadas |

---

**Documento elaborado por:** Equipe de Desenvolvimento SIGEEX  
**Aprovado por:** [Pendente aprovação formal]
