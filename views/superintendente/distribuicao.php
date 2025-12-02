<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div>
                    <div class="stat-value">R$ <?= number_format($valorDisponivel, 0, ',', '.') ?></div>
                    <div class="stat-label">Orçamento Disponível</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                    <i class="bi bi-check2-circle"></i>
                </div>
                <div>
                    <div class="stat-value">R$ <?= number_format($totalDistribuido, 0, ',', '.') ?></div>
                    <div class="stat-label">Total Distribuído</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-info bg-opacity-10 text-info me-3">
                    <i class="bi bi-wallet2"></i>
                </div>
                <div>
                    <div class="stat-value">R$ <?= number_format($saldoRestante, 0, ',', '.') ?></div>
                    <div class="stat-label">Saldo Restante</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-secondary bg-opacity-10 text-secondary me-3">
                    <i class="bi bi-calendar3"></i>
                </div>
                <div>
                    <div class="stat-label mb-1">Filtrar por Ano</div>
                    <select class="form-select form-select-sm" onchange="window.location.href='?ano='+this.value" style="width: 100px;">
                        <?php for ($y = date('Y') - 2; $y <= date('Y') + 1; $y++): ?>
                            <option value="<?= $y ?>" <?= $y == $ano ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-diagram-3 me-2"></i>Distribuição por Unidade</h5>
        <div class="alert alert-info py-1 px-3 mb-0" id="alertaSaldo">
            Saldo: <strong id="saldoDisplay">R$ <?= number_format($saldoRestante, 2, ',', '.') ?></strong>
        </div>
    </div>
    <div class="card-body">
        <form action="/superintendente/distribuicao/salvar" method="POST" id="formDistribuicao">
            <input type="hidden" name="ano" value="<?= $ano ?>">
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th style="width: 40%">Unidade</th>
                            <th style="width: 25%">Valor a Distribuir (R$)</th>
                            <th style="width: 15%">% do Total</th>
                            <th style="width: 20%" class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($unidades as $u): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($u['nome']) ?></strong>
                                </td>
                                <td>
                                    <div class="input-group">
                                        <span class="input-group-text">R$</span>
                                        <input type="text" class="form-control valor-distribuicao" 
                                               name="distribuicao[<?= $u['id'] ?>]"
                                               value="<?= number_format($u['valor_distribuido'], 2, ',', '.') ?>"
                                               data-id="<?= $u['id'] ?>">
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary percentual-badge" data-id="<?= $u['id'] ?>">
                                        <?= $valorDisponivel > 0 ? number_format(($u['valor_distribuido'] / $valorDisponivel) * 100, 1) : 0 ?>%
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-info" 
                                            onclick="verHistorico(<?= $u['id'] ?>, '<?= htmlspecialchars(addslashes($u['nome'])) ?>')">
                                        <i class="bi bi-clock-history me-1"></i>Histórico
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-light">
                            <th>Total</th>
                            <th>
                                <div class="input-group">
                                    <span class="input-group-text">R$</span>
                                    <input type="text" class="form-control fw-bold" id="totalDistribuido" 
                                           value="<?= number_format($totalDistribuido, 2, ',', '.') ?>" readonly>
                                </div>
                            </th>
                            <th>
                                <span class="badge bg-primary" id="percentualTotal">
                                    <?= $valorDisponivel > 0 ? number_format(($totalDistribuido / $valorDisponivel) * 100, 1) : 0 ?>%
                                </span>
                            </th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="/" class="btn btn-outline-secondary btn-lg">
                    <i class="bi bi-arrow-left me-2"></i>Voltar
                </a>
                <button type="submit" class="btn btn-primary btn-lg" id="btnSalvar">
                    <i class="bi bi-check-lg me-2"></i>Salvar Distribuição
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const valorDisponivel = <?= $valorDisponivel ?>;

function parseValor(str) {
    return parseFloat(str.replace(/\./g, '').replace(',', '.')) || 0;
}

function formatValor(num) {
    return num.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

function calcularTotais() {
    let total = 0;
    document.querySelectorAll('.valor-distribuicao').forEach(input => {
        total += parseValor(input.value);
    });
    
    const saldo = valorDisponivel - total;
    
    document.getElementById('totalDistribuido').value = formatValor(total);
    document.getElementById('saldoDisplay').textContent = 'R$ ' + formatValor(saldo);
    
    const alerta = document.getElementById('alertaSaldo');
    const btnSalvar = document.getElementById('btnSalvar');
    
    if (saldo < 0) {
        alerta.className = 'alert alert-danger py-1 px-3 mb-0';
        btnSalvar.disabled = true;
    } else {
        alerta.className = 'alert alert-success py-1 px-3 mb-0';
        btnSalvar.disabled = false;
    }
    
    if (valorDisponivel > 0) {
        document.getElementById('percentualTotal').textContent = ((total / valorDisponivel) * 100).toFixed(1) + '%';
        
        document.querySelectorAll('.valor-distribuicao').forEach(input => {
            const valor = parseValor(input.value);
            const perc = (valor / valorDisponivel) * 100;
            document.querySelector(`.percentual-badge[data-id="${input.dataset.id}"]`).textContent = perc.toFixed(1) + '%';
        });
    }
}

document.querySelectorAll('.valor-distribuicao').forEach(input => {
    input.addEventListener('input', calcularTotais);
});

function verHistorico(unidadeId, nomeUnidade) {
    document.getElementById('historicoUnidadeNome').textContent = nomeUnidade;
    document.getElementById('historicoConteudo').innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted">Carregando histórico...</p></div>';
    
    const modal = new bootstrap.Modal(document.getElementById('modalHistorico'));
    modal.show();
    
    fetch(`/superintendente/distribuicao/historico?unidade_id=${unidadeId}&ano=<?= $ano ?>`)
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                document.getElementById('historicoConteudo').innerHTML = '<div class="alert alert-danger">Erro ao carregar histórico</div>';
                return;
            }
            
            if (data.historico.length === 0) {
                document.getElementById('historicoConteudo').innerHTML = '<div class="alert alert-info"><i class="bi bi-info-circle me-2"></i>Nenhum registro de aporte encontrado para esta unidade em ' + data.ano + '.</div>';
                return;
            }
            
            let html = '<table class="table table-sm table-striped">';
            html += '<thead><tr><th>Data/Hora</th><th>Tipo</th><th>Valor Anterior</th><th>Valor Novo</th><th>Diferença</th></tr></thead><tbody>';
            
            data.historico.forEach(h => {
                const dataFormatada = new Date(h.created_at).toLocaleString('pt-BR');
                const tipoLabel = h.tipo === 'adicao' ? '<span class="badge bg-success">Adição</span>' : '<span class="badge bg-warning text-dark">Edição</span>';
                const valorAnterior = parseFloat(h.valor_anterior) || 0;
                const valorNovo = parseFloat(h.valor_novo) || 0;
                const diferenca = valorNovo - valorAnterior;
                const diferencaClass = diferenca >= 0 ? 'text-success' : 'text-danger';
                const diferencaPrefix = diferenca >= 0 ? '+' : '';
                
                html += '<tr>';
                html += '<td>' + dataFormatada + '</td>';
                html += '<td>' + tipoLabel + '</td>';
                html += '<td>R$ ' + valorAnterior.toLocaleString('pt-BR', {minimumFractionDigits: 2}) + '</td>';
                html += '<td>R$ ' + valorNovo.toLocaleString('pt-BR', {minimumFractionDigits: 2}) + '</td>';
                html += '<td class="' + diferencaClass + ' fw-bold">' + diferencaPrefix + 'R$ ' + diferenca.toLocaleString('pt-BR', {minimumFractionDigits: 2}) + '</td>';
                html += '</tr>';
            });
            
            html += '</tbody></table>';
            document.getElementById('historicoConteudo').innerHTML = html;
        })
        .catch(err => {
            document.getElementById('historicoConteudo').innerHTML = '<div class="alert alert-danger">Erro ao carregar histórico: ' + err.message + '</div>';
        });
}
</script>

<div class="modal fade" id="modalHistorico" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="bi bi-clock-history me-2"></i>Histórico de Aportes - <span id="historicoUnidadeNome"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="historicoConteudo">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>
