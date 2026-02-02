<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>SIGEEX - <?php echo $__env->yieldContent('title', 'Sistema de Gestão de Escalas'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 250px;
        }
        body {
            min-height: 100vh;
        }
        .sidebar {
            width: var(--sidebar-width);
            min-height: 100vh;
            background: linear-gradient(180deg, #1a237e 0%, #283593 100%);
            position: fixed;
            left: 0;
            top: 0;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            border-radius: 8px;
            margin: 4px 10px;
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: rgba(255,255,255,0.15);
            color: #fff;
        }
        .sidebar .nav-link i {
            width: 24px;
        }
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            background: #f5f5f5;
        }
        .navbar-top {
            background: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .card {
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border-radius: 12px;
        }
        .card-stat {
            transition: transform 0.2s;
        }
        .card-stat:hover {
            transform: translateY(-4px);
        }
        .card-stat .card-body h4 {
            font-size: 1.1rem;
            word-break: break-word;
        }
        @media (max-width: 1200px) {
            .card-stat .card-body h4 {
                font-size: 1rem;
            }
        }
        @media (max-width: 992px) {
            .card-stat .card-body h4 {
                font-size: 0.9rem;
            }
            .card-stat .card-body h6 {
                font-size: 0.75rem;
            }
        }
        .brand-logo {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .brand-logo h4 {
            color: #fff;
            margin: 0;
            font-weight: 700;
        }
        .user-info {
            padding: 15px 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
            color: rgba(255,255,255,0.8);
            font-size: 0.9rem;
        }
    </style>
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body>
    <aside class="sidebar d-flex flex-column">
        <div class="brand-logo">
            <h4><i class="bi bi-calendar-check"></i> SIGEEX</h4>
            <small class="text-white-50">Laravel Edition</small>
        </div>
        
        <nav class="nav flex-column flex-grow-1 py-3">
            <?php echo $__env->yieldContent('sidebar'); ?>
        </nav>
        
        <div class="user-info">
            <div class="d-flex align-items-center">
                <i class="bi bi-person-circle fs-4 me-2"></i>
                <div>
                    <div class="fw-semibold"><?php echo e(Auth::user()->nome); ?></div>
                    <small class="text-capitalize"><?php echo e(Auth::user()->papel); ?></small>
                </div>
            </div>
            <a href="/perfil" class="btn btn-sm btn-outline-light w-100 mt-2">
                <i class="bi bi-person-gear"></i> Meu Perfil
            </a>
            <form action="<?php echo e(route('logout')); ?>" method="POST" class="mt-2">
                <?php echo csrf_field(); ?>
                <button type="submit" class="btn btn-sm btn-outline-light w-100">
                    <i class="bi bi-box-arrow-left"></i> Sair
                </button>
            </form>
        </div>
    </aside>

    <main class="main-content">
        <nav class="navbar navbar-top px-4 py-3">
            <h5 class="mb-0"><?php echo $__env->yieldContent('header', 'Dashboard'); ?></h5>
            <span class="text-muted"><?php echo e(now()->format('d/m/Y H:i')); ?></span>
        </nav>

        <div class="container-fluid p-4">
            <?php if(session('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo e(session('success')); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if($errors->any()): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php echo $__env->yieldContent('content'); ?>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const currentPath = window.location.pathname;
            document.querySelectorAll('.sidebar .nav-link').forEach(link => {
                if (link.getAttribute('href') === currentPath) {
                    link.classList.add('active');
                }
            });
        });
    </script>
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH /home/runner/workspace/sigeex-laravel/resources/views/layouts/app.blade.php ENDPATH**/ ?>