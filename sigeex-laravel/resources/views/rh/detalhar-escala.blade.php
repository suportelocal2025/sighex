@extends('layouts.app')

@section('title', 'RH - Detalhar Escala')
@section('header', 'Detalhes da Escala')

@section('sidebar')
    <a href="/rh" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="/rh/escalas" class="nav-link"><i class="bi bi-calendar-check"></i> Escalas</a>
    <a href="/rh/relatorios" class="nav-link"><i class="bi bi-file-earmark-bar-graph"></i> Relatórios</a>
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h4>{{ $escala->unidade->nome ?? 'N/A' }}</h4>
        <p class="text-muted">
            Escala: {{ str_pad($escala->mes, 2, '0', STR_PAD_LEFT) }}/{{ $escala->ano }} |
            Status: 
            <span class="badge bg-{{ $escala->status === 'pendente' ? 'warning' : ($escala->status === 'aprovada' ? 'success' : ($escala->status === 'executada' ? 'info' : 'danger')) }}">
                {{ ucfirst($escala->status) }}
            </span>
        </p>
    </div>
    <div class="col-md-4 text-end">
        <a href="/rh/escalas" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>
</div>

@if($escala->status === 'rejeitada' && $escala->motivo_rejeicao)
<div class="alert alert-danger">
    <strong>Motivo da Rejeição:</strong> {{ $escala->motivo_rejeicao }}
</div>
@endif

<div class="row g-4 mb-4">
    @if($escala->status === 'pendente')
    <div class="col-md-6">
        <div class="card border-success">
            <div class="card-body">
                <h5 class="text-success"><i class="bi bi-check-circle"></i> Aprovar Escala</h5>
                <form method="POST" action="/rh/aprovar" onsubmit="return confirm('Confirma aprovação desta escala?')">
                    @csrf
                    <input type="hidden" name="escala_id" value="{{ $escala->id }}">
                    <button type="submit" class="btn btn-success">Aprovar</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-danger">
            <div class="card-body">
                <h5 class="text-danger"><i class="bi bi-x-circle"></i> Rejeitar Escala</h5>
                <form method="POST" action="/rh/rejeitar">
                    @csrf
                    <input type="hidden" name="escala_id" value="{{ $escala->id }}">
                    <div class="mb-3">
                        <textarea name="motivo_rejeicao" class="form-control" rows="2" 
                                  placeholder="Informe o motivo da rejeição (mín. 10 caracteres)" required minlength="10"></textarea>
                    </div>
                    <button type="submit" class="btn btn-danger">Rejeitar</button>
                </form>
            </div>
        </div>
    </div>
    @endif

    @if($escala->status === 'aprovada')
    <div class="col-md-6">
        <div class="card border-info">
            <div class="card-body">
                <h5 class="text-info"><i class="bi bi-cash-stack"></i> Marcar como Executada</h5>
                <form method="POST" action="/rh/executar">
                    @csrf
                    <input type="hidden" name="escala_id" value="{{ $escala->id }}">
                    <div class="mb-3">
                        <label class="form-label">Valor Executado (R$)</label>
                        <input type="number" name="valor_executado" class="form-control" step="0.01" min="0" required>
                    </div>
                    <button type="submit" class="btn btn-info">Marcar como Executada</button>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>

<div class="card">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="bi bi-people me-2"></i>Servidores na Escala</h5>
    </div>
    <div class="card-body">
        @if($servidores->count() > 0)
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Servidor</th>
                        <th>Equipe</th>
                        <th>Módulo</th>
                        <th>Líder</th>
                        <th>Horas</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($servidores as $es)
                    @php
                        $horasServidor = $alocacoes->where('servidor_id', $es->servidor_id)->sum('horas');
                    @endphp
                    <tr>
                        <td>{{ $es->servidor->nome }}</td>
                        <td>{{ $es->equipe->nome }}</td>
                        <td>{{ $es->modulo->nome ?? '-' }}</td>
                        <td>
                            @if($es->lider)
                                <span class="badge bg-primary">Líder</span>
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $horasServidor }}h</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="table-secondary">
                        <th colspan="4">Total de Horas</th>
                        <th>{{ $alocacoes->sum('horas') }}h</th>
                    </tr>
                </tfoot>
            </table>
        </div>
        @else
        <div class="text-center py-4 text-muted">
            <i class="bi bi-calendar-x display-4"></i>
            <p class="mt-2">Nenhum servidor na escala</p>
        </div>
        @endif
    </div>
</div>
@endsection
