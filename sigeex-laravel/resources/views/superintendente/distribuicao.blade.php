@extends('layouts.app')

@section('title', 'Superintendente - Distribuição')
@section('header', 'Distribuição de Orçamento')

@section('sidebar')
    <a href="/superintendente" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="/superintendente/orcamento" class="nav-link"><i class="bi bi-wallet2"></i> Orçamento</a>
    <a href="/superintendente/distribuicao" class="nav-link"><i class="bi bi-diagram-3"></i> Distribuição</a>
    <a href="/superintendente/relatorios" class="nav-link"><i class="bi bi-file-earmark-bar-graph"></i> Relatórios</a>
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h6>Orçamento Total</h6>
                <h3>R$ {{ number_format($valorTotal, 2, ',', '.') }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <h6>Reserva Técnica</h6>
                <h3>R$ {{ number_format($reservaTecnica, 2, ',', '.') }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h6>Disponível para Distribuir</h6>
                <h3>R$ {{ number_format($valorDisponivel, 2, ',', '.') }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="bi bi-diagram-3 me-2"></i>Distribuição por Unidade - {{ $ano }}</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Unidade</th>
                        <th>Valor Distribuído</th>
                        <th>Valor Gasto</th>
                        <th>Saldo</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($unidades as $unidade)
                    @php
                        $dist = $distribuicoes->get($unidade->id);
                        $distribuido = $dist->valor_distribuido ?? 0;
                        $gasto = $dist->valor_gasto ?? 0;
                        $saldo = $distribuido - $gasto;
                    @endphp
                    <tr>
                        <td>{{ $unidade->nome }}</td>
                        <td>R$ {{ number_format($distribuido, 2, ',', '.') }}</td>
                        <td>R$ {{ number_format($gasto, 2, ',', '.') }}</td>
                        <td class="{{ $saldo < 0 ? 'text-danger' : 'text-success' }}">
                            R$ {{ number_format($saldo, 2, ',', '.') }}
                        </td>
                        <td>
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalDistribuir"
                                    onclick="abrirModal({{ $unidade->id }}, '{{ $unidade->nome }}', {{ $distribuido }})">
                                <i class="bi bi-pencil"></i> Editar
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDistribuir" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/superintendente/distribuicao">
                @csrf
                <input type="hidden" name="unidade_id" id="modal_unidade_id">
                <div class="modal-header">
                    <h5 class="modal-title">Distribuir Orçamento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Unidade: <strong id="modal_unidade_nome"></strong></p>
                    <div class="mb-3">
                        <label class="form-label">Valor a Distribuir (R$)</label>
                        <input type="number" name="valor" id="modal_valor" class="form-control" step="0.01" min="0" required>
                    </div>
                    <p class="text-muted small">Disponível: R$ {{ number_format($valorDisponivel, 2, ',', '.') }}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function abrirModal(id, nome, valor) {
    document.getElementById('modal_unidade_id').value = id;
    document.getElementById('modal_unidade_nome').textContent = nome;
    document.getElementById('modal_valor').value = valor;
}
</script>
@endpush
