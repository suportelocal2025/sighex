<div class="row justify-content-center">
    <div class="col-lg-10">
        <form action="/admin/unidades/salvar" method="POST">
            <input type="hidden" name="id" value="<?= $unidade['id'] ?? '' ?>">
            
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-building me-2"></i>Dados da Unidade</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Nome da Unidade *</label>
                            <input type="text" name="nome" class="form-control" 
                                   value="<?= htmlspecialchars($unidade['nome'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Local</label>
                            <input type="text" name="local" class="form-control" 
                                   value="<?= htmlspecialchars($unidade['local'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-person me-2"></i>Diretor Responsável</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Nome do Diretor</label>
                            <input type="text" name="responsavel_nome" class="form-control" 
                                   value="<?= htmlspecialchars($responsavel['nome'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Email</label>
                            <input type="email" name="responsavel_email" class="form-control" 
                                   value="<?= htmlspecialchars($responsavel['email'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Senha <?= $unidade ? '(deixe em branco para manter)' : '' ?></label>
                            <input type="password" name="responsavel_senha" class="form-control">
                        </div>
                    </div>
                    <?php if (!$unidade): ?>
                        <div class="alert alert-info mt-3 mb-0">
                            <i class="bi bi-info-circle me-2"></i>
                            Ao criar a unidade, o sistema criará automaticamente 4 equipes padrão (A, B, C, D).
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if ($unidade): ?>
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-diagram-3 me-2"></i>Módulos / Raios / Setores</h5>
                        <span class="badge bg-primary" id="contadorModulos"><?= count($modulos ?? []) ?> cadastrado(s)</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-plus-circle"></i></span>
                                <input type="text" class="form-control" id="novoModuloNome" 
                                       placeholder="Digite o nome do módulo, raio ou setor (ex: Raio 1, Módulo A, Portaria)">
                                <button type="button" class="btn btn-primary" onclick="adicionarModulo()">
                                    <i class="bi bi-plus-lg me-1"></i> Adicionar
                                </button>
                            </div>
                            <small class="text-muted">Pressione Enter ou clique em Adicionar para incluir</small>
                        </div>
                        <div class="col-md-4">
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary dropdown-toggle w-100" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-lightning me-1"></i> Adicionar Rápido
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#" onclick="adicionarMultiplos('Raio', 5); return false;">Raios 1 a 5</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="adicionarMultiplos('Raio', 10); return false;">Raios 1 a 10</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="#" onclick="adicionarMultiplos('Módulo', 4); return false;">Módulos A a D</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="adicionarMultiplos('Módulo', 6); return false;">Módulos A a F</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="#" onclick="adicionarSetoresPadrao(); return false;">Setores Padrão</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (empty($modulos ?? [])): ?>
                    <div class="alert alert-info mb-0" id="alertaSemModulos">
                        <i class="bi bi-info-circle me-2"></i>
                        Nenhum módulo, raio ou setor cadastrado. Adicione usando o campo acima ou o botão "Adicionar Rápido".
                    </div>
                    <?php endif; ?>
                    
                    <div class="row g-2" id="listaModulos">
                        <?php foreach ($modulos ?? [] as $m): ?>
                            <div class="col-md-3 col-sm-4" id="modulo-<?= $m['id'] ?>">
                                <div class="card border">
                                    <div class="card-body py-2 px-3 d-flex justify-content-between align-items-center">
                                        <span class="fw-medium">
                                            <i class="bi bi-geo-alt text-primary me-1"></i>
                                            <?= htmlspecialchars($m['nome']) ?>
                                        </span>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removerModulo(<?= $m['id'] ?>, '<?= htmlspecialchars($m['nome'], ENT_QUOTES) ?>')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-people me-2"></i>Equipes</h5>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <?php foreach ($equipes ?? [] as $e): ?>
                            <div class="col-md-3">
                                <div class="card text-center py-3 border">
                                    <i class="bi bi-people-fill text-primary mb-1" style="font-size: 1.5rem;"></i>
                                    <strong><?= htmlspecialchars($e['nome']) ?></strong>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="d-flex justify-content-end gap-2">
                <a href="/admin/unidades" class="btn btn-outline-secondary btn-lg">
                    <i class="bi bi-arrow-left me-2"></i>Voltar
                </a>
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-check-lg me-2"></i>Salvar
                </button>
            </div>
        </form>
    </div>
</div>

<?php if ($unidade): ?>
<script>
let moduloCount = <?= count($modulos ?? []) ?>;

document.getElementById('novoModuloNome').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        adicionarModulo();
    }
});

function atualizarContador() {
    document.getElementById('contadorModulos').textContent = moduloCount + ' cadastrado(s)';
    const alerta = document.getElementById('alertaSemModulos');
    if (alerta && moduloCount > 0) {
        alerta.style.display = 'none';
    }
}

async function adicionarModuloAPI(nome) {
    const form = new FormData();
    form.append('unidade_id', <?= $unidade['id'] ?>);
    form.append('nome', nome.trim());
    
    const response = await fetch('/admin/modulos/adicionar', { method: 'POST', body: form });
    return await response.json();
}

async function adicionarModulo() {
    const input = document.getElementById('novoModuloNome');
    const nome = input.value.trim();
    
    if (!nome) {
        input.focus();
        return;
    }
    
    const result = await adicionarModuloAPI(nome);
    
    if (result.success) {
        const lista = document.getElementById('listaModulos');
        const div = document.createElement('div');
        div.className = 'col-md-3 col-sm-4';
        div.id = 'modulo-' + result.id;
        div.innerHTML = `
            <div class="card border">
                <div class="card-body py-2 px-3 d-flex justify-content-between align-items-center">
                    <span class="fw-medium">
                        <i class="bi bi-geo-alt text-primary me-1"></i>
                        ${nome}
                    </span>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removerModulo(${result.id}, '${nome.replace(/'/g, "\\'")}')">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        `;
        lista.appendChild(div);
        
        input.value = '';
        input.focus();
        moduloCount++;
        atualizarContador();
    } else {
        alert(result.message || 'Erro ao adicionar');
    }
}

async function removerModulo(id, nome) {
    if (!confirm('Remover o módulo "' + nome + '"?')) return;
    
    const form = new FormData();
    form.append('id', id);
    
    const response = await fetch('/admin/modulos/remover', { method: 'POST', body: form });
    const result = await response.json();
    
    if (result.success) {
        document.getElementById('modulo-' + id).remove();
        moduloCount--;
        atualizarContador();
    } else {
        alert(result.message || 'Erro ao remover');
    }
}

async function adicionarMultiplos(tipo, quantidade) {
    const letras = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
    let adicionados = 0;
    
    for (let i = 0; i < quantidade; i++) {
        let nome;
        if (tipo === 'Raio') {
            nome = 'Raio ' + (i + 1);
        } else {
            nome = 'Módulo ' + letras[i];
        }
        
        const result = await adicionarModuloAPI(nome);
        if (result.success) {
            const lista = document.getElementById('listaModulos');
            const div = document.createElement('div');
            div.className = 'col-md-3 col-sm-4';
            div.id = 'modulo-' + result.id;
            div.innerHTML = `
                <div class="card border">
                    <div class="card-body py-2 px-3 d-flex justify-content-between align-items-center">
                        <span class="fw-medium">
                            <i class="bi bi-geo-alt text-primary me-1"></i>
                            ${nome}
                        </span>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removerModulo(${result.id}, '${nome}')">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            `;
            lista.appendChild(div);
            adicionados++;
            moduloCount++;
        }
    }
    
    atualizarContador();
    if (adicionados > 0) {
        alert(adicionados + ' módulo(s) adicionado(s) com sucesso!');
    }
}

async function adicionarSetoresPadrao() {
    const setores = ['Portaria', 'Administração', 'Enfermaria', 'Cozinha', 'Pátio', 'Oficina'];
    let adicionados = 0;
    
    for (const nome of setores) {
        const result = await adicionarModuloAPI(nome);
        if (result.success) {
            const lista = document.getElementById('listaModulos');
            const div = document.createElement('div');
            div.className = 'col-md-3 col-sm-4';
            div.id = 'modulo-' + result.id;
            div.innerHTML = `
                <div class="card border">
                    <div class="card-body py-2 px-3 d-flex justify-content-between align-items-center">
                        <span class="fw-medium">
                            <i class="bi bi-geo-alt text-primary me-1"></i>
                            ${nome}
                        </span>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removerModulo(${result.id}, '${nome}')">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            `;
            lista.appendChild(div);
            adicionados++;
            moduloCount++;
        }
    }
    
    atualizarContador();
    if (adicionados > 0) {
        alert(adicionados + ' setor(es) adicionado(s) com sucesso!');
    }
}
</script>
<?php endif; ?>
