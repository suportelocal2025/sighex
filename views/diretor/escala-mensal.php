<?php
$meses = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 
          'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
$mesNome = $meses[$mes - 1];

$alocacoesPorServidor = [];
foreach ($alocacoes as $a) {
    if (!isset($alocacoesPorServidor[$a['servidor_id']])) {
        $alocacoesPorServidor[$a['servidor_id']] = [];
    }
    $alocacoesPorServidor[$a['servidor_id']][$a['dia']] = $a;
}

$podeEditar = in_array($escala['status'], ['rascunho', 'rejeitada']);
?>

<style>
.calendario-container {
    max-height: 400px;
    overflow-y: auto;
    overflow-x: auto;
}
.dia-cell {
    width: 32px;
    height: 32px;
    min-width: 32px;
    border: 1px solid #e5e7eb;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    cursor: pointer;
    transition: all 0.15s ease;
    position: relative;
    background-color: #fff;
    color: #374151;
}
.dia-cell:hover {
    transform: scale(1.05);
    z-index: 10;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.dia-cell.dia-semana {
    background-color: #fff;
}
.dia-cell.sabado {
    background-color: #fef9c3;
    border-color: #fde047;
    color: #854d0e;
}
.dia-cell.domingo {
    background-color: #fed7aa;
    border-color: #fdba74;
    color: #9a3412;
}
.dia-cell.feriado {
    background-color: #fecaca;
    border-color: #fca5a5;
    color: #991b1b;
}
.dia-cell.selecionado {
    background-color: #3b82f6 !important;
    color: white !important;
    border-color: #2563eb !important;
    font-weight: 600;
}
.dia-cell.alocado {
    background-color: #1e3a5f !important;
    color: white !important;
    border-color: #1e3a5f !important;
}
.servidor-row {
    transition: background-color 0.15s ease;
}
.servidor-row:hover {
    background-color: #f8fafc;
}
.servidor-row.selecionado {
    background-color: #eff6ff;
}
.horas-badge {
    font-size: 0.7rem;
    min-width: 40px;
}
.legenda-item {
    display: inline-flex;
    align-items: center;
    margin-right: 12px;
    font-size: 0.8rem;
    color: #6b7280;
}
.legenda-cor {
    width: 16px;
    height: 16px;
    border-radius: 3px;
    margin-right: 4px;
}
.servidor-info {
    min-width: 180px;
    max-width: 180px;
}
.dias-container {
    display: flex;
    gap: 2px;
    flex-wrap: nowrap;
}
.form-alocacao {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 1.25rem;
}
.form-alocacao .form-label {
    color: #475569;
    font-weight: 500;
    font-size: 0.85rem;
}
.card-stat {
    border: none;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
}
.table-calendario th {
    font-size: 0.75rem;
    padding: 0.4rem 0.2rem;
}
.table-calendario td {
    padding: 0.25rem;
}
</style>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm p-3">
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <div>
                    <label class="form-label mb-0 small text-muted">Mês</label>
                    <select class="form-select form-select-sm" id="selectMes" onchange="alterarPeriodo()">
                        <?php foreach ($meses as $i => $m): ?>
                            <option value="<?= $i + 1 ?>" <?= ($i + 1) == $mes ? 'selected' : '' ?>><?= $m ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label mb-0 small text-muted">Ano</label>
                    <select class="form-select form-select-sm" id="selectAno" onchange="alterarPeriodo()">
                        <?php for ($y = date('Y') - 1; $y <= date('Y') + 1; $y++): ?>
                            <option value="<?= $y ?>" <?= $y == $ano ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="ms-auto d-flex gap-2 align-items-center">
                    <?php 
                    $statusBadge = match($escala['status']) {
                        'rascunho' => 'bg-secondary-subtle text-secondary',
                        'pendente' => 'bg-warning-subtle text-warning',
                        'aprovada' => 'bg-success-subtle text-success',
                        'executada' => 'bg-primary-subtle text-primary',
                        'rejeitada' => 'bg-danger-subtle text-danger',
                        default => 'bg-secondary-subtle text-secondary'
                    };
                    ?>
                    <span class="badge <?= $statusBadge ?> fs-6 px-3 py-2 fw-normal">
                        <?= ucfirst($escala['status']) ?>
                    </span>
                    <?php if ($escala['status'] == 'rejeitada'): ?>
                    <a href="/diretor/escala/reabrir?mes=<?= $mes ?>&ano=<?= $ano ?>" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-pencil me-1"></i> Editar
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3 h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted">Total de Horas</small>
                    <h4 class="mb-0 fw-bold" id="totalHorasGeral"><?= number_format($escala['total_horas'], 0, ',', '.') ?>h</h4>
                </div>
                <div class="rounded-circle bg-light p-2">
                    <i class="bi bi-clock-history text-primary fs-5"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3 h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted">Servidores Alocados</small>
                    <h4 class="mb-0 fw-bold"><?= count($alocacoesPorServidor) ?></h4>
                </div>
                <div class="rounded-circle bg-light p-2">
                    <i class="bi bi-people-fill text-success fs-5"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($escala['status'] == 'rejeitada'): ?>
<div class="alert alert-danger border-0 mb-4 d-flex align-items-center justify-content-between">
    <div>
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <strong>Escala Rejeitada:</strong> <?= htmlspecialchars($escala['motivo_rejeicao']) ?>
    </div>
    <a href="/diretor/escala/reabrir?mes=<?= $mes ?>&ano=<?= $ano ?>" class="btn btn-danger btn-sm">
        <i class="bi bi-pencil me-1"></i> Editar e Corrigir
    </a>
</div>
<?php endif; ?>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-2">
        <div class="d-flex flex-wrap gap-3 align-items-center">
            <strong class="text-muted small me-2"><i class="bi bi-info-circle me-1"></i> Legenda:</strong>
            <span class="legenda-item"><span class="legenda-cor" style="background:#fff; border:1px solid #e5e7eb;"></span> Dia útil</span>
            <span class="legenda-item"><span class="legenda-cor" style="background:#fef9c3; border:1px solid #fde047;"></span> Sábado</span>
            <span class="legenda-item"><span class="legenda-cor" style="background:#fed7aa; border:1px solid #fdba74;"></span> Domingo</span>
            <span class="legenda-item"><span class="legenda-cor" style="background:#fecaca; border:1px solid #fca5a5;"></span> Feriado</span>
            <span class="legenda-item"><span class="legenda-cor" style="background:#1e3a5f;"></span> Alocado</span>
        </div>
    </div>
</div>

<?php if ($podeEditar): ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body form-alocacao">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 fw-semibold text-dark"><i class="bi bi-hand-index me-2"></i>Montar Escala - Clique nos dias para alocar</h6>
            <span class="badge bg-info-subtle text-info border">
                <i class="bi bi-lightbulb me-1"></i> Selecione Equipe e Módulo, depois clique nos dias do calendário
            </span>
        </div>
        
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Equipe <span class="text-danger">*</span></label>
                <select name="equipe_id" id="equipeSelect" class="form-select form-select-sm" required>
                    <option value="">Selecione a equipe...</option>
                    <?php foreach ($equipes as $e): ?>
                        <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Módulo/Raio <span class="text-danger">*</span></label>
                <select name="modulo_id" id="moduloSelect" class="form-select form-select-sm" required>
                    <option value="">Selecione o módulo...</option>
                    <?php foreach ($modulos as $m): ?>
                        <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Horas por dia</label>
                <input type="number" name="horas" id="horasInput" class="form-control form-control-sm" min="1" max="24" step="1" value="12">
            </div>
            <div class="col-md-2">
                <label class="form-label">Abono (h)</label>
                <input type="number" name="horas_abono" class="form-control form-control-sm" min="0" max="24" step="1" value="0">
            </div>
            <div class="col-md-2">
                <div class="form-check">
                    <input type="checkbox" name="is_lider" value="1" class="form-check-input" id="checkLider">
                    <label class="form-check-label" for="checkLider">Marcar como Líder</label>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-2 border-0">
        <h6 class="mb-0 fw-semibold"><i class="bi bi-calendar3 me-2"></i>Calendário de <?= $mesNome ?>/<?= $ano ?></h6>
        <div>
            <button class="btn btn-outline-secondary btn-sm me-2" onclick="imprimirEscala()">
                <i class="bi bi-printer me-1"></i> Imprimir
            </button>
            <?php if ($podeEditar && count($alocacoes) > 0): ?>
            <a href="/diretor/enviar-escala?mes=<?= $mes ?>&ano=<?= $ano ?>" class="btn btn-success btn-sm">
                <i class="bi bi-send me-1"></i> <?= $escala['status'] == 'rejeitada' ? 'Re-Enviar para Aprovação' : 'Enviar para Aprovação' ?>
            </a>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="calendario-container">
        <table class="table table-sm table-calendario mb-0" id="tabelaCalendario">
            <thead class="sticky-top bg-white">
                <tr>
                    <th class="servidor-info bg-light text-muted">Servidor</th>
                    <th class="text-center bg-light text-muted" style="min-width:45px;">Horas</th>
                    <?php for ($d = 1; $d <= $diasNoMes; $d++): 
                        $info = $diasInfo[$d];
                        $classeHeader = 'bg-white';
                        if ($info['isFeriado']) $classeHeader = 'bg-red-50';
                        elseif ($info['diaSemana'] == 0) $classeHeader = 'bg-orange-50';
                        elseif ($info['diaSemana'] == 6) $classeHeader = 'bg-yellow-50';
                    ?>
                        <th class="text-center text-muted" style="min-width:34px; <?= $info['isFeriado'] ? 'background:#fef2f2;' : ($info['diaSemana'] == 0 ? 'background:#fff7ed;' : ($info['diaSemana'] == 6 ? 'background:#fefce8;' : '')) ?>" 
                            title="<?= $info['isFeriado'] ? $info['nomeFeriado'] : '' ?>">
                            <small class="d-block" style="font-size:0.65rem;"><?= $info['nomeDia'] ?></small>
                            <?= $d ?>
                        </th>
                    <?php endfor; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($servidores)): ?>
                <tr>
                    <td colspan="<?= $diasNoMes + 2 ?>" class="text-center py-4 text-muted">
                        <i class="bi bi-person-x fs-1 d-block mb-2 opacity-50"></i>
                        Nenhum servidor disponível para alocação
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($servidores as $s): 
                    $horasServidor = $horasMap[$s['id']] ?? 0;
                    $alocacoesServidor = $alocacoesPorServidor[$s['id']] ?? [];
                ?>
                <tr class="servidor-row" data-servidor-id="<?= $s['id'] ?>">
                    <td class="servidor-info">
                        <div class="fw-medium text-truncate small" title="<?= htmlspecialchars($s['nome']) ?>">
                            <?= htmlspecialchars($s['nome']) ?>
                        </div>
                        <small class="text-muted" style="font-size:0.7rem;"><?= $s['matricula'] ?></small>
                    </td>
                    <td class="text-center">
                        <span class="badge horas-badge <?= $horasServidor >= 60 ? 'bg-danger' : ($horasServidor >= 48 ? 'bg-warning text-dark' : 'bg-secondary') ?>" 
                              id="horas-<?= $s['id'] ?>">
                            <?= number_format($horasServidor, 0) ?>h
                        </span>
                    </td>
                    <?php for ($d = 1; $d <= $diasNoMes; $d++): 
                        $info = $diasInfo[$d];
                        $alocacao = $alocacoesServidor[$d] ?? null;
                        
                        $classeBase = 'dia-semana';
                        if ($info['isFeriado']) $classeBase = 'feriado';
                        elseif ($info['diaSemana'] == 0) $classeBase = 'domingo';
                        elseif ($info['diaSemana'] == 6) $classeBase = 'sabado';
                        
                        if ($alocacao) $classeBase = 'alocado';
                    ?>
                        <td class="p-1">
                            <div class="dia-cell <?= $classeBase ?>" 
                                 data-dia="<?= $d ?>" 
                                 data-servidor="<?= $s['id'] ?>"
                                 data-alocado="<?= $alocacao ? '1' : '0' ?>"
                                 data-alocacao-id="<?= $alocacao['id'] ?? '' ?>"
                                 data-equipe="<?= $alocacao['equipe_id'] ?? '' ?>"
                                 data-modulo="<?= $alocacao['modulo_id'] ?? '' ?>"
                                 data-horas="<?= $alocacao['horas'] ?? '' ?>"
                                 title="<?= $alocacao ? 'Alocado: ' . number_format($alocacao['horas'], 0) . 'h' : ($info['isFeriado'] ? $info['nomeFeriado'] : $info['nomeDia'] . ', dia ' . $d) ?>"
                                 onclick="<?= $podeEditar ? 'toggleDia(this)' : '' ?>">
                                <?= $d ?>
                                <?php if ($alocacao && $alocacao['is_lider']): ?>
                                    <i class="bi bi-star-fill position-absolute" style="font-size:0.45rem; top:1px; right:1px; color:#fbbf24;"></i>
                                <?php endif; ?>
                            </div>
                        </td>
                    <?php endfor; ?>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modalConflito" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning-subtle border-0">
                <h6 class="modal-title fw-semibold"><i class="bi bi-exclamation-triangle me-2"></i>Servidor já alocado</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">O servidor já está alocado em outro local para os seguintes dias:</p>
                <div id="conflitosList" class="mb-3"></div>
                <p class="mb-0">O que deseja fazer?</p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning btn-sm" onclick="confirmarMover()">
                    <i class="bi bi-arrow-right-circle me-1"></i> Mover para novo local
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const escalaId = <?= $escala['id'] ?>;
const limiteHoras = <?= $limiteHoras ?>;
const podeEditar = <?= $podeEditar ? 'true' : 'false' ?>;
let diasSelecionados = new Set();
let servidorSelecionado = null;
let conflitosAtuais = [];

const horasIniciais = {
    <?php foreach ($horasMap as $sid => $h): ?>
    <?= $sid ?>: <?= $h ?>,
    <?php endforeach; ?>
};

function alterarPeriodo() {
    const mes = document.getElementById('selectMes').value;
    const ano = document.getElementById('selectAno').value;
    window.location.href = `/diretor/escala-mensal?mes=${mes}&ano=${ano}`;
}

async function toggleDia(el) {
    if (!podeEditar) return;
    
    const dia = parseInt(el.dataset.dia);
    const servidorId = parseInt(el.dataset.servidor);
    const alocado = el.dataset.alocado === '1';
    
    // Se já está alocado, remove
    if (alocado) {
        if (confirm('Remover alocação deste dia?')) {
            await removerAlocacaoDia(servidorId, dia);
        }
        return;
    }
    
    // Verifica se os campos obrigatórios estão preenchidos
    const equipe = document.getElementById('equipeSelect')?.value;
    const modulo = document.getElementById('moduloSelect')?.value;
    const horas = document.getElementById('horasInput')?.value || 12;
    const abono = document.querySelector('[name="horas_abono"]')?.value || 0;
    const isLider = document.querySelector('[name="is_lider"]')?.checked ? 1 : 0;
    
    if (!equipe || equipe === '') {
        alert('Selecione uma Equipe antes de clicar no dia.');
        return;
    }
    
    if (!modulo || modulo === '') {
        alert('Selecione um Módulo/Raio antes de clicar no dia.');
        return;
    }
    
    // Aloca imediatamente
    el.style.opacity = '0.5';
    el.style.pointerEvents = 'none';
    
    const form = new FormData();
    form.append('escala_id', escalaId);
    form.append('servidor_id', servidorId);
    form.append('equipe_id', equipe);
    form.append('modulo_id', modulo);
    form.append('horas', horas);
    form.append('horas_abono', abono);
    form.append('is_lider', isLider);
    form.append('dias', dia.toString());
    
    try {
        const response = await fetch('/diretor/escala/salvar-alocacao', {
            method: 'POST',
            body: form
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Atualiza visualmente como alocado
            el.dataset.alocado = '1';
            el.classList.remove('selecionado');
            el.classList.add('alocado');
            el.style.background = '#1e3a5f';
            el.style.color = '#fff';
            el.style.borderColor = '#1e3a5f';
            el.title = 'Alocado: ' + horas + 'h';
            
            // Atualiza horas do servidor
            if (horasIniciais[servidorId] !== undefined) {
                horasIniciais[servidorId] += parseFloat(horas) + parseFloat(abono);
            }
            atualizarHorasServidor(servidorId);
            
        } else if (result.conflito) {
            conflitosAtuais = result.conflitos;
            pendingAlocacao = { servidorId, dia, equipe, modulo, horas, abono, isLider, el };
            mostrarModalConflito(result.conflitos);
        } else {
            alert(result.message || 'Erro ao alocar');
        }
    } catch (error) {
        alert('Erro de conexão. Tente novamente.');
    }
    
    el.style.opacity = '1';
    el.style.pointerEvents = 'auto';
}

let pendingAlocacao = null;

function atualizarHorasServidor(servidorId) {
    const row = document.querySelector(`.servidor-row[data-servidor-id="${servidorId}"]`);
    if (row) {
        const horasEl = row.querySelector('.horas-servidor');
        if (horasEl) {
            const novasHoras = horasIniciais[servidorId] || 0;
            horasEl.textContent = novasHoras + 'h';
            if (novasHoras > limiteHoras) {
                horasEl.classList.add('text-danger');
            }
        }
    }
}

async function removerAlocacaoDia(servidorId, dia) {
    const form = new FormData();
    form.append('servidor_id', servidorId);
    form.append('escala_id', escalaId);
    form.append('dia', dia);
    
    const response = await fetch('/diretor/escala/remover-alocacao', {
        method: 'POST',
        body: form
    });
    
    const result = await response.json();
    if (result.success) {
        location.reload();
    } else {
        alert(result.message || 'Erro ao remover');
    }
}

function mostrarModalConflito(conflitos) {
    const lista = document.getElementById('conflitosList');
    lista.innerHTML = conflitos.map(c => `
        <div class="alert alert-warning py-2 mb-2 border-0">
            <strong>Dia ${c.dia}:</strong> ${c.equipe_atual} - ${c.modulo_atual}
        </div>
    `).join('');
    
    new bootstrap.Modal(document.getElementById('modalConflito')).show();
}

async function confirmarMover() {
    bootstrap.Modal.getInstance(document.getElementById('modalConflito')).hide();
    
    if (!pendingAlocacao) return;
    
    const { servidorId, dia, equipe, modulo, horas, abono, isLider, el } = pendingAlocacao;
    
    const form = new FormData();
    form.append('escala_id', escalaId);
    form.append('servidor_id', servidorId);
    form.append('equipe_id', equipe);
    form.append('modulo_id', modulo);
    form.append('horas', horas);
    form.append('horas_abono', abono);
    form.append('is_lider', isLider);
    form.append('dias', dia.toString());
    form.append('forcar_mover', '1');
    
    try {
        const response = await fetch('/diretor/escala/salvar-alocacao', {
            method: 'POST',
            body: form
        });
        
        const result = await response.json();
        
        if (result.success) {
            location.reload();
        } else {
            alert(result.message || 'Erro ao mover');
        }
    } catch (error) {
        alert('Erro de conexão.');
    }
    
    pendingAlocacao = null;
}

function imprimirEscala() {
    window.print();
}
</script>
