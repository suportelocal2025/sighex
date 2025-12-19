@extends('layouts.app')

@section('title', 'Administrativo - Unidades')
@section('header', 'Gestão de Unidades')

@section('sidebar')
    <a href="/admin" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="/admin/unidades" class="nav-link"><i class="bi bi-building"></i> Unidades</a>
    <a href="/admin/servidores" class="nav-link"><i class="bi bi-people"></i> Servidores</a>
    <a href="/admin/usuarios" class="nav-link"><i class="bi bi-person-gear"></i> Usuários</a>
@endsection

@section('content')
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-building me-2"></i>Unidades Prisionais</h5>
        <a href="/admin/unidade" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nova Unidade
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nome</th>
                        <th>Servidores</th>
                        <th>Módulos</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($unidades as $unidade)
                    <tr>
                        <td><code>{{ $unidade->codigo }}</code></td>
                        <td>{{ $unidade->nome }}</td>
                        <td>{{ $unidade->servidores_count }}</td>
                        <td>{{ $unidade->modulos_count }}</td>
                        <td>
                            @if($unidade->ativo)
                                <span class="badge bg-success">Ativa</span>
                            @else
                                <span class="badge bg-danger">Inativa</span>
                            @endif
                        </td>
                        <td>
                            <a href="/admin/unidade/{{ $unidade->id }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="/admin/unidade/{{ $unidade->id }}" method="POST" class="d-inline" onsubmit="return confirm('Excluir esta unidade? Isso removerá todos os dados vinculados.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">Nenhuma unidade cadastrada</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
