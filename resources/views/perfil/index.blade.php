@extends('layouts.app')

@section('title', 'Meu Perfil')

@section('sidebar')
@php $papel = Auth::user()->papel; @endphp
@if($papel === 'superintendente')
    <a href="/superintendente" class="nav-link"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
    <a href="/superintendente/orcamento" class="nav-link"><i class="bi bi-cash-coin me-2"></i>Orçamento</a>
    <a href="/superintendente/distribuicao" class="nav-link"><i class="bi bi-diagram-3 me-2"></i>Distribuição</a>
    <a href="/superintendente/escalas" class="nav-link"><i class="bi bi-calendar-check me-2"></i>Escalas</a>
    <a href="/superintendente/alertas" class="nav-link"><i class="bi bi-bell me-2"></i>Alertas</a>
@elseif($papel === 'diretor')
    <a href="/diretor" class="nav-link"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
    <a href="/diretor/escala-mensal" class="nav-link"><i class="bi bi-calendar-week me-2"></i>Escala Mensal</a>
    <a href="/diretor/servidores" class="nav-link"><i class="bi bi-people me-2"></i>Servidores</a>
    <a href="/diretor/alertas" class="nav-link"><i class="bi bi-bell me-2"></i>Alertas</a>
@elseif($papel === 'rh')
    <a href="/rh" class="nav-link"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
    <a href="/rh/escalas" class="nav-link"><i class="bi bi-calendar-check me-2"></i>Escalas</a>
    <a href="/rh/relatorios" class="nav-link"><i class="bi bi-bar-chart me-2"></i>Relatórios</a>
@elseif($papel === 'administrativo')
    <a href="/admin" class="nav-link"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
    <a href="/admin/unidades" class="nav-link"><i class="bi bi-building me-2"></i>Unidades</a>
    <a href="/admin/servidores" class="nav-link"><i class="bi bi-people me-2"></i>Servidores</a>
    <a href="/admin/usuarios" class="nav-link"><i class="bi bi-person-gear me-2"></i>Usuários</a>
@endif
@endsection

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h2><i class="bi bi-person-circle me-2"></i>Meu Perfil</h2>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-person-vcard me-2"></i>Dados Pessoais</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="/perfil/atualizar">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Nome Completo</label>
                            <input type="text" name="nome" class="form-control" value="{{ $usuario->nome }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ $usuario->email }}" required>
                            <small class="text-muted">Este email será usado para receber alertas do sistema.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Telefone</label>
                            <input type="text" name="telefone" class="form-control" value="{{ $usuario->telefone ?? '' }}" placeholder="(00) 00000-0000">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Matrícula</label>
                            <input type="text" name="matricula" class="form-control" value="{{ $usuario->matricula ?? '' }}" placeholder="Sua matrícula funcional">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Perfil</label>
                            <input type="text" class="form-control" value="{{ ucfirst($usuario->papel) }}" disabled>
                        </div>
                        @if($usuario->unidade)
                        <div class="mb-3">
                            <label class="form-label">Unidade Vinculada</label>
                            <input type="text" class="form-control" value="{{ $usuario->unidade->nome }}" disabled>
                        </div>
                        @endif
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Salvar Alterações
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="bi bi-key me-2"></i>Alterar Senha</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="/perfil/alterar-senha">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Senha Atual</label>
                            <input type="password" name="senha_atual" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nova Senha</label>
                            <input type="password" name="nova_senha" class="form-control" required minlength="6">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirmar Nova Senha</label>
                            <input type="password" name="nova_senha_confirmation" class="form-control" required minlength="6">
                        </div>
                        <button type="submit" class="btn btn-secondary">
                            <i class="bi bi-lock me-1"></i>Alterar Senha
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
