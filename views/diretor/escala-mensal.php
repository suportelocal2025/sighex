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
?>

<style>
.dia-cell {
    width: 38px;
    height: 38px;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
    cursor: pointer;
    transition: all 0.2s;
    position: relative;
}
.dia-cell:hover {
    transform: scale(1.1);
    z-index: 10;
}
.dia-cell.dia-semana {
    background-color: #f8f9fa;
}
.dia-cell.sabado {
    background-color: #fff3cd;
    border-color: #ffc107;
}
.dia-cell.domingo {
    background-color: #f8d7da;
    border-color: #dc3545;
}
.dia-cell.feriado {
    background-color: #d1e7dd;
    border-color: #198754;
}
.dia-cell.selecionado {
    background-color: #0d6efd !important;
    color: white !important;
    border-color: #0d6efd !important;
    font-weight: bold;
}
.dia-cell.alocado {
    background-color: #6f42c1 !important;
    color: white !important;
    border-color: #6f42c1 !important;
}
.dia-cell.alocado-outro {
    background-color: #fd7e14 !important;
    color: white !important;
    border-color: #fd7e14 !important;
}
.servidor-row {
    transition: background-color 0.2s;
}
.servidor-row:hover {
    background-color: #f0f7ff;
}
.servidor-row.selecionado {
    background-color: #e3f2fd;
}
.horas-badge {
    font-size: 0.75rem;
    min-width: 45px;
}
.legenda-item {
    display: inline-flex;
    align-items: center;
    margin-right: 15px;
    font-size: 0.85rem;
}
.legenda-cor {
    width: 20px;
    height: 20px;
    border-radius: 4px;
    margin-right: 5px;
}
.calendario-header {
    position: sticky;
    top: 0;
    background: white;
    z-index: 100;
}
.servidor-info {
    min-width: 200px;
    max-width: 200px;
}
.dias-container {
    display: flex;
    gap: 2px;
    flex-wrap: nowrap;
    overflow-x: auto;
}
.form-alocacao {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    padding: 1.5rem;
    color: white;
}
.form-alocacao .form-label {
    color: rgba(255,255,255,0.9);
    font-weight: 500;
}
.form-alocacao .form-select, .form-alocacao .form-control {
    background-color: rgba(255,255,255,0.95);
}
</style>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card p-3">
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <div>
                    <label class="form-label mb-0 small">Mês</label>
                    <select class="form-select form-select-sm" id="selectMes" onchange="alterarPeriodo()">
                        <?php foreach ($meses as $i => $m): ?>
                            <option value="<?= $i + 1 ?>" <?= ($i + 1) == $mes ? 'selected' : '' ?>><?= $m ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label mb-0 small">Ano</label>
                    <select class="form-select form-select-sm" id="selectAno" onchange="alterarPeriodo()">
                        <?php for ($y = date('Y') - 1; $y <= date('Y') + 1; $y++): ?>
                            <option value="<?= $y ?>" <?= $y == $ano ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="ms-auto">
                    <span class="badge bg-<?= 
                        $escala['status'] == 'rascunho' ? 'secondary' :
                        ($escala['status'] == 'pendente' ? 'warning text-dark' :
                        ($escala['status'] == 'aprovada' ? 'success' :
                        ($escala['status'] == 'executada' ? 'info' : 'danger')))
                    ?> fs-6 px-3 py-2">
                        <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i>
                        <?= ucfirst($escala['status']) ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 bg-primary text-white h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <small>Total de Horas</small>
                    <h4 class="mb-0" id="totalHorasGeral"><?= number_format($escala['total_horas'], 0, ',', '.') ?>h</h4>
                </div>
                <i class="bi bi-clock-history" style="font-size: 1.8rem;"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 bg-success text-white h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <small>Servidores Alocados</small>
                    <h4 class="mb-0"><?= count($alocacoesPorServidor) ?></h4>
                </div>
                <i class="bi bi-people-fill" style="font-size: 1.8rem;"></i>
            </div>
        </div>
    </div>
</div>

<?php if ($escala['status'] == 'rejeitada'): ?>
<div class="alert alert-danger mb-4">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    <strong>Escala Rejeitada:</strong> <?= htmlspecialchars($escala['motivo_rejeicao']) ?>
</div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header bg-white py-2">
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <strong class="me-3"><i class="bi bi-info-circle me-1"></i> Legenda:</strong>
            <span class="legenda-item"><span class="legenda-cor" style="background:#f8f9fa; border:1px solid #dee2e6;"></span> Dia útil</span>
            <span class="legenda-item"><span class="legenda-cor" style="background:#fff3cd; border:1px solid #ffc107;"></span> Sábado</span>
            <span class="legenda-item"><span class="legenda-cor" style="background:#f8d7da; border:1px solid #dc3545;"></span> Domingo</span>
            <span class="legenda-item"><span class="legenda-cor" style="background:#d1e7dd; border:1px solid #198754;"></span> Feriado</span>
            <span class="legenda-item"><span class="legenda-cor" style="background:#0d6efd;"></span> Selecionado</span>
            <span class="legenda-item"><span class="legenda-cor" style="background:#6f42c1;"></span> Alocado</span>
        </div>
    </div>
</div>

<?php if ($escala['status'] == 'rascunho'): ?>
<div class="card mb-4">
    <div class="card-body form-alocacao">
        <h5 class="mb-3"><i class="bi bi-plus-circle me-2"></i>Alocar Servidor</h5>
        <form id="formAlocacao">
            <input type="hidden" name="escala_id" value="<?= $escala['id'] ?>">
            <input type="hidden" name="dias" id="diasSelecionados" value="">
            
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Servidor</label>
                    <select name="servidor_id" id="servidorSelect" class="form-select" required onchange="carregarAlocacoesServidor()">
                        <option value="">Selecione um servidor...</option>
                        <?php foreach ($servidores as $s): ?>
                            <option value="<?= $s['id'] ?>" data-horas="<?= $horasMap[$s['id']] ?? 0 ?>">
                                <?= htmlspecialchars($s['nome']) ?> (<?= $s['matricula'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Equipe</label>
                    <select name="equipe_id" id="equipeSelect" class="form-select" required>
                        <option value="">Selecione...</option>
                        <?php foreach ($equipes as $e): ?>
                            <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Módulo/Raio</label>
                    <select name="modulo_id" id="moduloSelect" class="form-select" required>
                        <option value="">Selecione...</option>
                        <?php foreach ($modulos as $m): ?>
                            <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label">Horas</label>
                    <input type="number" name="horas" id="horasInput" class="form-control" min="1" max="24" step="1" value="12" required>
                </div>
                <div class="col-md-1">
                    <label class="form-label">Abono</label>
                    <input type="number" name="horas_abono" class="form-control" min="0" max="24" step="1" value="0">
                </div>
                <div class="col-md-1">
                    <label class="form-label">Líder</label>
                    <div class="form-check mt-2">
                        <input type="checkbox" name="is_lider" value="1" class="form-check-input" style="width:24px; height:24px;">
                    </div>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-light w-100 fw-bold" id="btnAlocar" disabled>
                        <i class="bi bi-check-lg me-1"></i> Alocar
                    </button>
                </div>
            </div>
            
            <div class="row mt-3">
                <div class="col-12">
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <span id="diasSelecionadosInfo" class="badge bg-light text-dark fs-6">
                            <i class="bi bi-calendar3 me-1"></i> Nenhum dia selecionado
                        </span>
                        <span id="horasServidorInfo" class="badge bg-warning text-dark fs-6" style="display:none;">
                            <i class="bi bi-clock me-1"></i> <span id="horasAtuais">0</span>h / 60h utilizadas
                        </span>
                        <span id="horasProjetadasInfo" class="badge bg-info text-dark fs-6" style="display:none;">
                            <i class="bi bi-calculator me-1"></i> +<span id="horasProjetadas">0</span>h projetadas
                        </span>
                        <button type="button" class="btn btn-outline-light btn-sm ms-auto" onclick="limparSelecao()">
                            <i class="bi bi-x-lg me-1"></i> Limpar Seleção
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
        <h5 class="mb-0"><i class="bi bi-calendar3 me-2"></i>Calendário de <?= $mesNome ?>/<?= $ano ?></h5>
        <div>
            <button class="btn btn-outline-primary btn-sm me-2" onclick="imprimirEscala()">
                <i class="bi bi-printer me-1"></i> Imprimir
            </button>
            <?php if ($escala['status'] == 'rascunho' && count($alocacoes) > 0): ?>
            <a href="/diretor/enviar-escala?mes=<?= $mes ?>&ano=<?= $ano ?>" class="btn btn-success btn-sm">
                <i class="bi bi-send me-1"></i> Enviar para Aprovação
            </a>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="table-responsive">
        <table class="table table-sm mb-0" id="tabelaCalendario">
            <thead class="calendario-header">
                <tr>
                    <th class="servidor-info bg-light">Servidor</th>
                    <th class="text-center bg-light" style="min-width:50px;">Horas</th>
                    <?php for ($d = 1; $d <= $diasNoMes; $d++): 
                        $info = $diasInfo[$d];
                        $classeHeader = '';
                        if ($info['isFeriado']) $classeHeader = 'bg-success-subtle';
                        elseif ($info['diaSemana'] == 0) $classeHeader = 'bg-danger-subtle';
                        elseif ($info['diaSemana'] == 6) $classeHeader = 'bg-warning-subtle';
                    ?>
                        <th class="text-center <?= $classeHeader ?>" style="min-width:40px;" 
                            title="<?= $info['isFeriado'] ? $info['nomeFeriado'] : '' ?>">
                            <small class="d-block text-muted"><?= $info['nomeDia'] ?></small>
                            <?= $d ?>
                        </th>
                    <?php endfor; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($servidores)): ?>
                <tr>
                    <td colspan="<?= $diasNoMes + 2 ?>" class="text-center py-4 text-muted">
                        <i class="bi bi-person-x fs-1 d-block mb-2"></i>
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
                        <div class="fw-medium text-truncate" title="<?= htmlspecialchars($s['nome']) ?>">
                            <?= htmlspecialchars($s['nome']) ?>
                        </div>
                        <small class="text-muted"><?= $s['matricula'] ?></small>
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
                                 onclick="<?= $escala['status'] == 'rascunho' ? 'toggleDia(this)' : '' ?>">
                                <?= $d ?>
                                <?php if ($alocacao && $alocacao['is_lider']): ?>
                                    <i class="bi bi-star-fill position-absolute" style="font-size:0.5rem; top:2px; right:2px; color:#ffc107;"></i>
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
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>Servidor já alocado</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>O servidor já está alocado em outro local para os seguintes dias:</p>
                <div id="conflitosList" class="mb-3"></div>
                <p>O que deseja fazer?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" onclick="confirmarMover()">
                    <i class="bi bi-arrow-right-circle me-1"></i> Mover para novo local
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const escalaId = <?= $escala['id'] ?>;
const limiteHoras = <?= $limiteHoras ?>;
const statusEscala = '<?= $escala['status'] ?>';
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

function toggleDia(el) {
    if (statusEscala !== 'rascunho') return;
    
    const dia = parseInt(el.dataset.dia);
    const servidorId = parseInt(el.dataset.servidor);
    const alocado = el.dataset.alocado === '1';
    
    if (!document.getElementById('servidorSelect').value) {
        document.getElementById('servidorSelect').value = servidorId;
        carregarAlocacoesServidor();
    }
    
    const servidorAtual = parseInt(document.getElementById('servidorSelect').value);
    if (servidorId !== servidorAtual) {
        alert('Selecione primeiro o servidor na lista acima ou clique em um dia do mesmo servidor.');
        return;
    }
    
    if (alocado) {
        if (confirm('Remover alocação deste dia?')) {
            removerAlocacaoDia(servidorId, dia);
        }
        return;
    }
    
    if (diasSelecionados.has(dia)) {
        diasSelecionados.delete(dia);
        el.classList.remove('selecionado');
    } else {
        diasSelecionados.add(dia);
        el.classList.add('selecionado');
    }
    
    atualizarInfoDias();
}

function atualizarInfoDias() {
    const diasArray = Array.from(diasSelecionados).sort((a,b) => a-b);
    const info = document.getElementById('diasSelecionadosInfo');
    const btn = document.getElementById('btnAlocar');
    
    if (diasArray.length === 0) {
        info.innerHTML = '<i class="bi bi-calendar3 me-1"></i> Nenhum dia selecionado';
        btn.disabled = true;
    } else {
        info.innerHTML = `<i class="bi bi-calendar-check me-1"></i> ${diasArray.length} dia(s): ${diasArray.join(', ')}`;
        btn.disabled = !document.getElementById('servidorSelect').value;
    }
    
    document.getElementById('diasSelecionados').value = diasArray.join(',');
    calcularHorasProjetadas();
}

function calcularHorasProjetadas() {
    const servidorId = document.getElementById('servidorSelect').value;
    const horas = parseFloat(document.getElementById('horasInput').value) || 0;
    const abono = parseFloat(document.querySelector('[name="horas_abono"]').value) || 0;
    const totalPorDia = horas + abono;
    const diasCount = diasSelecionados.size;
    const horasProjetadas = diasCount * totalPorDia;
    
    const infoProjetadas = document.getElementById('horasProjetadasInfo');
    const spanProjetadas = document.getElementById('horasProjetadas');
    
    if (diasCount > 0 && servidorId) {
        infoProjetadas.style.display = 'inline-flex';
        spanProjetadas.textContent = horasProjetadas;
        
        const horasAtuais = horasIniciais[servidorId] || 0;
        if (horasAtuais + horasProjetadas > limiteHoras) {
            infoProjetadas.classList.remove('bg-info');
            infoProjetadas.classList.add('bg-danger', 'text-white');
        } else {
            infoProjetadas.classList.remove('bg-danger', 'text-white');
            infoProjetadas.classList.add('bg-info');
        }
    } else {
        infoProjetadas.style.display = 'none';
    }
}

function carregarAlocacoesServidor() {
    const select = document.getElementById('servidorSelect');
    const servidorId = select.value;
    
    limparSelecaoVisual();
    
    if (!servidorId) {
        document.getElementById('horasServidorInfo').style.display = 'none';
        return;
    }
    
    servidorSelecionado = servidorId;
    const horasAtuais = horasIniciais[servidorId] || 0;
    
    document.getElementById('horasServidorInfo').style.display = 'inline-flex';
    document.getElementById('horasAtuais').textContent = horasAtuais;
    
    document.querySelectorAll('.servidor-row').forEach(row => {
        row.classList.remove('selecionado');
        if (row.dataset.servidorId == servidorId) {
            row.classList.add('selecionado');
        }
    });
    
    atualizarInfoDias();
}

function limparSelecaoVisual() {
    document.querySelectorAll('.dia-cell.selecionado').forEach(el => {
        el.classList.remove('selecionado');
    });
    diasSelecionados.clear();
}

function limparSelecao() {
    limparSelecaoVisual();
    atualizarInfoDias();
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

document.getElementById('formAlocacao')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    if (diasSelecionados.size === 0) {
        alert('Selecione pelo menos um dia no calendário');
        return;
    }
    
    await enviarAlocacao(false);
});

async function enviarAlocacao(forcarMover) {
    const form = new FormData(document.getElementById('formAlocacao'));
    if (forcarMover) {
        form.append('forcar_mover', '1');
    }
    
    const response = await fetch('/diretor/escala/salvar-alocacao', {
        method: 'POST',
        body: form
    });
    
    const result = await response.json();
    
    if (result.success) {
        location.reload();
    } else if (result.conflito) {
        conflitosAtuais = result.conflitos;
        mostrarModalConflito(result.conflitos);
    } else {
        alert(result.message || 'Erro ao salvar');
    }
}

function mostrarModalConflito(conflitos) {
    const lista = document.getElementById('conflitosList');
    lista.innerHTML = conflitos.map(c => `
        <div class="alert alert-warning py-2 mb-2">
            <strong>Dia ${c.dia}:</strong> ${c.equipe_atual} - ${c.modulo_atual}
        </div>
    `).join('');
    
    new bootstrap.Modal(document.getElementById('modalConflito')).show();
}

function confirmarMover() {
    bootstrap.Modal.getInstance(document.getElementById('modalConflito')).hide();
    enviarAlocacao(true);
}

document.getElementById('horasInput')?.addEventListener('change', calcularHorasProjetadas);
document.querySelector('[name="horas_abono"]')?.addEventListener('change', calcularHorasProjetadas);

function imprimirEscala() {
    window.print();
}
</script>
