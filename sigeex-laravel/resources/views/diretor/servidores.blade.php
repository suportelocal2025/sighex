@extends('layouts.app')

@section('title', 'Diretor - Servidores')
@section('header', 'Servidores da Unidade')

@section('sidebar')
    <a href="/diretor" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="/diretor/escala-mensal" class="nav-link"><i class="bi bi-calendar3"></i> Escala Mensal</a>
    <a href="/diretor/servidores" class="nav-link"><i class="bi bi-people"></i> Servidores</a>
@endsection

@section('content')
<div class="card">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="bi bi-people me-2"></i>Servidores Disponíveis para Escala Extra</h5>
    </div>
    <div class="card-body">
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
                    @forelse($servidores as $servidor)
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
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">Nenhum servidor cadastrado</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
