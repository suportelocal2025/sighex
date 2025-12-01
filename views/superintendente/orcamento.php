<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="bi bi-wallet2 me-2"></i>Configuração do Orçamento Anual</h5>
            </div>
            <div class="card-body p-4">
                <form action="/superintendente/orcamento/salvar" method="POST">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Ano</label>
                            <select name="ano" class="form-select form-select-lg" onchange="window.location.href='?ano='+this.value">
                                <?php for ($y = date('Y') - 2; $y <= date('Y') + 1; $y++): ?>
                                    <option value="<?= $y ?>" <?= $y == $ano ? 'selected' : '' ?>><?= $y ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Valor Total Anual (R$)</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text">R$</span>
                                <input type="text" name="valor_total" class="form-control" 
                                       value="<?= number_format($orcamento['valor_total'] ?? 0, 2, ',', '.') ?>"
                                       placeholder="0,00" id="valorTotal">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Percentual de Reserva Técnica (%)</label>
                            <div class="input-group input-group-lg">
                                <input type="number" name="percentual_reserva" class="form-control" 
                                       min="0" max="100" step="0.1"
                                       value="<?= $orcamento['percentual_reserva'] ?? 10 ?>"
                                       id="percentualReserva">
                                <span class="input-group-text">%</span>
                            </div>
                            <div class="form-text">Valor que será reservado: <strong id="valorReserva">R$ <?= number_format($reservaTecnica, 2, ',', '.') ?></strong></div>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Valor Disponível para Distribuição</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-success text-white">R$</span>
                                <input type="text" class="form-control bg-light" 
                                       value="<?= number_format($valorDisponivel, 2, ',', '.') ?>"
                                       id="valorDisponivel" readonly>
                            </div>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="d-flex justify-content-end gap-2">
                        <a href="/" class="btn btn-outline-secondary btn-lg">
                            <i class="bi bi-arrow-left me-2"></i>Voltar
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-lg me-2"></i>Salvar Orçamento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function parseValor(str) {
    return parseFloat(str.replace(/\./g, '').replace(',', '.')) || 0;
}

function formatValor(num) {
    return num.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

function calcular() {
    const total = parseValor(document.getElementById('valorTotal').value);
    const perc = parseFloat(document.getElementById('percentualReserva').value) || 0;
    const reserva = (total * perc) / 100;
    const disponivel = total - reserva;
    
    document.getElementById('valorReserva').textContent = 'R$ ' + formatValor(reserva);
    document.getElementById('valorDisponivel').value = formatValor(disponivel);
}

document.getElementById('valorTotal').addEventListener('input', calcular);
document.getElementById('percentualReserva').addEventListener('input', calcular);
</script>
