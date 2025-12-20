@extends('layouts.app')

@section('title', 'RH - Detalhar Escala')
@section('header', 'Detalhes da Escala')

@section('sidebar')
    <a href="/rh" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="/rh/escalas" class="nav-link"><i class="bi bi-calendar-check"></i> Escalas</a>
    <a href="/rh/relatorios" class="nav-link"><i class="bi bi-file-earmark-bar-graph"></i> Relatórios</a>
@endsection

@section('content')
@php
    $meses = ['', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 
              'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
    $totalHoras = $alocacoes->sum(fn($a) => $a->horas ?? 0);
    $totalAbono = $alocacoes->sum(fn($a) => $a->horas_abono ?? 0);
    $totalGeral = $totalHoras + $totalAbono;
@endphp

<div class="row mb-4">
    <div class="col-md-8">
        <div class="card p-3">
            <div class="row">
                <div class="col-md-4">
                    <small class="text-muted">Unidade</small>
                    <h5 class="mb-0">{{ $escala->unidade->nome ?? 'N/A' }}</h5>
                </div>
                <div class="col-md-4">
                    <small class="text-muted">Período</small>
                    <h5 class="mb-0">{{ $meses[$escala->mes] }}/{{ $escala->ano }}</h5>
                </div>
                <div class="col-md-4">
                    <small class="text-muted">Total de Horas</small>
                    <h5 class="mb-0">{{ number_format($totalGeral, 0, ',', '.') }}h</h5>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-3 text-center">
            @php
                $badgeClass = match($escala->status) {
                    'pendente' => 'bg-warning',
                    'aprovada' => 'bg-success',
                    'executada' => 'bg-info',
                    'rejeitada' => 'bg-danger',
                    default => 'bg-secondary'
                };
            @endphp
            <span class="badge {{ $badgeClass }} fs-5 py-2">
                {{ ucfirst($escala->status) }}
            </span>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-person-badge me-2"></i>Resumo por Servidor</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Servidor</th>
                            <th>Matrícula</th>
                            <th class="text-center">Horas</th>
                            <th class="text-center">Abono</th>
                            <th class="text-center">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $resumo = $alocacoes->groupBy('servidor_id')->map(function($group) use ($servidores) {
                                $servidor = $servidores->firstWhere('servidor_id', $group->first()->servidor_id);
                                $horas = $group->sum(fn($a) => $a->horas ?? 0);
                                $abono = $group->sum(fn($a) => $a->horas_abono ?? 0);
                                return [
                                    'nome' => $servidor?->servidor?->nome ?? 'N/A',
                                    'matricula' => $servidor?->servidor?->matricula ?? '-',
                                    'horas' => $horas,
                                    'abono' => $abono,
                                    'total' => $horas + $abono,
                                ];
                            });
                        @endphp
                        @foreach($resumo as $r)
                        <tr>
                            <td>{{ $r['nome'] }}</td>
                            <td>{{ $r['matricula'] }}</td>
                            <td class="text-center">{{ number_format($r['horas'], 1) }}</td>
                            <td class="text-center">{{ number_format($r['abono'], 1) }}</td>
                            <td class="text-center fw-bold">{{ number_format($r['total'], 1) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informações</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th>Data de Envio:</th>
                        <td>{{ $escala->data_envio ? date('d/m/Y H:i', strtotime($escala->data_envio)) : '-' }}</td>
                    </tr>
                    @if($escala->data_aprovacao)
                    <tr>
                        <th>Data de Aprovação:</th>
                        <td>{{ date('d/m/Y H:i', strtotime($escala->data_aprovacao)) }}</td>
                    </tr>
                    @endif
                    @if($escala->status === 'executada' && $escala->valor_executado)
                    <tr>
                        <th>Valor Executado:</th>
                        <td class="text-success fw-bold">R$ {{ number_format($escala->valor_executado, 2, ',', '.') }}</td>
                    </tr>
                    @endif
                    @if($escala->motivo_rejeicao)
                    <tr>
                        <th>Motivo da Rejeição:</th>
                        <td class="text-danger">{{ $escala->motivo_rejeicao }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>

@if($escala->status === 'pendente')
<div class="row g-4 mb-4">
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
</div>
@endif

@if($escala->status === 'aprovada')
<div class="row g-4 mb-4">
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
</div>
@endif

@php
    $alocacoesAgrupadas = [];
    foreach ($alocacoes as $a) {
        $servidor = $servidores->firstWhere('servidor_id', $a->servidor_id);
        $key = $a->servidor_id . '_' . ($a->equipe_id ?? 0) . '_' . ($a->modulo_id ?? 0);
        if (!isset($alocacoesAgrupadas[$key])) {
            $alocacoesAgrupadas[$key] = [
                'servidor_nome' => $servidor?->servidor?->nome ?? 'N/A',
                'matricula' => $servidor?->servidor?->matricula ?? '-',
                'equipe_nome' => $servidor?->equipe?->nome ?? '-',
                'modulo_nome' => $servidor?->modulo?->nome ?? '-',
                'is_lider' => $servidor?->lider ?? false,
                'dias' => [],
                'horas' => 0,
                'horas_abono' => 0
            ];
        }
        $alocacoesAgrupadas[$key]['dias'][] = str_pad($a->dia, 2, '0', STR_PAD_LEFT);
        $alocacoesAgrupadas[$key]['horas'] += $a->horas ?? 0;
        $alocacoesAgrupadas[$key]['horas_abono'] += $a->horas_abono ?? 0;
    }
    foreach ($alocacoesAgrupadas as &$ag) {
        sort($ag['dias']);
    }
    unset($ag);
@endphp

<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-table me-2"></i>Alocações Detalhadas</h5>
        <div>
            <a href="/rh/escala/{{ $escala->id }}/exportar-excel" class="btn btn-success btn-sm me-2">
                <i class="bi bi-file-earmark-excel me-2"></i>Exportar Excel
            </a>
            <button class="btn btn-outline-primary btn-sm" onclick="window.print()">
                <i class="bi bi-printer me-2"></i>Imprimir
            </button>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Servidor</th>
                    <th>Matrícula</th>
                    <th>Equipe</th>
                    <th>Módulo</th>
                    <th>Dias</th>
                    <th class="text-center">Horas</th>
                    <th class="text-center">Abono</th>
                    <th class="text-center">Líder</th>
                </tr>
            </thead>
            <tbody>
                @forelse($alocacoesAgrupadas as $a)
                <tr>
                    <td>{{ $a['servidor_nome'] }}</td>
                    <td>{{ $a['matricula'] }}</td>
                    <td>{{ $a['equipe_nome'] }}</td>
                    <td>{{ $a['modulo_nome'] }}</td>
                    <td><span class="badge bg-light text-dark">{{ implode(', ', $a['dias']) }}</span></td>
                    <td class="text-center">{{ number_format($a['horas'], 1) }}</td>
                    <td class="text-center">{{ number_format($a['horas_abono'], 1) }}</td>
                    <td class="text-center">
                        @if($a['is_lider'])
                            <span class="badge bg-warning"><i class="bi bi-star-fill"></i></span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-4 text-muted">
                        <i class="bi bi-calendar-x display-4"></i>
                        <p class="mt-2">Nenhuma alocação encontrada</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">
    <a href="/rh/escalas" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Voltar
    </a>
</div>
@endsection
