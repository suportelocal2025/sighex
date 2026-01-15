@extends('layouts.app')

@section('title', 'RH - Escalas')
@section('header', 'Gestão de Escalas')

@section('sidebar')
    <a href="/rh" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="/rh/escalas" class="nav-link active"><i class="bi bi-calendar-check"></i> Escalas</a>
    <a href="/rh/servidores" class="nav-link"><i class="bi bi-people"></i> Servidores</a>
    <a href="/rh/relatorios" class="nav-link"><i class="bi bi-file-earmark-bar-graph"></i> Relatórios</a>
@endsection

@section('content')
<div class="card">
    <div class="card-header bg-white">
        <div class="row align-items-center">
            <div class="col">
                <h5 class="mb-0"><i class="bi bi-calendar-check me-2"></i>Escalas - {{ $ano }}</h5>
            </div>
            <div class="col-auto">
                <div class="btn-group">
                    <a href="/rh/escalas?status=todos" class="btn btn-sm {{ $status === 'todos' ? 'btn-primary' : 'btn-outline-primary' }}">Todas</a>
                    <a href="/rh/escalas?status=pendente" class="btn btn-sm {{ $status === 'pendente' ? 'btn-warning' : 'btn-outline-warning' }}">Pendentes</a>
                    <a href="/rh/escalas?status=aprovada" class="btn btn-sm {{ $status === 'aprovada' ? 'btn-success' : 'btn-outline-success' }}">Aprovadas</a>
                    <a href="/rh/escalas?status=executada" class="btn btn-sm {{ $status === 'executada' ? 'btn-info' : 'btn-outline-info' }}">Executadas</a>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Unidade</th>
                        <th>Mês/Ano</th>
                        <th>Status</th>
                        <th>Data Envio</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($escalas as $escala)
                    <tr>
                        <td>{{ $escala->unidade->nome ?? 'N/A' }}</td>
                        <td>{{ str_pad($escala->mes, 2, '0', STR_PAD_LEFT) }}/{{ $escala->ano }}</td>
                        <td>
                            @switch($escala->status)
                                @case('pendente')
                                    <span class="badge bg-warning text-dark">Pendente</span>
                                    @break
                                @case('aprovada')
                                    <span class="badge bg-success">Aprovada</span>
                                    @break
                                @case('rejeitada')
                                    <span class="badge bg-danger">Rejeitada</span>
                                    @break
                                @case('executada')
                                    <span class="badge bg-info">Executada</span>
                                    @break
                            @endswitch
                        </td>
                        <td>{{ $escala->data_envio ? $escala->data_envio->format('d/m/Y H:i') : '-' }}</td>
                        <td>
                            <a href="/rh/escala/{{ $escala->id }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> Detalhar
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">Nenhuma escala encontrada</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
