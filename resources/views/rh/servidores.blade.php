@extends('layouts.app')

@section('title', 'RH - Servidores')
@section('header', 'Gerenciamento de Servidores')

@section('sidebar')
    <a href="/rh" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="/rh/escalas" class="nav-link"><i class="bi bi-calendar-check"></i> Escalas</a>
    <a href="/rh/servidores" class="nav-link active"><i class="bi bi-people"></i> Servidores</a>
    <a href="/rh/relatorios" class="nav-link"><i class="bi bi-file-earmark-bar-graph"></i> Relatórios</a>
@endsection

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-semibold"><i class="bi bi-search me-2 text-primary"></i>Buscar Servidores</h5>
        <a href="/rh/solicitacoes-servidores" class="btn btn-outline-primary position-relative">
            <i class="bi bi-person-plus me-1"></i> Solicitações
            @if($solicitacoesPendentes > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                {{ $solicitacoesPendentes }}
            </span>
            @endif
        </a>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-8">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" id="termoBusca" class="form-control" placeholder="Digite matrícula ou nome (mínimo 3 caracteres)...">
                </div>
            </div>
            <div class="col-md-4">
                <button type="button" class="btn btn-primary w-100" onclick="buscarServidores()">
                    <i class="bi bi-search"></i> Buscar
                </button>
            </div>
        </div>
        
        <div id="resultados" class="table-responsive" style="display: none;">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Matrícula</th>
                        <th>Nome</th>
                        <th>Unidade</th>
                        <th>Cargo</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Escala Extra</th>
                        <th class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody id="tabelaServidores">
                </tbody>
            </table>
        </div>
        
        <div id="semResultados" class="text-center py-4 text-muted" style="display: none;">
            <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
            Nenhum servidor encontrado
        </div>
    </div>
</div>

<!-- Modal Editar Status -->
<div class="modal fade" id="editarStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Editar Status do Servidor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="/rh/servidores/alterar-status" method="POST">
                @csrf
                <input type="hidden" name="servidor_id" id="servidorId">
                <div class="modal-body">
                    <p class="fw-bold mb-3" id="servidorInfo"></p>
                    
                    <div class="mb-3">
                        <label class="form-label">Status Geral</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="ativo" id="statusAtivo" value="1">
                            <label class="btn btn-outline-success" for="statusAtivo">Ativo</label>
                            <input type="radio" class="btn-check" name="ativo" id="statusInativo" value="0">
                            <label class="btn btn-outline-danger" for="statusInativo">Inativo</label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Escala Extraordinária</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="apto_escala_extra" id="escalaApto" value="1">
                            <label class="btn btn-outline-success" for="escalaApto">Apto</label>
                            <input type="radio" class="btn-check" name="apto_escala_extra" id="escalaInapto" value="0">
                            <label class="btn btn-outline-danger" for="escalaInapto">Inapto</label>
                        </div>
                    </div>
                    
                    <div id="camposInatividade" style="display: none;">
                        <hr>
                        <h6 class="text-muted mb-3">Configurações de Inatividade</h6>
                        
                        <div class="mb-3">
                            <label class="form-label">Motivo da Inatividade</label>
                            <select name="motivo_inativo" id="motivoInativo" class="form-select">
                                <option value="">Selecione...</option>
                                <option value="Férias">Férias</option>
                                <option value="Licença Médica">Licença Médica</option>
                                <option value="Licença Prêmio">Licença Prêmio</option>
                                <option value="Afastamento">Afastamento</option>
                                <option value="Outro">Outro</option>
                            </select>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" name="inativo_indefinido" id="inativoIndefinido" value="1">
                            <label class="form-check-label" for="inativoIndefinido">Inativo por tempo indeterminado</label>
                        </div>
                        
                        <div id="camposPeriodo">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Data Início</label>
                                    <input type="date" name="inativo_inicio" id="inativoInicio" class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Data Fim</label>
                                    <input type="date" name="inativo_fim" id="inativoFim" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function buscarServidores() {
    const termo = document.getElementById('termoBusca').value;
    
    if (termo.length < 3) {
        alert('Digite pelo menos 3 caracteres para buscar');
        return;
    }
    
    fetch('/rh/servidores/buscar?termo=' + encodeURIComponent(termo))
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('tabelaServidores');
            const resultados = document.getElementById('resultados');
            const semResultados = document.getElementById('semResultados');
            
            if (data.length === 0) {
                resultados.style.display = 'none';
                semResultados.style.display = 'block';
                return;
            }
            
            semResultados.style.display = 'none';
            resultados.style.display = 'block';
            
            tbody.innerHTML = data.map(s => `
                <tr>
                    <td><strong>${s.matricula}</strong></td>
                    <td>${s.nome}</td>
                    <td>${s.unidade?.nome || '-'}</td>
                    <td>${s.cargo || '-'}</td>
                    <td class="text-center">
                        <span class="badge ${s.ativo ? 'bg-success' : 'bg-danger'}">
                            ${s.ativo ? 'Ativo' : 'Inativo'}
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="badge ${s.apto_escala_extra ? 'bg-success' : 'bg-secondary'}">
                            ${s.apto_escala_extra ? 'Apto' : 'Inapto'}
                        </span>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                onclick="abrirModalStatus(${s.id}, '${s.matricula}', '${s.nome.replace(/'/g, "\\'")}', ${s.ativo ? 1 : 0}, ${s.apto_escala_extra ? 1 : 0}, '${s.motivo_inativo || ''}', ${s.inativo_indefinido ? 1 : 0}, '${s.inativo_inicio || ''}', '${s.inativo_fim || ''}')">
                            <i class="bi bi-pencil"></i> Editar Status
                        </button>
                    </td>
                </tr>
            `).join('');
        });
}

function abrirModalStatus(id, matricula, nome, ativo, aptoEscala, motivo, indefinido, inicio, fim) {
    document.getElementById('servidorId').value = id;
    document.getElementById('servidorInfo').textContent = `${matricula} - ${nome}`;
    
    if (ativo) {
        document.getElementById('statusAtivo').checked = true;
    } else {
        document.getElementById('statusInativo').checked = true;
    }
    
    if (aptoEscala) {
        document.getElementById('escalaApto').checked = true;
    } else {
        document.getElementById('escalaInapto').checked = true;
    }
    
    document.getElementById('motivoInativo').value = motivo;
    document.getElementById('inativoIndefinido').checked = indefinido;
    
    if (inicio) {
        document.getElementById('inativoInicio').value = inicio.split('T')[0];
    }
    if (fim) {
        document.getElementById('inativoFim').value = fim.split('T')[0];
    }
    
    toggleCamposInatividade();
    toggleCamposPeriodo();
    
    new bootstrap.Modal(document.getElementById('editarStatusModal')).show();
}

function toggleCamposInatividade() {
    const inativo = document.getElementById('statusInativo').checked;
    document.getElementById('camposInatividade').style.display = inativo ? 'block' : 'none';
}

function toggleCamposPeriodo() {
    const indefinido = document.getElementById('inativoIndefinido').checked;
    document.getElementById('camposPeriodo').style.display = indefinido ? 'none' : 'block';
}

document.getElementById('statusAtivo').addEventListener('change', toggleCamposInatividade);
document.getElementById('statusInativo').addEventListener('change', toggleCamposInatividade);
document.getElementById('inativoIndefinido').addEventListener('change', toggleCamposPeriodo);

document.getElementById('termoBusca').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        buscarServidores();
    }
});
</script>
@endsection
