@extends('layouts.app')

@section('title', 'Administrativo - Vincular Servidores a Modulos/Equipes')
@section('header', 'Vincular Servidores a Modulos/Equipes')

@section('sidebar')
    <a href="/admin" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="/admin/unidades" class="nav-link"><i class="bi bi-building"></i> Unidades</a>
    <a href="/admin/servidores" class="nav-link"><i class="bi bi-people"></i> Servidores</a>
    <a href="/admin/usuarios" class="nav-link"><i class="bi bi-person-badge"></i> Usuarios</a>
    <a href="/admin/vinculos-modulo-equipe" class="nav-link active"><i class="bi bi-link-45deg"></i> Vinculos Modulo/Equipe</a>
@endsection

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('warning'))
<div class="alert alert-warning alert-dismissible fade show">
    {{ session('warning') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="bi bi-link-45deg me-2"></i>Vincular Servidores a Modulos e Equipes</h5>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Como funciona:</strong> Vincule servidores a combinacoes especificas de modulo e equipe. 
            Por exemplo, vincule "Servidor X" a "Equipe A" do "Raio 1". Assim, quando o diretor montar a escala 
            e selecionar "Raio 1" + "Equipe A", apenas os servidores vinculados a essa combinacao aparecerao.
        </div>
        
        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-4">
                <label class="form-label">1. Selecione a Unidade</label>
                <select name="unidade_id" class="form-select" onchange="this.form.submit()">
                    <option value="">Escolha uma unidade...</option>
                    @foreach($unidades as $u)
                        <option value="{{ $u->id }}" {{ ($unidadeSelecionada?->id ?? '') == $u->id ? 'selected' : '' }}>
                            {{ $u->nome }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            @if($unidadeSelecionada)
            <div class="col-md-4">
                <label class="form-label">2. Selecione o Modulo</label>
                <select name="modulo_id" class="form-select" onchange="this.form.submit()">
                    <option value="">Escolha um modulo...</option>
                    @foreach($unidadeSelecionada->modulos as $m)
                        <option value="{{ $m->id }}" {{ $moduloId == $m->id ? 'selected' : '' }}>
                            {{ $m->nome }}
                        </option>
                    @endforeach
                </select>
                <input type="hidden" name="unidade_id" value="{{ $unidadeSelecionada->id }}">
            </div>
            @endif
            
            @if($unidadeSelecionada && $moduloId)
            <div class="col-md-4">
                <label class="form-label">3. Selecione a Equipe</label>
                <select name="equipe_id" class="form-select" onchange="this.form.submit()">
                    <option value="">Escolha uma equipe...</option>
                    @foreach($unidadeSelecionada->equipes as $e)
                        <option value="{{ $e->id }}" {{ $equipeId == $e->id ? 'selected' : '' }}>
                            {{ $e->nome }}
                        </option>
                    @endforeach
                </select>
                <input type="hidden" name="unidade_id" value="{{ $unidadeSelecionada->id }}">
                <input type="hidden" name="modulo_id" value="{{ $moduloId }}">
            </div>
            @endif
        </form>
    </div>
</div>

@if($unidadeSelecionada && $moduloId && $equipeId)
<div class="row">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0"><i class="bi bi-person-plus me-2"></i>Adicionar Servidor ao Vinculo</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="/admin/vinculo-modulo-equipe">
                    @csrf
                    <input type="hidden" name="modulo_id" value="{{ $moduloId }}">
                    <input type="hidden" name="equipe_id" value="{{ $equipeId }}">
                    
                    <div class="mb-3">
                        <label class="form-label">Servidor</label>
                        <select name="servidor_id" class="form-select" required>
                            <option value="">Selecione um servidor...</option>
                            @php
                                $vinculadosIds = $vinculos->pluck('servidor_id')->toArray();
                            @endphp
                            @foreach($unidadeSelecionada->servidores as $s)
                                @if(!in_array($s->id, $vinculadosIds))
                                <option value="{{ $s->id }}">{{ $s->nome }} ({{ $s->matricula }})</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-success w-100">
                        <i class="bi bi-plus-circle me-1"></i> Adicionar Vinculo
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-secondary text-white">
                <h6 class="mb-0"><i class="bi bi-people me-2"></i>Servidores Vinculados ({{ $vinculos->count() }})</h6>
            </div>
            <div class="card-body">
                @if($vinculos->count() > 0)
                <ul class="list-group">
                    @foreach($vinculos as $v)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong>{{ $v->servidor->nome ?? 'N/A' }}</strong>
                            <br><small class="text-muted">{{ $v->servidor->matricula ?? '' }}</small>
                        </div>
                        <form method="POST" action="/admin/vinculo-modulo-equipe/{{ $v->id }}" onsubmit="return confirm('Remover vinculo?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </li>
                    @endforeach
                </ul>
                @else
                <div class="text-center text-muted py-4">
                    <i class="bi bi-inbox h1"></i>
                    <p>Nenhum servidor vinculado a esta combinacao.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endif

@endsection
