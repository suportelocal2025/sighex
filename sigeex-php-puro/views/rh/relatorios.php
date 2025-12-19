<div class="row g-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Relatório de Horas Aprovadas</h5>
            </div>
            <div class="card-body">
                <form action="/rh/relatorios/gerar" method="GET">
                    <input type="hidden" name="tipo" value="horas">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Ano</label>
                            <select name="ano" class="form-select">
                                <?php for ($y = date('Y') - 2; $y <= date('Y'); $y++): ?>
                                    <option value="<?= $y ?>" <?= $y == $ano ? 'selected' : '' ?>><?= $y ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mês</label>
                            <select name="mes" class="form-select">
                                <option value="todos">Todos</option>
                                <?php 
                                $meses = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 
                                          'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
                                foreach ($meses as $i => $m): ?>
                                    <option value="<?= $i + 1 ?>"><?= $m ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Unidade</label>
                            <select name="unidade_id" class="form-select">
                                <option value="todas">Todas</option>
                                <?php foreach ($unidades as $u): ?>
                                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Formato</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="formato" id="horasHtml" value="html" checked>
                                <label class="btn btn-outline-primary" for="horasHtml"><i class="bi bi-display me-2"></i>Tela</label>
                                
                                <input type="radio" class="btn-check" name="formato" id="horasCsv" value="csv">
                                <label class="btn btn-outline-primary" for="horasCsv"><i class="bi bi-filetype-csv me-2"></i>CSV</label>
                                
                                <input type="radio" class="btn-check" name="formato" id="horasExcel" value="excel">
                                <label class="btn btn-outline-primary" for="horasExcel"><i class="bi bi-file-earmark-excel me-2"></i>Excel</label>
                                
                                <input type="radio" class="btn-check" name="formato" id="horasPdf" value="pdf">
                                <label class="btn btn-outline-primary" for="horasPdf"><i class="bi bi-file-earmark-pdf me-2"></i>PDF</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-file-earmark-bar-graph me-2"></i>Gerar Relatório
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-cash-stack me-2"></i>Relatório de Valores Executados</h5>
            </div>
            <div class="card-body">
                <form action="/rh/relatorios/gerar" method="GET">
                    <input type="hidden" name="tipo" value="valores">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Ano</label>
                            <select name="ano" class="form-select">
                                <?php for ($y = date('Y') - 2; $y <= date('Y'); $y++): ?>
                                    <option value="<?= $y ?>" <?= $y == $ano ? 'selected' : '' ?>><?= $y ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mês</label>
                            <select name="mes" class="form-select">
                                <option value="todos">Todos</option>
                                <?php foreach ($meses as $i => $m): ?>
                                    <option value="<?= $i + 1 ?>"><?= $m ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Unidade</label>
                            <select name="unidade_id" class="form-select">
                                <option value="todas">Todas</option>
                                <?php foreach ($unidades as $u): ?>
                                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Formato</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="formato" id="valoresHtml" value="html" checked>
                                <label class="btn btn-outline-success" for="valoresHtml"><i class="bi bi-display me-2"></i>Tela</label>
                                
                                <input type="radio" class="btn-check" name="formato" id="valoresCsv" value="csv">
                                <label class="btn btn-outline-success" for="valoresCsv"><i class="bi bi-filetype-csv me-2"></i>CSV</label>
                                
                                <input type="radio" class="btn-check" name="formato" id="valoresExcel" value="excel">
                                <label class="btn btn-outline-success" for="valoresExcel"><i class="bi bi-file-earmark-excel me-2"></i>Excel</label>
                                
                                <input type="radio" class="btn-check" name="formato" id="valoresPdf" value="pdf">
                                <label class="btn btn-outline-success" for="valoresPdf"><i class="bi bi-file-earmark-pdf me-2"></i>PDF</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-file-earmark-bar-graph me-2"></i>Gerar Relatório
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
