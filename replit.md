# SIGEEX - Sistema de Gestão de Escalas Extraordinárias

## Overview
SIGEEX is a web-based system developed in PHP for managing extraordinary duty rosters (escalas) for correctional officers in prison units. Its core purpose is to streamline the process of staff scheduling, budget control, roster approval, and reporting. The system aims to enhance efficiency in resource allocation, ensure compliance with budgetary constraints, and provide transparency in the management of extraordinary shifts.

Key capabilities include:
- Multi-user role management (Superintendent, Director, HR, Administrative)
- Budget allocation and tracking for extraordinary scales
- Interactive roster creation and approval workflows
- Automated alerts and notifications
- Comprehensive reporting, including Excel export for HR
- Dual-database support for PostgreSQL (development) and MySQL (production) environments.

The project seeks to provide a robust and scalable solution for public security institutions, improving operational oversight and reducing manual workload associated with complex scheduling.

## User Preferences
I prefer clear, concise explanations and direct answers.
I value an iterative development approach, where changes are proposed and discussed before implementation.
Please ask for confirmation before making significant architectural changes or adding new external dependencies.
When implementing features, focus on maintainability and adherence to established design patterns.
I expect the agent to prioritize security and data integrity in all development tasks.
Please provide code examples for complex logic or new features.
Do not make changes to the `/deploy/` folder.
Do not modify the core database connection logic in `src/Config/Database.php` unless explicitly instructed.
Avoid making changes that would break the dual-database compatibility (PostgreSQL/MySQL).

## System Architecture

**Core Design Principles:**
- **MVC Pattern:** The system is structured using a custom Model-View-Controller (MVC) pattern, particularly in the PHP Pure version, to separate concerns and improve maintainability. The Laravel version leverages Laravel's inherent MVC structure.
- **Dual-Database Support:** Automatically detects and connects to PostgreSQL (development, Replit) or MySQL (production, Hostinger) based on environment variables, ensuring flexibility without code changes.
- **Role-Based Access Control (RBAC):** Implemented via middleware to restrict access to functionalities based on user roles (Superintendent, Director, RH, Administrative).

**UI/UX Decisions:**
- **Framework:** Bootstrap 5.3 for a responsive and modern interface.
- **Theming:** Clean, professional interface with distinct color coding for calendar elements (weekends, holidays, allocated days) and status alerts (e.g., green for approved, red for rejected, yellow/red for budget alerts).
- **Interactive Elements:** Chart.js for data visualization, interactive calendars for roster creation, and modal windows for user input.
- **Print Optimization:** Specific layouts for printing rosters (e.g., "Imprimir P/Mural") are designed for readability on physical displays.

**Technical Implementations:**
- **Backend:** PHP 8.x (PHP Pure version built without external frameworks, Laravel version uses Laravel 12.x).
- **Routing:** Custom `Router.php` in PHP Pure, Laravel routing in the Laravel version.
- **Session Management:** Custom `Session.php` for handling user sessions.
- **View Rendering:** Custom `View.php` for template rendering (PHP Pure), Blade templates (Laravel).
- **Authentication:** Custom `AuthController.php` (PHP Pure), Laravel's built-in authentication system (Laravel).
- **Database Migrations:** Laravel's migration system in the Laravel version.
- **Export Functionality:** HTML Table with specific MIME type for Excel (`.xls`) compatibility (Office 2003-2007+).

**Key Feature Specifications:**

1.  **Budget Management (Superintendent):**
    *   Annual budget configuration with technical reserve.
    *   Distribution of budget to units.
    *   Monthly budgetary margin control (`margin_percentual`) per unit.
    *   Dynamic redistribution of remaining budget across months.
    *   Logging of budget allocation history.

2.  **Roster Creation & Management (Director/Gestor):**
    *   Interactive monthly calendar for staff allocation.
    *   Allocation of servers to teams (A, B, C, D) and modules.
    *   Limit of 60 hours per server with conflict detection.
    *   Visual alerts for roster status (rejected, approved, pending) and budget overruns (yellow/red).
    *   Option to view "ALL TEAMS" for a consolidated roster.

3.  **Roster Approval & Execution (RH):**
    *   Approval/rejection of rosters with mandatory reasons.
    *   Marking rosters as "executed" with financial value input.
    *   Validation against monthly budget margins, triggering alerts for Superintendents and Directors.
    *   Detailed roster view with calendar.
    *   Excel export of rosters, including logos and authorization texts.

4.  **Administrative Functions:**
    *   CRUD operations for prison units, modules/sectors, and correctional officers.
    *   CSV import for officers.
    *   Comprehensive user management (create, edit, delete, reset password, activate/deactivate users) with role assignment and unit linking.

5.  **Alerts and Notifications:**
    *   Automated email notifications to Superintendents and Directors when executed scales exceed budgetary limits.
    *   Dedicated "Alerts Center" with filters for reviewing budget-related alerts (yellow/red).

## External Dependencies

-   **Backend Language:** PHP 8.x
-   **Database Systems:**
    *   PostgreSQL (for Replit development environment)
    *   MySQL (for Hostinger production environment)
-   **PHP Frameworks:**
    *   Laravel 12.x (for the new version)
-   **Frontend Libraries/Frameworks:**
    *   Bootstrap 5.3 (CSS Framework)
    *   HTML5, CSS3, JavaScript ES6+
    *   Chart.js (for data visualization)
    *   Bootstrap Icons (for iconography)
-   **Composer:** For PHP dependency management (e.g., Laravel's dependencies).

## Recent Features (January 2026)

### Sistema Global de Servidores (/servidores)
- Página acessível por todos os perfis
- Busca por matrícula e nome (sem listagem visível inicial)
- Importação CSV para administradores (Matrícula, Nome, Unidade, Cargo, Escala Extra, Status)
- Modal de alteração de status para RH/Superintendente/Admin
- Controle de inatividade: período definido (data início/fim) ou indefinido
- Motivos: Férias, Licença Médica, Licença Prêmio, Afastamento, Outro
- Reativação automática após período de inatividade expirar (comando agendado: servidores:reativar)
- Validação no Diretor: só pode alocar servidor ATIVO e APTO para escala extra

### Barras de Orçamento Mensal (Dashboard Diretor)
- Barras com 4 cores: cinza (não utilizado), verde (dentro do previsto), laranja (acima mas dentro da margem), vermelho (excedeu margem)
- Valor disponível exibido dentro da barra cinza
- Valor base mensal original exibido abaixo de cada barra