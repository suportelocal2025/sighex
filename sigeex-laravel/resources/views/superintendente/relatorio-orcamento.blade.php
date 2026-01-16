@extends('layouts.app')

@section('title', 'Superintendente - Relatório de Orçamento')
@section('header', 'Relatório de Orçamento')

@section('sidebar')
    <a href="/superintendente" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="/superintendente/orcamento" class="nav-link"><i class="bi bi-wallet2"></i> Orçamento</a>
    <a href="/superintendente/distribuicao" class="nav-link"><i class="bi bi-diagram-3"></i> Distribuição</a>
    <a href="/superintendente/escalas" class="nav-link"><i class="bi bi-calendar-check"></i> Escalas</a>
    <a href="/superintendente/relatorios" class="nav-link active"><i class="bi bi-file-earmark-bar-graph"></i> Relatórios</a>
@endsection

@section('content')
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-funnel me-2"></i>Filtros do Relatório</span>
        <a href="/superintendente/relatorios" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Voltar
        </a>
    </div>
    <div class="card-body">
        <form method="GET" action="/superintendente/relatorio-orcamento" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small">Ano</label>
                <select name="ano" class="form-select form-select-sm">
                    @for($i = date('Y'); $i >= date('Y') - 5; $i--)
                        <option value="{{ $i }}" {{ $ano == $i ? 'selected' : '' }}>{{ $i }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-9 d-flex gap-2 justify-content-end">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-funnel me-1"></i> Filtrar
                </button>
                <a href="/superintendente/relatorio-orcamento/exportar-excel?ano={{ $ano }}" class="btn btn-success btn-sm" title="Exportar Excel">
                    <i class="bi bi-file-earmark-excel"></i> Excel
                </a>
                <button type="button" onclick="window.print()" class="btn btn-secondary btn-sm" title="Imprimir/PDF">
                    <i class="bi bi-printer"></i> Imprimir
                </button>
            </div>
        </form>
    </div>
</div>

<div class="row mb-4" id="resumo-global">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <h6>Orçamento Total</h6>
                <h4>R$ {{ number_format($valorTotal, 2, ',', '.') }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body text-center">
                <h6>Reserva Técnica</h6>
                <h4>R$ {{ number_format($reservaTecnica, 2, ',', '.') }}</h4>
                <small>{{ $orcamentoGlobal?->reserva_tecnica_percentual ?? 10 }}%</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <h6>Disponível</h6>
                <h4>R$ {{ number_format($valorDisponivel, 2, ',', '.') }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card {{ $saldoNaoDistribuido >= 0 ? 'bg-success' : 'bg-danger' }} text-white">
            <div class="card-body text-center">
                <h6>Não Distribuído</h6>
                <h4>R$ {{ number_format($saldoNaoDistribuido, 2, ',', '.') }}</h4>
            </div>
        </div>
    </div>
</div>

<div class="card" id="relatorio-content">
    <div class="card-header">
        <i class="bi bi-building me-2"></i>
        Distribuição de Orçamento por Unidade - Ano {{ $ano }}
    </div>
    <div class="card-body">
        @if($dados->isEmpty())
            <div class="text-center text-muted py-5">
                <i class="bi bi-inbox display-1"></i>
                <p class="mt-3">Nenhuma unidade cadastrada.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Código</th>
                            <th>Unidade</th>
                            <th class="text-end">Orçamento Anual</th>
                            <th class="text-center">Margem %</th>
                            <th class="text-end">Valor Gasto</th>
                            <th class="text-end">Saldo</th>
                            <th class="text-center">% Utilizado</th>
                            <th class="text-center">Escalas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $num = 1; @endphp
                        @foreach($dados as $d)
                            @php 
                                $saldo = $d->orcamento_distribuido - $d->valor_gasto;
                                $percentual = $d->orcamento_distribuido > 0 ? ($d->valor_gasto / $d->orcamento_distribuido * 100) : 0;
                            @endphp
                            <tr>
                                <td>{{ str_pad($num++, 3, '0', STR_PAD_LEFT) }}</td>
                                <td>{{ $d->codigo ?? '-' }}</td>
                                <td>{{ $d->nome }}</td>
                                <td class="text-end">R$ {{ number_format($d->orcamento_distribuido, 2, ',', '.') }}</td>
                                <td class="text-center">
                                    <span class="badge bg-secondary">{{ number_format($d->margin_percentual, 0) }}%</span>
                                </td>
                                <td class="text-end">
                                    <strong>R$ {{ number_format($d->valor_gasto, 2, ',', '.') }}</strong>
                                </td>
                                <td class="text-end">
                                    @if($saldo >= 0)
                                        <span class="text-success">R$ {{ number_format($saldo, 2, ',', '.') }}</span>
                                    @else
                                        <span class="text-danger">R$ {{ number_format($saldo, 2, ',', '.') }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="progress" style="height: 20px;">
                                        @php
                                            $cor = 'bg-success';
                                            if ($percentual > 100) $cor = 'bg-danger';
                                            elseif ($percentual > 80) $cor = 'bg-warning';
                                        @endphp
                                        <div class="progress-bar {{ $cor }}" style="width: {{ min($percentual, 100) }}%">
                                            {{ number_format($percentual, 1, ',', '.') }}%
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">{{ $d->total_escalas }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-secondary">
                        <tr class="fw-bold">
                            <td colspan="3" class="text-end">TOTAIS:</td>
                            <td class="text-end">R$ {{ number_format($totalDistribuido, 2, ',', '.') }}</td>
                            <td></td>
                            <td class="text-end">R$ {{ number_format($totalGasto, 2, ',', '.') }}</td>
                            <td class="text-end">
                                @php $saldoTotal = $totalDistribuido - $totalGasto; @endphp
                                @if($saldoTotal >= 0)
                                    <span class="text-success">R$ {{ number_format($saldoTotal, 2, ',', '.') }}</span>
                                @else
                                    <span class="text-danger">R$ {{ number_format($saldoTotal, 2, ',', '.') }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @php $percentualTotal = $totalDistribuido > 0 ? ($totalGasto / $totalDistribuido * 100) : 0; @endphp
                                {{ number_format($percentualTotal, 1, ',', '.') }}%
                            </td>
                            <td class="text-center">{{ $dados->sum('total_escalas') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <div class="row mt-4">
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6 class="text-muted">Total de Unidades</h6>
                            <h3 class="text-primary">{{ $dados->count() }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6 class="text-muted">Total Distribuído</h6>
                            <h3 class="text-info">R$ {{ number_format($totalDistribuido, 2, ',', '.') }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6 class="text-muted">Total Gasto</h6>
                            <h3 class="text-warning">R$ {{ number_format($totalGasto, 2, ',', '.') }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6 class="text-muted">Saldo Total</h6>
                            <h3 class="{{ $saldoTotal >= 0 ? 'text-success' : 'text-danger' }}">
                                R$ {{ number_format($saldoTotal, 2, ',', '.') }}
                            </h3>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<style>
@media print {
    @page {
        size: A4 landscape;
        margin: 10mm;
    }
    html, body {
        width: 100% !important;
        height: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    .sidebar, .navbar, form, .btn, .alert, .card:not(#relatorio-content):not(#resumo-global .card), nav { 
        display: none !important; 
    }
    .main-content, .container-fluid, .row, .col-12, .col-md-10 {
        width: 100% !important;
        max-width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    #resumo-global {
        display: flex !important;
        margin-bottom: 15px !important;
    }
    #resumo-global .col-md-3 {
        width: 25% !important;
        padding: 0 5px !important;
    }
    #resumo-global .card {
        border: 1px solid #ccc !important;
    }
    #relatorio-content {
        width: 100% !important;
        border: none !important;
        box-shadow: none !important;
        margin: 0 !important;
    }
    #relatorio-content .card-header {
        background-color: #0d6efd !important;
        color: #fff !important;
        font-size: 14pt !important;
        padding: 10px !important;
    }
    .table {
        width: 100% !important;
        font-size: 9pt !important;
    }
    .table th, .table td {
        padding: 5px 6px !important;
    }
    .table-dark th { 
        background-color: #0d6efd !important; 
        color: #fff !important; 
    }
    .progress {
        border: 1px solid #ccc !important;
    }
    .row.mt-4 {
        margin-top: 15px !important;
    }
    .row.mt-4 .card {
        display: inline-block !important;
        width: 23% !important;
        margin: 0 1% !important;
        border: 1px solid #ccc !important;
    }
}
</style>
@endsection
