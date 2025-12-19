@extends('layouts.app')

@section('title', 'Diretor - Escala Mensal')
@section('header', 'Escala Mensal ' . str_pad($mes, 2, '0', STR_PAD_LEFT) . '/' . $ano)

@section('sidebar')
    <a href="/diretor" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="/diretor/escala-mensal" class="nav-link"><i class="bi bi-calendar3"></i> Escala Mensal</a>
    <a href="/diretor/servidores" class="nav-link"><i class="bi bi-people"></i> Servidores</a>
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <form class="row g-2" method="GET" action="/diretor/escala-mensal">
            <div class="col-auto">
                <select name="mes" class="form-select">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $mes == $m ? 'selected' : '' }}>
                            {{ str_pad($m, 2, '0', STR_PAD_LEFT) }}
                        </option>
                    @endfor
                </select>
            </div>
            <div class="col-auto">
                <select name="ano" class="form-select">
                    @for($a = date('Y') - 1; $a <= date('Y') + 1; $a++)
                        <option value="{{ $a }}" {{ $ano == $a ? 'selected' : '' }}>{{ $a }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">Carregar</button>
            </div>
        </form>
    </div>
    <div class="col-md-6 text-end">
        <span class="badge bg-{{ $escala->status === 'rascunho' ? 'secondary' : ($escala->status === 'pendente' ? 'warning' : ($escala->status === 'aprovada' ? 'success' : ($escala->status === 'rejeitada' ? 'danger' : 'info'))) }} fs-6">
            Status: {{ ucfirst($escala->status) }}
        </span>
    </div>
</div>

@if($escala->status === 'rejeitada' && $escala->motivo_rejeicao)
<div class="alert alert-danger">
    <strong>Motivo da Rejeição:</strong> {{ $escala->motivo_rejeicao }}
</div>
@endif

<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-calendar3 me-2"></i>Calendário</h5>
        @if(in_array($escala->status, ['rascunho', 'rejeitada']))
        <form method="POST" action="/diretor/enviar-aprovacao" onsubmit="return confirm('Enviar escala para aprovação?')">
            @csrf
            <input type="hidden" name="escala_id" value="{{ $escala->id }}">
            <button type="submit" class="btn btn-success">
                <i class="bi bi-send"></i> Enviar para Aprovação
            </button>
        </form>
        @endif
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> Esta é uma versão simplificada do calendário. 
            A versão completa com seleção de servidores por equipe será implementada na próxima fase.
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <h6>Servidores na Escala: {{ $escalaServidores->count() }}</h6>
            </div>
            <div class="col-md-6">
                <h6>Total de Alocações: {{ $alocacoes->count() }}</h6>
            </div>
        </div>

        @if($escalaServidores->count() > 0)
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
                    @foreach($escalaServidores as $es)
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
            </table>
        </div>
        @else
        <div class="text-center py-5">
            <i class="bi bi-calendar-x display-1 text-muted"></i>
            <p class="text-muted mt-3">Nenhum servidor adicionado a esta escala ainda.</p>
        </div>
        @endif
    </div>
</div>
@endsection
