<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card bg-primary text-white">
            <div class="stat-value">R$ <?= number_format($valorDisponivel, 2, ',', '.') ?></div>
            <div class="stat-label text-white-50">Orçamento Disponível</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card bg-success text-white">
            <div class="stat-value">R$ <?= number_format($totalDistribuido, 2, ',', '.') ?></div>
            <div class="stat-label text-white-50">Total Distribuído</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card bg-info text-white">
            <div class="stat-value">R$ <?= number_format($saldoRestante, 2, ',', '.') ?></div>
            <div class="stat-label text-white-50">Saldo Restante</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <label class="form-label mb-2">Ano:</label>
            <select class="form-select" onchange="window.location.href='?ano='+this.value">
                <?php for ($y = date('Y') - 2; $y <= date('Y') + 1; $y++): ?>
                    <option value="<?= $y ?>" <?= $y == $ano ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
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
                            <th style="width: 50%">Unidade</th>
                            <th style="width: 30%">Valor a Distribuir (R$)</th>
                            <th style="width: 20%">% do Total</th>
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
</script>
