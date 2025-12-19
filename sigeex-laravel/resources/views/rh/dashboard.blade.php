@extends('layouts.app')

@section('title', 'RH - Dashboard')
@section('header', 'Dashboard do RH')

@section('sidebar')
    <a href="/rh" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="/rh/escalas" class="nav-link"><i class="bi bi-calendar-check"></i> Escalas</a>
    <a href="/rh/relatorios" class="nav-link"><i class="bi bi-file-earmark-bar-graph"></i> Relatórios</a>
@endsection

@section('content')
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card card-stat h-100 border-start border-4 border-warning">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="bg-warning bg-opacity-10 rounded-3 p-3 me-3">
                        <i class="bi bi-hourglass-split text-warning fs-4"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Pendentes</h6>
                        <h3 class="mb-0">{{ $pendentes }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-stat h-100 border-start border-4 border-success">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="bg-success bg-opacity-10 rounded-3 p-3 me-3">
                        <i class="bi bi-check-circle text-success fs-4"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Aprovadas</h6>
                        <h3 class="mb-0">{{ $aprovadas }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-stat h-100 border-start border-4 border-info">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="bg-info bg-opacity-10 rounded-3 p-3 me-3">
                        <i class="bi bi-cash-stack text-info fs-4"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Executadas</h6>
                        <h3 class="mb-0">{{ $executadas }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-calendar-check me-2"></i>Escalas {{ $ano }}</h5>
        <a href="/rh/escalas" class="btn btn-primary">Ver Todas</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Unidade</th>
                        <th>Mês/Ano</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($escalas->take(10) as $escala)
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
                        <td>
                            <a href="/rh/escala/{{ $escala->id }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> Detalhar
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted">Nenhuma escala encontrada</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
