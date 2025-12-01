<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card stat-card bg-primary text-white">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-white bg-opacity-20 text-white me-3">
                    <i class="bi bi-building"></i>
                </div>
                <div>
                    <div class="stat-value"><?= $totalUnidades ?></div>
                    <div class="stat-label text-white-50">Unidades Cadastradas</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card bg-success text-white">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-white bg-opacity-20 text-white me-3">
                    <i class="bi bi-people"></i>
                </div>
                <div>
                    <div class="stat-value"><?= $totalServidores ?></div>
                    <div class="stat-label text-white-50">Servidores Cadastrados</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card bg-info text-white">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-white bg-opacity-20 text-white me-3">
                    <i class="bi bi-person-check"></i>
                </div>
                <div>
                    <div class="stat-value"><?= $servidoresAtivos ?></div>
                    <div class="stat-label text-white-50">Ativos na Extra</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body text-center p-5">
                <div class="mb-4">
                    <i class="bi bi-building text-primary" style="font-size: 4rem;"></i>
                </div>
                <h4>Gestão de Unidades</h4>
                <p class="text-muted">Cadastrar, editar e gerenciar unidades prisionais</p>
                <a href="/admin/unidades" class="btn btn-primary btn-lg">
                    <i class="bi bi-arrow-right me-2"></i>Acessar
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body text-center p-5">
                <div class="mb-4">
                    <i class="bi bi-person-badge text-success" style="font-size: 4rem;"></i>
                </div>
                <h4>Gestão de Servidores</h4>
                <p class="text-muted">Cadastrar, editar e gerenciar servidores/policiais penais</p>
                <a href="/admin/servidores" class="btn btn-success btn-lg">
                    <i class="bi bi-arrow-right me-2"></i>Acessar
                </a>
            </div>
        </div>
    </div>
</div>
