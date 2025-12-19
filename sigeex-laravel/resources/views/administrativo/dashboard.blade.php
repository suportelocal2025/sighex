@extends('layouts.app')

@section('title', 'Administrativo - Dashboard')
@section('header', 'Painel Administrativo')

@section('sidebar')
    <a href="/admin" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="/admin/unidades" class="nav-link"><i class="bi bi-building"></i> Unidades</a>
    <a href="/admin/servidores" class="nav-link"><i class="bi bi-people"></i> Servidores</a>
    <a href="/admin/usuarios" class="nav-link"><i class="bi bi-person-gear"></i> Usuários</a>
@endsection

@section('content')
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card card-stat h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 rounded-3 p-3 me-3">
                        <i class="bi bi-building text-primary fs-4"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Total Unidades</h6>
                        <h4 class="mb-0">{{ $unidades }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-stat h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="bg-success bg-opacity-10 rounded-3 p-3 me-3">
                        <i class="bi bi-check-circle text-success fs-4"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Unidades Ativas</h6>
                        <h4 class="mb-0">{{ $unidadesAtivas }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-stat h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="bg-info bg-opacity-10 rounded-3 p-3 me-3">
                        <i class="bi bi-people text-info fs-4"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Servidores</h6>
                        <h4 class="mb-0">{{ $servidores }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-stat h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="bg-warning bg-opacity-10 rounded-3 p-3 me-3">
                        <i class="bi bi-person-gear text-warning fs-4"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Usuários</h6>
                        <h4 class="mb-0">{{ $usuarios }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-building me-2"></i>Unidades</h5>
                <a href="/admin/unidades" class="btn btn-sm btn-primary">Gerenciar</a>
            </div>
            <div class="card-body">
                <p class="text-muted">Cadastre e gerencie as unidades prisionais do sistema.</p>
                <a href="/admin/unidade" class="btn btn-outline-primary">
                    <i class="bi bi-plus-circle"></i> Nova Unidade
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-people me-2"></i>Servidores</h5>
                <a href="/admin/servidores" class="btn btn-sm btn-primary">Gerenciar</a>
            </div>
            <div class="card-body">
                <p class="text-muted">Cadastre e gerencie os policiais penais do sistema.</p>
                <a href="/admin/servidores" class="btn btn-outline-primary">
                    <i class="bi bi-plus-circle"></i> Novo Servidor
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
