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
    max-height: 500px;
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
.dia-cell:hover:not(.alocado) {
    transform: scale(1.05);
    z-index: 10;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    background-color: #dbeafe !important;
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
    min-width: 200px;
    max-width: 200px;
}
.dias-container {
    display: flex;
    gap: 2px;
    flex-wrap: nowrap;
}
.form-selecao {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 1.25rem;
}
.servidor-item {
    padding: 0.75rem;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    margin-bottom: 0.5rem;
    cursor: pointer;
    transition: all 0.15s;
}
.servidor-item:hover {
    background-color: #f1f5f9;
}
.servidor-item.selected {
    background-color: #dbeafe;
    border-color: #3b82f6;
}
.servidor-item.disabled {
    opacity: 0.5;
    cursor: not-allowed;
    background-color: #f9fafb;
}
.badge-equipe {
    font-size: 0.7rem;
}
.table-calendario th {
    font-size: 0.75rem;
    padding: 0.4rem 0.2rem;
}
.table-calendario td {
    padding: 0.3rem 0.2rem;
    vertical-align: middle;
}
.btn-remover-servidor {
    opacity: 0;
    transition: opacity 0.2s;
}
.servidor-row:hover .btn-remover-servidor {
    opacity: 1;
}
.empty-state {
    text-align: center;
    padding: 3rem;
    color: #6b7280;
}
.empty-state i {
    font-size: 3rem;
    color: #d1d5db;
}
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold text-dark">
            <i class="bi bi-calendar3 me-2"></i>Escala de <?= $mesNome ?>/<?= $ano ?>
        </h4>
        <p class="text-muted mb-0 small"><?= htmlspecialchars($unidade['nome']) ?></p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <select id="selectMes" class="form-select form-select-sm" style="width:130px;" onchange="alterarPeriodo()">
            <?php foreach ($meses as $i => $m): ?>
                <option value="<?= $i + 1 ?>" <?= $i + 1 == $mes ? 'selected' : '' ?>><?= $m ?></option>
            <?php endforeach; ?>
        </select>
        <select id="selectAno" class="form-select form-select-sm" style="width:90px;" onchange="alterarPeriodo()">
            <?php for ($a = date('Y') - 1; $a <= date('Y') + 1; $a++): ?>
                <option value="<?= $a ?>" <?= $a == $ano ? 'selected' : '' ?>><?= $a ?></option>
            <?php endfor; ?>
        </select>
        <span class="badge <?= $escala['status'] == 'rascunho' ? 'bg-secondary' : 
            ($escala['status'] == 'pendente' ? 'bg-warning text-dark' : 
            ($escala['status'] == 'aprovada' ? 'bg-success' : 
            ($escala['status'] == 'rejeitada' ? 'bg-danger' : 'bg-info'))) ?>">
            <?= ucfirst($escala['status']) ?>
        </span>
    </div>
</div>

<?php if ($escala['status'] == 'rejeitada' && $escala['motivo_rejeicao']): ?>
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
    <div class="card-body form-selecao">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-semibold">1. Selecione a Equipe <span class="text-danger">*</span></label>
                <select id="equipeSelect" class="form-select" onchange="carregarServidoresEquipe()">
                    <option value="">Escolha uma equipe...</option>
                    <?php foreach ($equipes as $e): ?>
                        <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">2. Selecione o Módulo <span class="text-danger">*</span></label>
                <select id="moduloSelect" class="form-select">
                    <option value="">Escolha um módulo...</option>
                    <?php foreach ($modulos as $m): ?>
                        <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">Horas/dia</label>
                <input type="number" id="horasInput" class="form-control" min="1" max="24" value="12">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">Abono (h)</label>
                <input type="number" id="abonoInput" class="form-control" min="0" max="24" value="0">
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-primary w-100" id="btnAddServidor" onclick="abrirModalServidores()" disabled>
                    <i class="bi bi-person-plus me-1"></i> Add Servidor
                </button>
            </div>
        </div>
        <div class="mt-3 small text-muted">
            <i class="bi bi-lightbulb me-1"></i>
            <strong>Como usar:</strong> Selecione a Equipe e Módulo, clique em "Add Servidor" para adicionar servidores à equipe. 
            Depois, clique nos dias do calendário para alocar.
        </div>
    </div>
</div>
<?php else: ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body form-selecao">
        <div class="row g-3 align-items-center">
            <div class="col-md-4">
                <label class="form-label fw-semibold">Visualizar Equipe</label>
                <select id="equipeSelect" class="form-select" onchange="carregarServidoresEquipe()">
                    <option value="todas" selected>TODAS AS EQUIPES</option>
                    <?php foreach ($equipes as $e): ?>
                        <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-8">
                <div class="alert alert-info mb-0 py-2">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Modo Visualização:</strong> Esta escala está com status <strong><?= ucfirst($escala['status']) ?></strong> e não pode ser editada.
                </div>
            </div>
        </div>
    </div>
</div>
<input type="hidden" id="moduloSelect" value="">
<input type="hidden" id="horasInput" value="12">
<input type="hidden" id="abonoInput" value="0">
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-2 border-0">
        <h6 class="mb-0 fw-semibold">
            <i class="bi bi-calendar3 me-2"></i>Calendário - 
            <span id="equipeNomeHeader">Selecione uma equipe</span>
        </h6>
        <div>
            <a href="/diretor/escala/imprimir-mural?mes=<?= $mes ?>&ano=<?= $ano ?>" target="_blank" class="btn btn-outline-secondary btn-sm me-2">
                <i class="bi bi-printer me-1"></i> Imprimir P/Mural
            </a>
            <?php if ($podeEditar): ?>
            <a href="/diretor/enviar-escala?mes=<?= $mes ?>&ano=<?= $ano ?>" class="btn btn-success btn-sm" id="btnEnviar" style="display:none;">
                <i class="bi bi-send me-1"></i> <?= $escala['status'] == 'rejeitada' ? 'Re-Enviar para Aprovação' : 'Enviar para Aprovação' ?>
            </a>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="calendario-container" id="calendarioContainer">
        <div class="empty-state" id="emptyState">
            <i class="bi bi-people mb-3 d-block"></i>
            <h6>Nenhum servidor na equipe</h6>
            <p class="small">Selecione uma equipe e clique em "Add Servidor" para começar</p>
        </div>
        <table class="table table-sm table-calendario mb-0" id="tabelaCalendario" style="display:none;">
            <thead class="sticky-top bg-white">
                <tr>
                    <th class="servidor-info bg-light text-muted">Servidor</th>
                    <th class="text-center bg-light text-muted" style="min-width:45px;">Horas</th>
                    <th class="text-center bg-light text-muted" style="min-width:50px;">Líder</th>
                    <?php for ($d = 1; $d <= $diasNoMes; $d++): 
                        $info = $diasInfo[$d];
                    ?>
                        <th class="text-center bg-light text-muted px-1" style="min-width:34px;" title="<?= $info['nomeDia'] ?>, dia <?= $d ?>">
                            <small class="d-block text-uppercase" style="font-size:0.6rem;"><?= substr($info['nomeDia'], 0, 3) ?></small>
                            <?= $d ?>
                        </th>
                    <?php endfor; ?>
                </tr>
            </thead>
            <tbody id="calendarioBody">
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modalServidores" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white border-0">
                <h6 class="modal-title fw-semibold">
                    <i class="bi bi-person-plus me-2"></i>Adicionar Servidores à Equipe
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" class="form-control" id="buscarServidor" placeholder="Buscar servidor por nome ou matrícula...">
                </div>
                <div id="listaServidoresModal" style="max-height: 400px; overflow-y: auto;">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Carregando...</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="salvarServidoresSelecionados()">
                    <i class="bi bi-check-lg me-1"></i> Adicionar Selecionados
                </button>
            </div>
        </div>
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
                <p class="text-muted">O servidor já está alocado em outro local para este dia:</p>
                <div id="conflitoInfo" class="alert alert-warning mb-3"></div>
                <p class="mb-0">Deseja mover para o novo local?</p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning btn-sm" onclick="confirmarMover()">
                    <i class="bi bi-arrow-right-circle me-1"></i> Mover
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const escalaId = <?= $escala['id'] ?>;
const limiteHoras = <?= $limiteHoras ?>;
const podeEditar = <?= $podeEditar ? 'true' : 'false' ?>;
const diasNoMes = <?= $diasNoMes ?>;
const diasInfo = <?= json_encode($diasInfo) ?>;
let servidoresSelecionadosModal = new Set();
let servidoresEquipeAtual = [];
let pendingAlocacao = null;

const horasMap = {};

function alterarPeriodo() {
    const mes = document.getElementById('selectMes').value;
    const ano = document.getElementById('selectAno').value;
    window.location.href = `/diretor/escala-mensal?mes=${mes}&ano=${ano}`;
}

document.getElementById('equipeSelect')?.addEventListener('change', function() {
    const btn = document.getElementById('btnAddServidor');
    const modulo = document.getElementById('moduloSelect')?.value;
    btn.disabled = !(this.value && modulo);
    
    document.getElementById('equipeNomeHeader').textContent = 
        this.value ? this.options[this.selectedIndex].text : 'Selecione uma equipe';
});

document.getElementById('moduloSelect')?.addEventListener('change', function() {
    const btn = document.getElementById('btnAddServidor');
    const equipe = document.getElementById('equipeSelect')?.value;
    btn.disabled = !(this.value && equipe);
});

function carregarServidoresEquipe() {
    const equipeId = document.getElementById('equipeSelect')?.value;
    if (!equipeId) {
        document.getElementById('emptyState').style.display = 'block';
        document.getElementById('tabelaCalendario').style.display = 'none';
        const btnEnviar = document.getElementById('btnEnviar');
        if (btnEnviar) btnEnviar.style.display = 'none';
        return;
    }
    
    fetch(`/diretor/escala/servidores-equipe?escala_id=${escalaId}&equipe_id=${equipeId}`)
        .then(r => r.json())
        .then(data => {
            servidoresEquipeAtual = data.servidores || [];
            renderizarCalendario();
        });
}

document.addEventListener('DOMContentLoaded', function() {
    const equipeSelect = document.getElementById('equipeSelect');
    if (equipeSelect && equipeSelect.value === 'todas') {
        carregarServidoresEquipe();
    }
});

function renderizarCalendario() {
    const tbody = document.getElementById('calendarioBody');
    const emptyState = document.getElementById('emptyState');
    const tabela = document.getElementById('tabelaCalendario');
    const btnEnviar = document.getElementById('btnEnviar');
    
    if (servidoresEquipeAtual.length === 0) {
        emptyState.style.display = 'block';
        tabela.style.display = 'none';
        if (btnEnviar) btnEnviar.style.display = 'none';
        return;
    }
    
    emptyState.style.display = 'none';
    tabela.style.display = 'table';
    if (btnEnviar) btnEnviar.style.display = 'inline-block';
    
    const equipeId = document.getElementById('equipeSelect')?.value;
    const moduloId = document.getElementById('moduloSelect')?.value;
    const mostrarEquipe = (equipeId === 'todas');
    
    let html = '';
    servidoresEquipeAtual.forEach(servidor => {
        horasMap[servidor.id] = parseFloat(servidor.total_horas) || 0;
        const horasClass = horasMap[servidor.id] >= limiteHoras ? 'text-danger' : 'text-success';
        const equipeNome = servidor.equipe_nome || '';
        
        html += `<tr class="servidor-row" data-servidor-id="${servidor.id}">
            <td class="servidor-info">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="fw-semibold small">${servidor.nome}</div>
                        <small class="text-muted">${servidor.matricula}${mostrarEquipe ? ' - <span class="badge bg-primary">' + equipeNome + '</span>' : ''}</small>
                    </div>
                    ${podeEditar ? `<button class="btn btn-sm btn-outline-danger btn-remover-servidor" onclick="removerServidorEquipe(${servidor.id})" title="Remover da equipe">
                        <i class="bi bi-x"></i>
                    </button>` : ''}
                </div>
            </td>
            <td class="text-center">
                <span class="badge bg-light ${horasClass} horas-servidor" id="horas-${servidor.id}">${horasMap[servidor.id]}h</span>
            </td>
            <td class="text-center">
                <input type="checkbox" class="form-check-input" ${servidor.is_lider ? 'checked' : ''} 
                    onchange="atualizarLider(${servidor.id}, this.checked)" ${!podeEditar ? 'disabled' : ''}>
            </td>`;
        
        for (let d = 1; d <= diasNoMes; d++) {
            const info = diasInfo[d];
            let classe = '';
            let alocado = false;
            
            if (info.isFeriado) classe = 'feriado';
            else if (info.diaSemana === 0) classe = 'domingo';
            else if (info.diaSemana === 6) classe = 'sabado';
            
            // Verificar se está alocado (precisa buscar via AJAX ou ter os dados)
            const alocacaoKey = `${servidor.id}_${d}`;
            
            html += `<td>
                <div class="dia-cell ${classe}" 
                     data-dia="${d}" 
                     data-servidor="${servidor.id}"
                     data-alocado="0"
                     title="${info.isFeriado ? info.nomeFeriado : info.nomeDia + ', dia ' + d}"
                     onclick="${podeEditar ? 'toggleDia(this)' : ''}">
                    ${d}
                </div>
            </td>`;
        }
        
        html += '</tr>';
    });
    
    tbody.innerHTML = html;
    
    // Carregar alocações existentes
    carregarAlocacoesExistentes();
}

function carregarAlocacoesExistentes() {
    const equipeId = document.getElementById('equipeSelect')?.value;
    if (!equipeId) return;
    
    // Buscar alocações via PHP já renderizado
    <?php foreach ($alocacoes as $a): ?>
    marcarDiaAlocado(<?= $a['servidor_id'] ?>, <?= $a['dia'] ?>, <?= $a['horas'] ?>);
    <?php endforeach; ?>
}

function marcarDiaAlocado(servidorId, dia, horas) {
    const cell = document.querySelector(`.dia-cell[data-servidor="${servidorId}"][data-dia="${dia}"]`);
    if (cell) {
        cell.dataset.alocado = '1';
        cell.classList.add('alocado');
        cell.title = `Alocado: ${horas}h`;
    }
}

async function toggleDia(el) {
    if (!podeEditar) return;
    
    const dia = parseInt(el.dataset.dia);
    const servidorId = parseInt(el.dataset.servidor);
    const alocado = el.dataset.alocado === '1';
    
    const equipe = document.getElementById('equipeSelect')?.value;
    const modulo = document.getElementById('moduloSelect')?.value;
    const horas = document.getElementById('horasInput')?.value || 12;
    const abono = document.getElementById('abonoInput')?.value || 0;
    
    if (!equipe) {
        alert('Selecione uma equipe primeiro.');
        return;
    }
    
    if (!modulo) {
        alert('Selecione um módulo primeiro.');
        return;
    }
    
    if (alocado) {
        if (confirm('Remover alocação deste dia?')) {
            await removerAlocacaoDia(servidorId, dia, el);
        }
        return;
    }
    
    // Alocar
    el.style.opacity = '0.5';
    el.style.pointerEvents = 'none';
    
    const servidor = servidoresEquipeAtual.find(s => s.id == servidorId);
    const isLider = servidor?.is_lider ? 1 : 0;
    
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
            el.dataset.alocado = '1';
            el.classList.add('alocado');
            el.title = `Alocado: ${horas}h`;
            
            horasMap[servidorId] = (horasMap[servidorId] || 0) + parseFloat(horas) + parseFloat(abono);
            atualizarHorasDisplay(servidorId);
            
        } else if (result.conflito) {
            pendingAlocacao = { servidorId, dia, equipe, modulo, horas, abono, isLider, el };
            document.getElementById('conflitoInfo').innerHTML = result.conflitos.map(c => 
                `<strong>Dia ${c.dia}:</strong> ${c.equipe_atual} - ${c.modulo_atual}`
            ).join('<br>');
            new bootstrap.Modal(document.getElementById('modalConflito')).show();
        } else {
            alert(result.message || 'Erro ao alocar');
        }
    } catch (error) {
        alert('Erro de conexão.');
    }
    
    el.style.opacity = '1';
    el.style.pointerEvents = 'auto';
}

async function removerAlocacaoDia(servidorId, dia, el) {
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
        el.dataset.alocado = '0';
        el.classList.remove('alocado');
        
        const horas = parseFloat(document.getElementById('horasInput')?.value || 12);
        const abono = parseFloat(document.getElementById('abonoInput')?.value || 0);
        horasMap[servidorId] = Math.max(0, (horasMap[servidorId] || 0) - horas - abono);
        atualizarHorasDisplay(servidorId);
    } else {
        alert(result.message || 'Erro ao remover');
    }
}

function atualizarHorasDisplay(servidorId) {
    const el = document.getElementById(`horas-${servidorId}`);
    if (el) {
        const horas = horasMap[servidorId] || 0;
        el.textContent = horas + 'h';
        el.classList.toggle('text-danger', horas >= limiteHoras);
        el.classList.toggle('text-success', horas < limiteHoras);
    }
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

function abrirModalServidores() {
    const equipeId = document.getElementById('equipeSelect')?.value;
    if (!equipeId) {
        alert('Selecione uma equipe primeiro.');
        return;
    }
    
    servidoresSelecionadosModal.clear();
    document.getElementById('listaServidoresModal').innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>';
    new bootstrap.Modal(document.getElementById('modalServidores')).show();
    
    fetch(`/diretor/escala/servidores-disponiveis?escala_id=${escalaId}&equipe_id=${equipeId}`)
        .then(r => r.json())
        .then(data => {
            console.log('Resposta servidores:', data);
            if (!data.success) {
                document.getElementById('listaServidoresModal').innerHTML = 
                    `<div class="alert alert-warning">${data.message || 'Erro ao carregar servidores'}</div>`;
                return;
            }
            renderizarListaServidoresModal(data.servidores || []);
        })
        .catch(err => {
            console.error('Erro ao buscar servidores:', err);
            document.getElementById('listaServidoresModal').innerHTML = 
                '<div class="alert alert-danger">Erro ao carregar servidores. Faça login novamente.</div>';
        });
}

function renderizarListaServidoresModal(servidores) {
    const container = document.getElementById('listaServidoresModal');
    const equipeIdAtual = document.getElementById('equipeSelect')?.value;
    
    if (servidores.length === 0) {
        container.innerHTML = '<div class="text-center py-4 text-muted">Nenhum servidor disponível</div>';
        return;
    }
    
    let html = '';
    servidores.forEach(s => {
        const jaVinculado = s.equipe_atual_id != null;
        const mesmaEquipe = s.equipe_atual_id == equipeIdAtual;
        const disabled = jaVinculado && !mesmaEquipe;
        
        html += `<div class="servidor-item ${disabled ? 'disabled' : ''} ${mesmaEquipe ? 'selected' : ''}" 
                     data-id="${s.id}" 
                     onclick="${!disabled ? 'toggleServidorModal(this)' : ''}">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="fw-semibold">${s.nome}</div>
                    <small class="text-muted">${s.matricula}</small>
                </div>
                <div>
                    ${jaVinculado ? `<span class="badge ${mesmaEquipe ? 'bg-success' : 'bg-secondary'} badge-equipe">${s.equipe_atual}</span>` : ''}
                    ${!disabled ? '<i class="bi bi-check-circle-fill text-primary ms-2" style="display:none;"></i>' : ''}
                </div>
            </div>
        </div>`;
    });
    
    container.innerHTML = html;
}

function toggleServidorModal(el) {
    const id = parseInt(el.dataset.id);
    
    if (servidoresSelecionadosModal.has(id)) {
        servidoresSelecionadosModal.delete(id);
        el.classList.remove('selected');
        el.querySelector('.bi-check-circle-fill').style.display = 'none';
    } else {
        servidoresSelecionadosModal.add(id);
        el.classList.add('selected');
        el.querySelector('.bi-check-circle-fill').style.display = 'inline';
    }
}

async function salvarServidoresSelecionados() {
    if (servidoresSelecionadosModal.size === 0) {
        alert('Selecione pelo menos um servidor.');
        return;
    }
    
    const equipeId = document.getElementById('equipeSelect')?.value;
    
    const form = new FormData();
    form.append('escala_id', escalaId);
    form.append('equipe_id', equipeId);
    servidoresSelecionadosModal.forEach(id => {
        form.append('servidor_ids[]', id);
    });
    
    const response = await fetch('/diretor/escala/adicionar-servidor-equipe', {
        method: 'POST',
        body: form
    });
    
    const result = await response.json();
    
    bootstrap.Modal.getInstance(document.getElementById('modalServidores')).hide();
    
    if (result.conflitos && result.conflitos.length > 0) {
        let msg = result.message + '\n\nConflitos:\n';
        result.conflitos.forEach(c => {
            msg += `- ${c.servidor} já está na ${c.equipe_atual}\n`;
        });
        alert(msg);
    }
    
    carregarServidoresEquipe();
}

async function removerServidorEquipe(servidorId) {
    if (!confirm('Remover este servidor da equipe? Todas as alocações serão removidas.')) return;
    
    const form = new FormData();
    form.append('escala_id', escalaId);
    form.append('servidor_id', servidorId);
    
    const response = await fetch('/diretor/escala/remover-servidor-equipe', {
        method: 'POST',
        body: form
    });
    
    const result = await response.json();
    if (result.success) {
        carregarServidoresEquipe();
    } else {
        alert(result.message || 'Erro ao remover');
    }
}

async function atualizarLider(servidorId, isLider) {
    const form = new FormData();
    form.append('escala_id', escalaId);
    form.append('servidor_id', servidorId);
    form.append('is_lider', isLider ? '1' : '0');
    
    await fetch('/diretor/escala/atualizar-lider', {
        method: 'POST',
        body: form
    });
    
    // Atualizar no array local
    const servidor = servidoresEquipeAtual.find(s => s.id == servidorId);
    if (servidor) servidor.is_lider = isLider;
}

document.getElementById('buscarServidor')?.addEventListener('input', function() {
    const termo = this.value.toLowerCase();
    document.querySelectorAll('#listaServidoresModal .servidor-item').forEach(el => {
        const texto = el.textContent.toLowerCase();
        el.style.display = texto.includes(termo) ? '' : 'none';
    });
});

function imprimirEscala() {
    window.print();
}
</script>
