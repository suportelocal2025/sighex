@extends('layouts.app')

@section('title', 'Superintendente - Orçamento')
@section('header', 'Configuração de Orçamento')

@section('sidebar')
    <a href="/superintendente" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="/superintendente/orcamento" class="nav-link active"><i class="bi bi-wallet2"></i> Orçamento</a>
    <a href="/superintendente/distribuicao" class="nav-link"><i class="bi bi-diagram-3"></i> Distribuição</a>
    <a href="/superintendente/escalas" class="nav-link"><i class="bi bi-calendar-check"></i> Escalas</a>
    <a href="/superintendente/relatorios" class="nav-link"><i class="bi bi-file-earmark-bar-graph"></i> Relatórios</a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-wallet2 me-2"></i>Orçamento Anual - {{ $ano }}</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/superintendente/orcamento">
                    @csrf
                    <input type="hidden" name="ano" value="{{ $ano }}">
                    
                    <div class="mb-4">
                        <label class="form-label">Valor Total do Orçamento (R$)</label>
                        <div class="input-group">
                            <span class="input-group-text">R$</span>
                            <input type="number" name="valor_total" class="form-control form-control-lg" 
                                   value="{{ $orcamento->valor_total ?? 0 }}" step="0.01" min="0" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Reserva Técnica (%)</label>
                        <div class="input-group">
                            <input type="number" name="reserva_tecnica_percentual" class="form-control form-control-lg" 
                                   value="{{ $orcamento->reserva_tecnica_percentual ?? 10 }}" step="0.01" min="0" max="100" required>
                            <span class="input-group-text">%</span>
                        </div>
                        <small class="text-muted">Percentual reservado para contingências</small>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-circle me-2"></i>Salvar Orçamento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
