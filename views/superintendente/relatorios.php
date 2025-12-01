<div class="row g-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body text-center p-5">
                <div class="mb-4">
                    <i class="bi bi-building text-primary" style="font-size: 4rem;"></i>
                </div>
                <h4>Gastos por Unidade</h4>
                <p class="text-muted">Relatório detalhado de gastos e horas por unidade prisional</p>
                <a href="/rh/relatorios?tipo=gastos" class="btn btn-primary btn-lg">
                    <i class="bi bi-file-earmark-bar-graph me-2"></i>Gerar Relatório
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body text-center p-5">
                <div class="mb-4">
                    <i class="bi bi-calendar3 text-success" style="font-size: 4rem;"></i>
                </div>
                <h4>Análise Trimestral</h4>
                <p class="text-muted">Comparativo de desempenho trimestre a trimestre</p>
                <a href="/rh/relatorios?tipo=trimestral" class="btn btn-success btn-lg">
                    <i class="bi bi-graph-up me-2"></i>Gerar Relatório
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body text-center p-5">
                <div class="mb-4">
                    <i class="bi bi-cash-coin text-warning" style="font-size: 4rem;"></i>
                </div>
                <h4>Execução Orçamentária</h4>
                <p class="text-muted">Acompanhamento da execução do orçamento anual</p>
                <a href="/rh/relatorios?tipo=execucao" class="btn btn-warning btn-lg">
                    <i class="bi bi-pie-chart me-2"></i>Gerar Relatório
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body text-center p-5">
                <div class="mb-4">
                    <i class="bi bi-file-earmark-spreadsheet text-info" style="font-size: 4rem;"></i>
                </div>
                <h4>Exportar Dados</h4>
                <p class="text-muted">Exportar dados em formato Excel ou PDF</p>
                <div class="btn-group">
                    <a href="/rh/relatorios/gerar?formato=excel" class="btn btn-info btn-lg">
                        <i class="bi bi-file-earmark-excel me-2"></i>Excel
                    </a>
                    <a href="/rh/relatorios/gerar?formato=pdf" class="btn btn-danger btn-lg">
                        <i class="bi bi-file-earmark-pdf me-2"></i>PDF
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
