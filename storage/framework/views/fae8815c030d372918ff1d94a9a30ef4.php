<?php $__env->startSection('title', 'Diretor - Dashboard'); ?>
<?php $__env->startSection('header', 'Dashboard do Diretor'); ?>

<?php $__env->startSection('sidebar'); ?>
    <a href="/diretor" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="/diretor/escala-mensal" class="nav-link"><i class="bi bi-calendar3"></i> Escala Mensal</a>
    <a href="/diretor/servidores" class="nav-link"><i class="bi bi-people"></i> Servidores</a>
    <a href="/diretor/alertas" class="nav-link"><i class="bi bi-bell"></i> Alertas 
        <?php $totalAlertas = $alertasMargemVermelho->count() + $alertasMargemAmarelo->count() + $escalasRejeitadas + $alertasPrazo->count(); ?>
        <?php if($totalAlertas > 0): ?><span class="badge bg-danger"><?php echo e($totalAlertas); ?></span><?php endif; ?>
    </a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row g-3 mb-4">
    <?php $totalAlertas = $alertasMargemVermelho->count() + $alertasMargemAmarelo->count() + $escalasRejeitadas + $alertasPrazo->count(); ?>
    <div class="col-lg-3 col-md-6 col-sm-6">
        <a href="/diretor/alertas" class="text-decoration-none">
            <div class="card card-stat h-100 <?php echo e($totalAlertas > 0 ? 'border-danger border-2' : ''); ?>">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="bg-danger bg-opacity-10 rounded-3 p-2 me-2 flex-shrink-0">
                                <i class="bi bi-bell text-danger"></i>
                            </div>
                            <div class="min-width-0">
                                <h6 class="text-muted mb-0 small">Alertas</h6>
                                <h4 class="mb-0"><?php echo e($totalAlertas); ?></h4>
                            </div>
                        </div>
                        <?php if($alertasPrazo->count() > 0): ?>
                        <span class="badge bg-primary rounded-pill"><?php echo e($alertasPrazo->count()); ?></span>
                        <?php endif; ?>
                        <?php if($alertasMargemVermelho->count() > 0): ?>
                        <span class="badge bg-danger rounded-pill"><?php echo e($alertasMargemVermelho->count()); ?></span>
                        <?php endif; ?>
                        <?php if($alertasMargemAmarelo->count() > 0): ?>
                        <span class="badge bg-warning text-dark rounded-pill"><?php echo e($alertasMargemAmarelo->count()); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="card card-stat h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 rounded-3 p-2 me-2 flex-shrink-0">
                        <i class="bi bi-wallet2 text-primary"></i>
                    </div>
                    <div class="min-width-0">
                        <h6 class="text-muted mb-0 small">Orçamento</h6>
                        <h4 class="mb-0 text-truncate">R$ <?php echo e(number_format($orcamento, 0, ',', '.')); ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="card card-stat h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div class="bg-danger bg-opacity-10 rounded-3 p-2 me-2 flex-shrink-0">
                        <i class="bi bi-graph-down text-danger"></i>
                    </div>
                    <div class="min-width-0">
                        <h6 class="text-muted mb-0 small">Gasto</h6>
                        <h4 class="mb-0 text-truncate">R$ <?php echo e(number_format($gasto, 0, ',', '.')); ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="card card-stat h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div class="bg-success bg-opacity-10 rounded-3 p-2 me-2 flex-shrink-0">
                        <i class="bi bi-cash-coin text-success"></i>
                    </div>
                    <div class="min-width-0">
                        <h6 class="text-muted mb-0 small">Disponível</h6>
                        <h4 class="mb-0 text-truncate">R$ <?php echo e(number_format($disponivel, 0, ',', '.')); ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="card card-stat h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div class="bg-info bg-opacity-10 rounded-3 p-2 me-2 flex-shrink-0">
                        <i class="bi bi-clock-history text-info"></i>
                    </div>
                    <div class="min-width-0">
                        <h6 class="text-muted mb-0 small">Horas Exec.</h6>
                        <h4 class="mb-0 text-truncate"><?php echo e(number_format($horasExecutadas, 0, ',', '.')); ?>h</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="bi bi-bar-chart-line me-2"></i>Orçamento Mensal <?php echo e($ano); ?> <small class="text-muted">(Margem: <?php echo e(number_format($marginPercentual, 0)); ?>%)</small></h5>
    </div>
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-end" style="height: 200px; gap: 4px;">
            <?php
                $alturaMaxima = 180;
                $maxValor = 0;
                foreach($mesesInfo as $info) {
                    $maxValor = max($maxValor, $info['limite'], $info['gasto'], $info['orcamento']);
                }
                $maxValor = $maxValor * 1.1;
            ?>
            <?php $__currentLoopData = $mesesInfo; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m => $info): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $orcMes = $info['orcamento'];
                $gastoMes = $info['gasto'];
                $limiteMes = $info['limite'];
                $margemValor = $limiteMes - $orcMes;
                
                if ($gastoMes <= 0) {
                    $alturaVerde = 0;
                    $alturaLaranja = 0;
                    $alturaVermelho = 0;
                    $alturaCinza = $maxValor > 0 ? ($orcMes / $maxValor) * $alturaMaxima : 0;
                } elseif ($gastoMes <= $orcMes) {
                    $alturaVerde = $maxValor > 0 ? ($gastoMes / $maxValor) * $alturaMaxima : 0;
                    $alturaLaranja = 0;
                    $alturaVermelho = 0;
                    $alturaCinza = $maxValor > 0 ? (($orcMes - $gastoMes) / $maxValor) * $alturaMaxima : 0;
                } elseif ($gastoMes <= $limiteMes) {
                    $alturaVerde = $maxValor > 0 ? ($orcMes / $maxValor) * $alturaMaxima : 0;
                    $alturaLaranja = $maxValor > 0 ? (($gastoMes - $orcMes) / $maxValor) * $alturaMaxima : 0;
                    $alturaVermelho = 0;
                    $alturaCinza = 0;
                } else {
                    $alturaVerde = $maxValor > 0 ? ($orcMes / $maxValor) * $alturaMaxima : 0;
                    $alturaLaranja = $maxValor > 0 ? ($margemValor / $maxValor) * $alturaMaxima : 0;
                    $alturaVermelho = $maxValor > 0 ? (($gastoMes - $limiteMes) / $maxValor) * $alturaMaxima : 0;
                    $alturaCinza = 0;
                }
                
                $alturaTotal = $alturaVerde + $alturaLaranja + $alturaVermelho + $alturaCinza;
            ?>
            <div class="text-center flex-fill" style="min-width: 0;">
                <div class="position-relative mx-auto" style="width: 100%; max-width: 60px; height: <?php echo e($alturaMaxima); ?>px;">
                    <?php if($alturaVerde > 0): ?>
                    <div class="position-absolute start-0 end-0" 
                         style="bottom: 0; height: <?php echo e($alturaVerde); ?>px; background-color: #28a745; border-radius: 0 0 0 0;"
                         title="Gasto: R$ <?php echo e(number_format(min($gastoMes, $orcMes), 0, ',', '.')); ?>">
                    </div>
                    <?php endif; ?>
                    <?php if($alturaCinza > 0): ?>
                    <div class="position-absolute start-0 end-0 d-flex align-items-center justify-content-center" 
                         style="bottom: <?php echo e($alturaVerde); ?>px; height: <?php echo e($alturaCinza); ?>px; background-color: #e9ecef; border-radius: 4px 4px 0 0; overflow: hidden;"
                         title="Disponível: R$ <?php echo e(number_format($orcMes - max($gastoMes, 0), 0, ',', '.')); ?>">
                        <?php if($alturaCinza >= 30): ?>
                        <span class="fw-bold text-dark" style="font-size: 14px; writing-mode: vertical-rl; transform: rotate(180deg); white-space: nowrap;">
                            <?php echo e(number_format(($orcMes - max($gastoMes, 0))/1000, 1)); ?>k
                        </span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    <?php if($alturaLaranja > 0): ?>
                    <div class="position-absolute start-0 end-0" 
                         style="bottom: <?php echo e($alturaVerde); ?>px; height: <?php echo e($alturaLaranja); ?>px; background-color: #fd7e14; border-radius: <?php echo e($alturaVermelho == 0 ? '4px 4px' : '0 0'); ?> 0 0;"
                         title="Acima do previsto (dentro da margem): R$ <?php echo e(number_format($gastoMes <= $limiteMes ? $gastoMes - $orcMes : $margemValor, 0, ',', '.')); ?>">
                    </div>
                    <?php endif; ?>
                    <?php if($alturaVermelho > 0): ?>
                    <div class="position-absolute start-0 end-0" 
                         style="bottom: <?php echo e($alturaVerde + $alturaLaranja); ?>px; height: <?php echo e($alturaVermelho); ?>px; background-color: #dc3545; border-radius: 4px 4px 0 0;"
                         title="Excedeu a margem: R$ <?php echo e(number_format($gastoMes - $limiteMes, 0, ',', '.')); ?>">
                    </div>
                    <?php endif; ?>
                    <?php if($info['ultrapassouMargem']): ?>
                    <div class="position-absolute" style="top: -12px; left: 50%; transform: translateX(-50%);">
                        <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size: 0.7rem;"></i>
                    </div>
                    <?php endif; ?>
                </div>
                <?php if($info['mesAtual']): ?>
                <div class="mx-auto" style="width: 6px; height: 6px; margin-top: 2px;">
                    <div class="bg-primary rounded-circle" style="width: 6px; height: 6px;"></div>
                </div>
                <?php endif; ?>
                <div class="mt-1 <?php echo e($info['mesAtual'] ? 'fw-bold text-primary' : ''); ?>" style="font-size: 0.7rem;"><?php echo e($info['nome']); ?></div>
                <div class="text-muted" style="font-size: 0.55rem;"><?php echo e(number_format($orcamentoMensalBase/1000, 1)); ?>k</div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <div class="mt-3 d-flex flex-wrap justify-content-center gap-3">
            <div class="d-flex align-items-center">
                <div class="rounded me-2" style="width: 12px; height: 12px; background-color: #e9ecef;"></div>
                <small class="text-muted">Não utilizado</small>
            </div>
            <div class="d-flex align-items-center">
                <div class="rounded me-2" style="width: 12px; height: 12px; background-color: #28a745;"></div>
                <small class="text-muted">Dentro do previsto</small>
            </div>
            <div class="d-flex align-items-center">
                <div class="rounded me-2" style="width: 12px; height: 12px; background-color: #fd7e14;"></div>
                <small class="text-muted">Acima (dentro da margem)</small>
            </div>
            <div class="d-flex align-items-center">
                <div class="rounded me-2" style="width: 12px; height: 12px; background-color: #dc3545;"></div>
                <small class="text-muted">Excedeu a margem</small>
            </div>
        </div>
        <div class="mt-2 text-center">
            <small class="text-muted">Base mensal: R$ <?php echo e(number_format($orcamentoMensalBase, 0, ',', '.')); ?> | Total anual: R$ <?php echo e(number_format($orcamento, 0, ',', '.')); ?> | Margem: <?php echo e(number_format($marginPercentual, 0)); ?>%</small>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-calendar3 me-2"></i>Escalas <?php echo e($ano); ?></h5>
        <a href="/diretor/escala-mensal" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nova Escala
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Mês</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $escalas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $escala): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e(str_pad($escala->mes, 2, '0', STR_PAD_LEFT)); ?>/<?php echo e($escala->ano); ?></td>
                        <td>
                            <?php switch($escala->status):
                                case ('rascunho'): ?>
                                    <span class="badge bg-secondary">Rascunho</span>
                                    <?php break; ?>
                                <?php case ('pendente'): ?>
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
                        <td>
                            <a href="/diretor/escala-mensal?mes=<?php echo e($escala->mes); ?>&ano=<?php echo e($escala->ano); ?>" 
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> Ver
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="3" class="text-center text-muted">Nenhuma escala encontrada</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/runner/workspace/sigeex-laravel/resources/views/diretor/dashboard.blade.php ENDPATH**/ ?>