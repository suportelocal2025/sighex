@extends('layouts.app')

@section('title', 'Diretor - Servidores')
@section('header', 'Servidores da Unidade')

@section('sidebar')
    <a href="/diretor" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="/diretor/escala-mensal" class="nav-link"><i class="bi bi-calendar3"></i> Escala Mensal</a>
    <a href="/diretor/servidores" class="nav-link active"><i class="bi bi-people"></i> Servidores</a>
@endsection

@section('content')
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-people me-2"></i>Servidores Escalados nos Últimos Meses</h5>
        <button type="button" class="btn btn-success" onclick="abrirModalIncluirServidor()">
            <i class="bi bi-person-plus me-1"></i> Incluir Servidor
        </button>
    </div>
    <div class="card-body">
        @if($servidores->isEmpty())
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            Nenhum servidor foi escalado nos últimos 6 meses nesta unidade.
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Matrícula</th>
                        <th>Nome</th>
                        <th>Cargo</th>
                        <th>Escala Extra</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($servidores as $servidor)
                    <tr>
                        <td><code>{{ $servidor->matricula }}</code></td>
                        <td>{{ $servidor->nome }}</td>
                        <td>{{ $servidor->cargo ?? '-' }}</td>
                        <td>
                            @if($servidor->apto_escala_extra)
                                <span class="badge bg-success">Apto</span>
                            @else
                                <span class="badge bg-secondary">Não Apto</span>
                            @endif
                        </td>
                        <td>
                            @if($servidor->ativo)
                                <span class="badge bg-success">Ativo</span>
                            @else
                                <span class="badge bg-danger">Inativo</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

<div class="modal fade" id="modalIncluirServidor" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white border-0">
                <h6 class="modal-title fw-semibold">
                    <i class="bi bi-person-plus me-2"></i>Solicitar Inclusão de Servidor
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-1"></i>
                    Se o servidor não está na lista geral, preencha os dados abaixo para solicitar sua inclusão. 
                    A solicitação será enviada ao RH para aprovação.
                </div>
                <div class="mb-3">
                    <label class="form-label">Matrícula <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="novoServidorMatricula" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Nome Completo <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="novoServidorNome" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Cargo</label>
                    <input type="text" class="form-control" id="novoServidorCargo" placeholder="Ex: Policial Penal">
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" onclick="enviarSolicitacaoServidor()">
                    <i class="bi bi-send me-1"></i> Enviar Solicitação
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

function abrirModalIncluirServidor() {
    document.getElementById('novoServidorMatricula').value = '';
    document.getElementById('novoServidorNome').value = '';
    document.getElementById('novoServidorCargo').value = '';
    new bootstrap.Modal(document.getElementById('modalIncluirServidor')).show();
}

async function enviarSolicitacaoServidor() {
    const matricula = document.getElementById('novoServidorMatricula').value.trim();
    const nome = document.getElementById('novoServidorNome').value.trim();
    const cargo = document.getElementById('novoServidorCargo').value.trim();
    
    if (!matricula || !nome) {
        alert('Preencha a matrícula e o nome do servidor.');
        return;
    }
    
    const form = new FormData();
    form.append('matricula', matricula);
    form.append('nome', nome);
    form.append('cargo', cargo);
    
    try {
        const response = await fetch('/diretor/solicitar-inclusao-servidor', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            body: form
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert(data.message);
            bootstrap.Modal.getInstance(document.getElementById('modalIncluirServidor'))?.hide();
        } else {
            alert(data.message || 'Erro ao enviar solicitação.');
        }
    } catch (error) {
        alert('Erro ao enviar solicitação. Tente novamente.');
    }
}
</script>
@endpush
