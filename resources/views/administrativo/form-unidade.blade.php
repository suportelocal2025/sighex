@extends('layouts.app')

@section('title', 'Administrativo - ' . ($unidade ? 'Editar' : 'Nova') . ' Unidade')
@section('header', ($unidade ? 'Editar' : 'Nova') . ' Unidade')

@section('sidebar')
    <a href="/admin" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="/admin/unidades" class="nav-link"><i class="bi bi-building"></i> Unidades</a>
    <a href="/admin/servidores" class="nav-link"><i class="bi bi-people"></i> Servidores</a>
    <a href="/admin/usuarios" class="nav-link"><i class="bi bi-person-gear"></i> Usuários</a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="bi bi-building me-2"></i>
                    {{ $unidade ? 'Editar Unidade' : 'Nova Unidade' }}
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/admin/unidade">
                    @csrf
                    @if($unidade)
                        <input type="hidden" name="id" value="{{ $unidade->id }}">
                    @endif

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Nome da Unidade *</label>
                            <input type="text" name="nome" class="form-control" 
                                   value="{{ old('nome', $unidade->nome ?? '') }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Código *</label>
                            <input type="text" name="codigo" class="form-control" 
                                   value="{{ old('codigo', $unidade->codigo ?? '') }}" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Endereço</label>
                        <input type="text" name="endereco" class="form-control" 
                               value="{{ old('endereco', $unidade->endereco ?? '') }}">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Telefone</label>
                            <input type="text" name="telefone" class="form-control" 
                                   value="{{ old('telefone', $unidade->telefone ?? '') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check mt-4">
                                <input type="checkbox" name="ativo" class="form-check-input" value="1"
                                       {{ old('ativo', $unidade->ativo ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label">Unidade Ativa</label>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between">
                        <a href="/admin/unidades" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Voltar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Salvar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @if($unidade)
        <div class="card mt-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-diagram-2 me-2"></i>Setor/Módulo/Raio</h5>
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalNovoModulo">
                    <i class="bi bi-plus-circle"></i> Novo
                </button>
            </div>
            <div class="card-body">
                @if($unidade->modulos->count())
                <ul class="list-group">
                    @foreach($unidade->modulos as $modulo)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>{{ $modulo->nome }}</span>
                        <div class="d-flex align-items-center gap-2">
                            @if($modulo->ativo)
                                <span class="badge bg-success">Ativo</span>
                            @else
                                <span class="badge bg-secondary">Inativo</span>
                            @endif
                            <form method="POST" action="/admin/modulo/{{ $modulo->id }}/excluir" 
                                  onsubmit="return confirm('Excluir este setor/módulo/raio?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </li>
                    @endforeach
                </ul>
                @else
                <p class="text-muted mb-0">Nenhum setor/módulo/raio cadastrado.</p>
                @endif
            </div>
        </div>

        <div class="modal fade" id="modalNovoModulo" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="/admin/modulo">
                        @csrf
                        <input type="hidden" name="unidade_id" value="{{ $unidade->id }}">
                        <div class="modal-header">
                            <h5 class="modal-title">Novo Setor/Módulo/Raio</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Nome *</label>
                                <input type="text" name="nome" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Descrição</label>
                                <input type="text" name="descricao" class="form-control">
                            </div>
                            <div class="form-check">
                                <input type="checkbox" name="ativo" class="form-check-input" value="1" checked>
                                <label class="form-check-label">Ativo</label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Salvar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
