<?php $__env->startSection('title', 'RH - Dashboard'); ?>
<?php $__env->startSection('header', 'Dashboard do RH'); ?>

<?php $__env->startSection('sidebar'); ?>
    <a href="/rh" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="/rh/escalas" class="nav-link"><i class="bi bi-calendar-check"></i> Escalas</a>
    <a href="/rh/servidores" class="nav-link"><i class="bi bi-people"></i> Servidores</a>
    <a href="/rh/relatorios" class="nav-link"><i class="bi bi-file-earmark-bar-graph"></i> Relatórios</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<style>
    .stat-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        padding: 1.25rem;
        height: 100%;
        display: flex;
        align-items: center;
        gap: 1rem;
        min-width: 0;
    }
    .stat-icon {
        width: 48px;
        height: 48px;
        min-width: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }
    .stat-icon.warning { background: #fff3cd; color: #856404; }
    .stat-icon.success { background: #d1f2eb; color: #1e8449; }
    .stat-icon.primary { background: #e8f4fd; color: #2980b9; }
    .stat-icon.danger { background: #fadbd8; color: #c0392b; }
    .stat-content {
        min-width: 0;
        overflow: hidden;
    }
    .stat-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        color: #6c757d;
        font-weight: 500;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .stat-value {
        font-size: 1.75rem;
        font-weight: 700;
        color: #212529;
        line-height: 1.2;
    }
    @media (max-width: 991.98px) {
        .stat-card { padding: 1rem; gap: 0.75rem; }
        .stat-icon { width: 40px; height: 40px; min-width: 40px; font-size: 1rem; }
        .stat-value { font-size: 1.5rem; }
    }
    @media (max-width: 575.98px) {
        .stat-card { padding: 0.875rem; }
        .stat-value { font-size: 1.25rem; }
        .stat-label { font-size: 0.7rem; }
    }
</style>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon warning">
                <i class="bi bi-hourglass-split"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Pendentes</div>
                <div class="stat-value"><?php echo e($estatisticas['pendentes']); ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon success">
                <i class="bi bi-check-circle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Aprovadas</div>
                <div class="stat-value"><?php echo e($estatisticas['aprovadas']); ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon primary">
                <i class="bi bi-check-all"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Executadas</div>
                <div class="stat-value"><?php echo e($estatisticas['executadas']); ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon danger">
                <i class="bi bi-x-circle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Rejeitadas</div>
                <div class="stat-value"><?php echo e($estatisticas['rejeitadas']); ?></div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-0">
        <h5 class="mb-0 fw-semibold"><i class="bi bi-list-check me-2 text-primary"></i>Escalas Recentes</h5>
        <a href="/rh/escalas" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-arrow-right me-1"></i>Ver Todas
        </a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="border-0">Unidade</th>
                    <th class="border-0">Mês/Ano</th>
                    <th class="border-0 text-center">Horas</th>
                    <th class="border-0 text-center">Status</th>
                    <th class="border-0 text-center">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    $meses = ['', 'Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
                ?>
                <?php $__empty_1 = true; $__currentLoopData = $escalas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $escala): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $statusClass = match($escala->status) {
                        'pendente' => 'bg-light text-secondary',
                        'aprovada' => 'bg-success-subtle text-success',
                        'executada' => 'bg-primary-subtle text-primary',
                        'rejeitada' => 'bg-danger-subtle text-danger',
                        default => 'bg-secondary-subtle text-secondary'
                    };
                ?>
                <tr>
                    <td><strong><?php echo e($escala->unidade->nome ?? 'N/A'); ?></strong></td>
                    <td class="text-muted"><?php echo e($meses[$escala->mes]); ?>/<?php echo e($escala->ano); ?></td>
                    <td class="text-center"><?php echo e(number_format($escala->total_horas, 0, ',', '.')); ?>h</td>
                    <td class="text-center">
                        <span class="badge <?php echo e($statusClass); ?> fw-normal px-3 py-2">
                            <?php echo e(ucfirst($escala->status)); ?>

                        </span>
                    </td>
                    <td class="text-center">
                        <a href="/rh/escala/<?php echo e($escala->id); ?>" class="btn btn-sm btn-light border">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="5" class="text-center py-4 text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                        Nenhuma escala encontrada
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/runner/workspace/sigeex-laravel/resources/views/rh/dashboard.blade.php ENDPATH**/ ?>