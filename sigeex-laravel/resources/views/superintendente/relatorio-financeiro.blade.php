@extends('layouts.app')

@section('title', 'Superintendente - Relatório Financeiro')
@section('header', 'Relatório Financeiro')

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
        <form method="GET" action="/superintendente/relatorio-financeiro" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label small">Ano</label>
                <select name="ano" class="form-select form-select-sm">
                    @for($i = date('Y'); $i >= date('Y') - 5; $i--)
                        <option value="{{ $i }}" {{ $ano == $i ? 'selected' : '' }}>{{ $i }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Mês Início</label>
                <select name="mes_inicio" class="form-select form-select-sm">
                    @php $mesesNomes = ['', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro']; @endphp
                    @for($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ $mesInicio == $i ? 'selected' : '' }}>{{ $mesesNomes[$i] }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Mês Fim</label>
                <select name="mes_fim" class="form-select form-select-sm">
                    @for($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ $mesFim == $i ? 'selected' : '' }}>{{ $mesesNomes[$i] }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Unidade</label>
                <select name="unidade_id" class="form-select form-select-sm">
                    <option value="">Todas as Unidades</option>
                    @foreach($unidades as $unidade)
                        <option value="{{ $unidade->id }}" {{ $unidadeId == $unidade->id ? 'selected' : '' }}>{{ $unidade->nome }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                    <i class="bi bi-funnel me-1"></i> Filtrar
                </button>
                <a href="/superintendente/relatorio-financeiro/exportar-excel?ano={{ $ano }}&mes_inicio={{ $mesInicio }}&mes_fim={{ $mesFim }}&unidade_id={{ $unidadeId }}" class="btn btn-success btn-sm" title="Exportar Excel">
                    <i class="bi bi-file-earmark-excel"></i>
                </a>
                <button type="button" onclick="window.print()" class="btn btn-secondary btn-sm" title="Imprimir/PDF">
                    <i class="bi bi-printer"></i>
                </button>
            </div>
        </form>
    </div>
</div>

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card" id="relatorio-content">
    <div class="card-header">
        <i class="bi bi-cash-stack me-2"></i>
        Valores Executados por Escala - {{ $mesesNomes[$mesInicio] }}@if($mesInicio != $mesFim) a {{ $mesesNomes[$mesFim] }}@endif/{{ $ano }}
        @if($unidadeSelecionada)
            - {{ $unidadeSelecionada->nome }}
        @endif
    </div>
    <div class="card-body">
        @if($dados->isEmpty())
            <div class="text-center text-muted py-5">
                <i class="bi bi-inbox display-1"></i>
                <p class="mt-3">Nenhum dado encontrado para o período selecionado.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Unidade</th>
                            <th>Mês/Ano</th>
                            <th class="text-center">Status</th>
                            <th class="text-end">Orçamento Previsto</th>
                            <th class="text-end">Valor Executado</th>
                            <th class="text-end">Diferença</th>
                            <th class="text-center">Total Horas</th>
                            <th class="text-center">Servidores</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php 
                            $num = 1; 
                            $totalPrevisto = 0; 
                            $totalExecutado = 0; 
                            $totalHoras = 0;
                        @endphp
                        @foreach($dados as $d)
                            @php 
                                $totalPrevisto += $d->orcamento_previsto; 
                                $totalExecutado += $d->valor_executado;
                                $totalHoras += $d->total_horas;
                                $diferenca = $d->orcamento_previsto - $d->valor_executado;
                            @endphp
                            <tr>
                                <td>{{ str_pad($num++, 3, '0', STR_PAD_LEFT) }}</td>
                                <td>{{ $d->unidade_nome }}</td>
                                <td>{{ $mesesNomes[$d->mes] }}/{{ $d->ano }}</td>
                                <td class="text-center">
                                    @if($d->status == 'executada')
                                        <span class="badge bg-success">Executada</span>
                                    @elseif($d->status == 'aprovada')
                                        <span class="badge bg-primary">Aprovada</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($d->status) }}</span>
                                    @endif
                                </td>
                                <td class="text-end">R$ {{ number_format($d->orcamento_previsto, 2, ',', '.') }}</td>
                                <td class="text-end">
                                    <strong>R$ {{ number_format($d->valor_executado, 2, ',', '.') }}</strong>
                                </td>
                                <td class="text-end">
                                    @if($diferenca >= 0)
                                        <span class="text-success">R$ {{ number_format($diferenca, 2, ',', '.') }}</span>
                                    @else
                                        <span class="text-danger">R$ {{ number_format($diferenca, 2, ',', '.') }}</span>
                                    @endif
                                </td>
                                <td class="text-center">{{ number_format($d->total_horas, 0, ',', '.') }}</td>
                                <td class="text-center">{{ $d->total_servidores }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-secondary">
                        <tr class="fw-bold">
                            <td colspan="4" class="text-end">TOTAIS:</td>
                            <td class="text-end">R$ {{ number_format($totalPrevisto, 2, ',', '.') }}</td>
                            <td class="text-end">R$ {{ number_format($totalExecutado, 2, ',', '.') }}</td>
                            <td class="text-end">
                                @php $diferencaTotal = $totalPrevisto - $totalExecutado; @endphp
                                @if($diferencaTotal >= 0)
                                    <span class="text-success">R$ {{ number_format($diferencaTotal, 2, ',', '.') }}</span>
                                @else
                                    <span class="text-danger">R$ {{ number_format($diferencaTotal, 2, ',', '.') }}</span>
                                @endif
                            </td>
                            <td class="text-center">{{ number_format($totalHoras, 0, ',', '.') }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <div class="row mt-4">
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6 class="text-muted">Total de Escalas</h6>
                            <h3 class="text-primary">{{ $dados->count() }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6 class="text-muted">Orçamento Previsto</h6>
                            <h3 class="text-info">R$ {{ number_format($totalPrevisto, 2, ',', '.') }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6 class="text-muted">Valor Executado</h6>
                            <h3 class="text-success">R$ {{ number_format($totalExecutado, 2, ',', '.') }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6 class="text-muted">Saldo</h6>
                            <h3 class="{{ $diferencaTotal >= 0 ? 'text-success' : 'text-danger' }}">
                                R$ {{ number_format($diferencaTotal, 2, ',', '.') }}
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
    .sidebar, .navbar, form, .btn, .alert, .card:not(#relatorio-content), nav { 
        display: none !important; 
    }
    .main-content, .container-fluid, .row, .col-12, .col-md-10 {
        width: 100% !important;
        max-width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    #relatorio-content {
        width: 100% !important;
        border: none !important;
        box-shadow: none !important;
        margin: 0 !important;
    }
    #relatorio-content .card-header {
        background-color: #198754 !important;
        color: #fff !important;
        font-size: 14pt !important;
        padding: 10px !important;
    }
    #relatorio-content .card-body {
        padding: 10px 0 !important;
    }
    .table {
        width: 100% !important;
        font-size: 9pt !important;
    }
    .table th, .table td {
        padding: 5px 6px !important;
    }
    .table-dark th { 
        background-color: #198754 !important; 
        color: #fff !important; 
    }
    .table-secondary td {
        background-color: #e9ecef !important;
    }
    .badge { 
        border: 1px solid #000 !important;
        padding: 2px 6px !important;
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
