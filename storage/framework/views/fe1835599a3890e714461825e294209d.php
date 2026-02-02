<?php $__env->startSection('title', 'RH - Escalas'); ?>
<?php $__env->startSection('header', 'Gestão de Escalas'); ?>

<?php $__env->startSection('sidebar'); ?>
    <a href="/rh" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="/rh/escalas" class="nav-link active"><i class="bi bi-calendar-check"></i> Escalas</a>
    <a href="/rh/servidores" class="nav-link"><i class="bi bi-people"></i> Servidores</a>
    <a href="/rh/relatorios" class="nav-link"><i class="bi bi-file-earmark-bar-graph"></i> Relatórios</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="/rh/escalas" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label small text-muted">Ano</label>
                <select name="ano" class="form-select form-select-sm">
                    <?php $__currentLoopData = $anos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($a); ?>" <?php echo e($ano == $a ? 'selected' : ''); ?>><?php echo e($a); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Mês</label>
                <select name="mes" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <?php
                        $nomesMeses = ['', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
                    ?>
                    <?php for($m = 1; $m <= 12; $m++): ?>
                        <option value="<?php echo e($m); ?>" <?php echo e($mes == $m ? 'selected' : ''); ?>><?php echo e($nomesMeses[$m]); ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Unidade</label>
                <select name="unidade_id" class="form-select form-select-sm">
                    <option value="">Todas</option>
                    <?php $__currentLoopData = $unidades; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($u->id); ?>" <?php echo e($unidadeId == $u->id ? 'selected' : ''); ?>><?php echo e($u->nome); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="todos" <?php echo e($status === 'todos' ? 'selected' : ''); ?>>Todas</option>
                    <option value="pendente" <?php echo e($status === 'pendente' ? 'selected' : ''); ?>>Pendentes</option>
                    <option value="aprovada" <?php echo e($status === 'aprovada' ? 'selected' : ''); ?>>Aprovadas</option>
                    <option value="executada" <?php echo e($status === 'executada' ? 'selected' : ''); ?>>Executadas</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                    <i class="bi bi-funnel me-1"></i> Filtrar
                </button>
                <a href="/rh/escalas/exportar-excel?ano=<?php echo e($ano); ?>&mes=<?php echo e($mes); ?>&unidade_id=<?php echo e($unidadeId); ?>&status=<?php echo e($status); ?>" class="btn btn-success btn-sm" title="Exportar Excel">
                    <i class="bi bi-file-earmark-excel"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white">
        <div class="row align-items-center">
            <div class="col">
                <h5 class="mb-0">
                    <i class="bi bi-calendar-check me-2"></i>Escalas
                    <?php if($mes): ?>
                        - <?php echo e($nomesMeses[$mes]); ?>/<?php echo e($ano); ?>

                    <?php else: ?>
                        - <?php echo e($ano); ?>

                    <?php endif; ?>
                </h5>
            </div>
            <div class="col-auto">
                <span class="badge bg-secondary"><?php echo e($escalas->count()); ?> escala(s)</span>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Unidade</th>
                        <th>Mês/Ano</th>
                        <th>Status</th>
                        <th>Data Envio</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $escalas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $escala): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($escala->unidade->nome ?? 'N/A'); ?></td>
                        <td><?php echo e(str_pad($escala->mes, 2, '0', STR_PAD_LEFT)); ?>/<?php echo e($escala->ano); ?></td>
                        <td>
                            <?php switch($escala->status):
                                case ('pendente'): ?>
                                    <span class="badge bg-warning text-dark">Pendente</span>
                                    <?php break; ?>
                                <?php case ('aprovada'): ?>
                                    <span class="badge bg-success">Aprovada</span>
                                    <?php break; ?>
                                <?php case ('rejeitada'): ?>
                                    <span class="badge bg-danger">Rejeitada</span>
                                    <?php break; ?>
                                <?php case ('executada'): ?>
                                    <span class="badge bg-info">Executada</span>
                                    <?php break; ?>
                            <?php endswitch; ?>
                        </td>
                        <td><?php echo e($escala->data_envio ? $escala->data_envio->format('d/m/Y H:i') : '-'); ?></td>
                        <td>
                            <a href="/rh/escala/<?php echo e($escala->id); ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> Detalhar
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted">Nenhuma escala encontrada com os filtros selecionados</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/rh/escalas.blade.php ENDPATH**/ ?>