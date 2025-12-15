<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title><?= htmlspecialchars($titulo ?? 'Sistema de Escalas') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-color: #1a4480;
            --secondary-color: #005ea2;
            --success-color: #00a91c;
            --warning-color: #ffbe2e;
            --danger-color: #d54309;
        }
        body {
            background-color: #f0f4f8;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .sidebar {
            background: linear-gradient(180deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            min-height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            width: 250px;
            padding-top: 1rem;
            z-index: 1000;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.85);
            padding: 0.8rem 1.5rem;
            margin: 0.2rem 0.5rem;
            border-radius: 8px;
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background-color: rgba(255,255,255,0.15);
            color: #fff;
        }
        .sidebar .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        .main-content {
            margin-left: 250px;
            padding: 2rem;
        }
        .top-bar {
            background: #fff;
            padding: 1rem 2rem;
            margin: -2rem -2rem 2rem -2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 25px rgba(0,0,0,0.12);
        }
        .stat-card {
            padding: 1rem;
            height: 100%;
        }
        .stat-card .stat-icon {
            width: 45px;
            height: 45px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
        }
        .stat-card .stat-value {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--primary-color);
            white-space: nowrap;
        }
        .stat-card .stat-label {
            color: #6c757d;
            font-size: 0.75rem;
        }
        .stat-card.stat-card-colored .stat-value {
            color: inherit;
        }
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        .table-responsive {
            border-radius: 12px;
            overflow: hidden;
        }
        .table {
            margin-bottom: 0;
        }
        .table thead th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 500;
            border: none;
            padding: 1rem;
        }
        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
        }
        .badge-status {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 500;
        }
        .logo-container {
            padding: 1rem 1.5rem;
            margin-bottom: 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .logo-container h5 {
            color: white;
            margin: 0;
            font-weight: 600;
        }
        .logo-container small {
            color: rgba(255,255,255,0.7);
        }
        .user-info {
            padding: 1rem 1.5rem;
            margin-top: auto;
            border-top: 1px solid rgba(255,255,255,0.1);
            color: white;
        }
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }
        @media print {
            .sidebar, .top-bar, .no-print { display: none !important; }
            .main-content { margin-left: 0 !important; }
        }
    </style>
</head>
<body>
    <?php 
    use App\Core\Session;
    $user = Session::getUser();
    $papel = $user['papel'] ?? '';
    ?>
    
    <div class="sidebar d-flex flex-column">
        <div class="logo-container">
            <h5><i class="bi bi-shield-check"></i> SGEEX</h5>
            <small>Sistema de Gestão de Escalas</small>
        </div>
        
        <nav class="nav flex-column">
            <a class="nav-link" href="/"><i class="bi bi-speedometer2"></i> Dashboard</a>
            
            <?php if ($papel === 'superintendente'): ?>
                <a class="nav-link" href="/superintendente/orcamento"><i class="bi bi-wallet2"></i> Orçamento</a>
                <a class="nav-link" href="/superintendente/distribuicao"><i class="bi bi-diagram-3"></i> Distribuição</a>
                <a class="nav-link" href="/superintendente/relatorios"><i class="bi bi-file-earmark-bar-graph"></i> Relatórios</a>
            <?php endif; ?>
            
            <?php if ($papel === 'diretor'): ?>
                <a class="nav-link" href="/diretor/escala-mensal"><i class="bi bi-calendar3"></i> Escala Mensal</a>
                <a class="nav-link" href="/diretor/enviar-escala"><i class="bi bi-send"></i> Enviar Escala</a>
                <a class="nav-link" href="/diretor/servidores"><i class="bi bi-people"></i> Servidores</a>
            <?php endif; ?>
            
            <?php if ($papel === 'rh'): ?>
                <a class="nav-link" href="/rh/escalas"><i class="bi bi-list-check"></i> Escalas</a>
                <a class="nav-link" href="/rh/relatorios"><i class="bi bi-file-earmark-bar-graph"></i> Relatórios</a>
            <?php endif; ?>
            
            <?php if ($papel === 'administrativo'): ?>
                <a class="nav-link" href="/admin/unidades"><i class="bi bi-building"></i> Unidades</a>
                <a class="nav-link" href="/admin/servidores"><i class="bi bi-person-badge"></i> Servidores</a>
                <a class="nav-link" href="/admin/usuarios"><i class="bi bi-people"></i> Usuários</a>
            <?php endif; ?>
        </nav>
        
        <div class="user-info mt-auto">
            <div class="d-flex align-items-center">
                <div class="me-3">
                    <i class="bi bi-person-circle" style="font-size: 2rem;"></i>
                </div>
                <div>
                    <div class="fw-bold"><?= htmlspecialchars($user['nome'] ?? '') ?></div>
                    <small class="text-white-50"><?= ucfirst($papel) ?></small>
                </div>
            </div>
            <a href="/logout" class="btn btn-outline-light btn-sm mt-3 w-100">
                <i class="bi bi-box-arrow-right"></i> Sair
            </a>
        </div>
    </div>
    
    <div class="main-content">
        <div class="top-bar">
            <h4 class="mb-0"><?= htmlspecialchars($titulo ?? 'Dashboard') ?></h4>
            <div>
                <span class="text-muted"><?= date('d/m/Y H:i') ?></span>
            </div>
        </div>
        
        <div class="toast-container">
            <?php if ($success = Session::getFlash('success')): ?>
                <div class="toast show align-items-center text-white bg-success border-0" role="alert">
                    <div class="d-flex">
                        <div class="toast-body"><i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($success) ?></div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>
            <?php endif; ?>
            <?php if ($error = Session::getFlash('error')): ?>
                <div class="toast show align-items-center text-white bg-danger border-0" role="alert">
                    <div class="d-flex">
                        <div class="toast-body"><i class="bi bi-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?></div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <?= $content ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('.toast').forEach(toast => {
            setTimeout(() => {
                toast.classList.remove('show');
            }, 5000);
        });
        
        document.querySelectorAll('.sidebar .nav-link').forEach(link => {
            if (link.href === window.location.href) {
                link.classList.add('active');
            }
        });
    </script>
</body>
</html>
