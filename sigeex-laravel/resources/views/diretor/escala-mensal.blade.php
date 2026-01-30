@extends('layouts.app')

@section('title', 'Diretor - Escala Mensal')
@section('header', 'Escala Mensal ' . str_pad($mes, 2, '0', STR_PAD_LEFT) . '/' . $ano)

@section('sidebar')
    <a href="/diretor" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="/diretor/escala-mensal" class="nav-link active"><i class="bi bi-calendar3"></i> Escala Mensal</a>
    <a href="/diretor/servidores" class="nav-link"><i class="bi bi-people"></i> Servidores</a>
@endsection

@push('styles')
<style>
.calendario-container {
    max-height: 500px;
    overflow-y: auto;
    overflow-x: auto;
}
.dia-cell {
    width: 32px;
    height: 32px;
    min-width: 32px;
    border: 1px solid #e5e7eb;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    cursor: pointer;
    transition: all 0.15s ease;
    position: relative;
    background-color: #fff;
    color: #374151;
}
.dia-cell:hover:not(.alocado) {
    transform: scale(1.05);
    z-index: 10;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    background-color: #dbeafe !important;
}
.dia-cell.sabado {
    background-color: #fef9c3;
    border-color: #fde047;
    color: #854d0e;
}
.dia-cell.domingo {
    background-color: #fed7aa;
    border-color: #fdba74;
    color: #9a3412;
}
.dia-cell.feriado {
    background-color: #fecaca;
    border-color: #fca5a5;
    color: #991b1b;
}
.dia-cell.alocado {
    color: white !important;
}
.dia-cell.alocado-diurna {
    background-color: #60a5fa !important;
    border-color: #3b82f6 !important;
}
.dia-cell.alocado-noturna {
    background-color: #1e3a5f !important;
    border-color: #1e3a5f !important;
}
.servidor-row {
    transition: background-color 0.15s ease;
}
.servidor-row:hover {
    background-color: #f8fafc;
}
.legenda-item {
    display: inline-flex;
    align-items: center;
    margin-right: 12px;
    font-size: 0.8rem;
    color: #6b7280;
}
.legenda-cor {
    width: 16px;
    height: 16px;
    border-radius: 3px;
    margin-right: 4px;
}
.servidor-info {
    min-width: 200px;
    max-width: 200px;
}
.dias-container {
    display: flex;
    gap: 2px;
    flex-wrap: nowrap;
}
.form-selecao {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 1.25rem;
}
.servidor-item {
    padding: 0.75rem;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    margin-bottom: 0.5rem;
    cursor: pointer;
    transition: all 0.15s;
}
.servidor-item:hover {
    background-color: #f1f5f9;
}
.servidor-item.selected {
    background-color: #dbeafe;
    border-color: #3b82f6;
}
.servidor-item.disabled {
    opacity: 0.5;
    cursor: not-allowed;
    background-color: #f9fafb;
}
.table-calendario th {
    font-size: 0.75rem;
    padding: 0.4rem 0.2rem;
}
.table-calendario td {
    padding: 0.3rem 0.2rem;
    vertical-align: middle;
}
.btn-remover-servidor {
    opacity: 0;
    transition: opacity 0.2s;
}
.servidor-row:hover .btn-remover-servidor {
    opacity: 1;
}
.empty-state {
    text-align: center;
    padding: 3rem;
    color: #6b7280;
}
.empty-state i {
    font-size: 3rem;
    color: #d1d5db;
}
</style>
@endpush

@php
$meses = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 
          'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
$mesNome = $meses[$mes - 1];

$alocacoesPorServidor = [];
foreach ($alocacoes as $a) {
    if (!isset($alocacoesPorServidor[$a->servidor_id])) {
        $alocacoesPorServidor[$a->servidor_id] = [];
    }
    $alocacoesPorServidor[$a->servidor_id][$a->dia] = $a;
}
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold text-dark">
            <i class="bi bi-calendar3 me-2"></i>Escala de {{ $mesNome }}/{{ $ano }}
        </h4>
        <p class="text-muted mb-0 small">{{ $unidade->nome ?? 'Unidade não definida' }}</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <form method="GET" action="/diretor/escala-mensal" class="d-flex gap-2">
            <select name="mes" class="form-select form-select-sm" style="width:130px;">
                @foreach($meses as $i => $m)
                    <option value="{{ $i + 1 }}" {{ $i + 1 == $mes ? 'selected' : '' }}>{{ $m }}</option>
                @endforeach
            </select>
            <select name="ano" class="form-select form-select-sm" style="width:90px;">
                @for($a = date('Y') - 1; $a <= date('Y') + 1; $a++)
                    <option value="{{ $a }}" {{ $a == $ano ? 'selected' : '' }}>{{ $a }}</option>
                @endfor
            </select>
            <button type="submit" class="btn btn-sm btn-primary">Carregar</button>
        </form>
        <span class="badge bg-{{ $escala->status === 'rascunho' ? 'secondary' : ($escala->status === 'pendente' ? 'warning' : ($escala->status === 'aprovada' ? 'success' : ($escala->status === 'rejeitada' ? 'danger' : 'info'))) }}">
            {{ ucfirst($escala->status) }}
        </span>
    </div>
</div>

@if($escala->status === 'rejeitada' && $escala->motivo_rejeicao)
<div class="alert alert-danger border-0 mb-4 d-flex align-items-center justify-content-between">
    <div>
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <strong>Escala Rejeitada:</strong> {{ $escala->motivo_rejeicao }}
    </div>
</div>
@endif

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-2">
        <div class="d-flex flex-wrap gap-3 align-items-center">
            <strong class="text-muted small me-2"><i class="bi bi-info-circle me-1"></i> Legenda:</strong>
            <span class="legenda-item"><span class="legenda-cor" style="background:#fff; border:1px solid #e5e7eb;"></span> Dia útil</span>
            <span class="legenda-item"><span class="legenda-cor" style="background:#fef9c3; border:1px solid #fde047;"></span> Sábado</span>
            <span class="legenda-item"><span class="legenda-cor" style="background:#fed7aa; border:1px solid #fdba74;"></span> Domingo</span>
            <span class="legenda-item"><span class="legenda-cor" style="background:#fecaca; border:1px solid #fca5a5;"></span> Feriado</span>
            <span class="legenda-item"><span class="legenda-cor" style="background:#60a5fa;"></span> Diurna</span>
            <span class="legenda-item"><span class="legenda-cor" style="background:#1e3a5f;"></span> Noturna</span>
        </div>
    </div>
</div>

@if($podeEditar)
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body form-selecao">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-semibold">1. Selecione o Módulo <span class="text-danger">*</span></label>
                <select id="moduloSelect" class="form-select" onchange="onModuloChange()">
                    <option value="">Escolha um módulo...</option>
                    @foreach($modulos as $m)
                        <option value="{{ $m->id }}">{{ $m->nome }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">2. Selecione a Equipe <span class="text-danger">*</span></label>
                <select id="equipeSelect" class="form-select" onchange="onEquipeChange()" disabled>
                    <option value="">Primeiro selecione um módulo...</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">Tipo Extra</label>
                <select id="tipoExtraSelect" class="form-select">
                    <option value="diurna" selected>DIURNA</option>
                    <option value="noturna">NOTURNA</option>
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label fw-semibold">Horas/dia</label>
                <input type="number" id="horasInput" class="form-control bg-light" value="10" readonly disabled>
            </div>
            <div class="col-md-1">
                <label class="form-label fw-semibold">Abono</label>
                <input type="number" id="abonoInput" class="form-control" min="0" max="24" value="0">
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="button" class="btn btn-primary flex-grow-1" id="btnAddServidor" onclick="abrirModalServidores()" disabled>
                    <i class="bi bi-person-plus me-1"></i> Add
                </button>
                <button type="button" class="btn btn-info" id="btnEspelhar" onclick="abrirModalEspelhar()" title="Espelhar escala de mês anterior" disabled>
                    <i class="bi bi-copy"></i>
                </button>
            </div>
        </div>
        <div class="mt-3 small text-muted">
            <i class="bi bi-lightbulb me-1"></i>
            <strong>Como usar:</strong> Selecione Módulo e Equipe, escolha o tipo de extra (DIURNA ou NOTURNA), adicione servidores e clique nos dias para alocar.
            <br><strong>Limites:</strong> Máx. 60h diurnas (6 dias), máx. 20h noturnas (2 dias), total máx. 60h por servidor.
        </div>
    </div>
</div>
@else
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body form-selecao">
        <div class="row g-3 align-items-center">
            <div class="col-md-4">
                <label class="form-label fw-semibold">Visualizar Equipe</label>
                <select id="equipeSelect" class="form-select">
                    <option value="todas" selected>TODAS AS EQUIPES</option>
                    @foreach($equipes as $e)
                        <option value="{{ $e->id }}">{{ $e->nome }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-8">
                <div class="alert alert-info mb-0 py-2">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Modo Visualização:</strong> Esta escala está com status <strong>{{ ucfirst($escala->status) }}</strong> e não pode ser editada.
                </div>
            </div>
        </div>
    </div>
</div>
<input type="hidden" id="moduloSelect" value="">
<input type="hidden" id="horasInput" value="10">
<input type="hidden" id="abonoInput" value="0">
<input type="hidden" id="tipoExtraSelect" value="diurna">
@endif

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-2 border-0">
        <h6 class="mb-0 fw-semibold">
            <i class="bi bi-calendar3 me-2"></i>Calendário - 
            <span id="equipeNomeHeader">Selecione uma equipe</span>
        </h6>
        <div>
            <a href="/diretor/escala/imprimir-mural?mes={{ $mes }}&ano={{ $ano }}" target="_blank" class="btn btn-outline-secondary btn-sm me-2">
                <i class="bi bi-printer me-1"></i> Imprimir P/Mural
            </a>
            @if($podeEditar)
            <form method="POST" action="/diretor/enviar-aprovacao" class="d-inline" onsubmit="return confirm('Enviar escala para aprovação?')">
                @csrf
                <input type="hidden" name="escala_id" value="{{ $escala->id }}">
                <button type="submit" class="btn btn-success btn-sm" id="btnEnviar" style="display:none;">
                    <i class="bi bi-send me-1"></i> {{ $escala->status === 'rejeitada' ? 'Re-Enviar para Aprovação' : 'Enviar para Aprovação' }}
                </button>
            </form>
            @endif
        </div>
    </div>
    
    <div class="calendario-container" id="calendarioContainer">
        <div class="empty-state" id="emptyState">
            <i class="bi bi-people mb-3 d-block"></i>
            <h6>Nenhum servidor na equipe</h6>
            <p class="small">Selecione uma equipe e clique em "Add Servidor" para começar</p>
        </div>
        <table class="table table-sm table-calendario mb-0" id="tabelaCalendario" style="display:none;">
            <thead class="sticky-top bg-white">
                <tr>
                    <th class="servidor-info bg-light text-muted">Servidor</th>
                    <th class="text-center bg-light text-muted" style="min-width:80px;">D / N / Total</th>
                    <th class="text-center bg-light text-muted" style="min-width:50px;">Líder</th>
                    @for($d = 1; $d <= $diasNoMes; $d++)
                        @php $info = $diasInfo[$d]; @endphp
                        <th class="text-center bg-light text-muted px-1" style="min-width:34px;" title="{{ $info['nomeDia'] }}, dia {{ $d }}">
                            <small class="d-block text-uppercase" style="font-size:0.6rem;">{{ substr($info['nomeDia'], 0, 3) }}</small>
                            {{ $d }}
                        </th>
                    @endfor
                </tr>
            </thead>
            <tbody id="calendarioBody">
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modalServidores" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white border-0">
                <h6 class="modal-title fw-semibold">
                    <i class="bi bi-person-plus me-2"></i>Adicionar Servidores à Equipe
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" class="form-control" id="buscarServidor" placeholder="Buscar servidor por nome ou matrícula...">
                </div>
                <div id="listaServidoresModal" style="max-height: 400px; overflow-y: auto;">
                </div>
            </div>
            <div class="modal-footer border-0 d-flex justify-content-between">
                <button type="button" class="btn btn-outline-success" onclick="abrirModalIncluirServidor()">
                    <i class="bi bi-person-plus me-1"></i> Incluir Servidor
                </button>
                <div>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="salvarServidoresSelecionados()">
                        <i class="bi bi-check-lg me-1"></i> Adicionar Selecionados
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalIncluirServidor" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white border-0">
                <h6 class="modal-title fw-semibold">
                    <i class="bi bi-person-plus me-2"></i>Solicitar Inclusão de Servidor
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-1"></i>
                    Se o servidor não está na lista, preencha os dados abaixo para solicitar sua inclusão. 
                    A solicitação será enviada ao RH para aprovação.
                </div>
                <div class="mb-3">
                    <label class="form-label">Matrícula <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="novoServidorMatricula" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Nome Completo <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="novoServidorNome" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Cargo</label>
                    <input type="text" class="form-control" id="novoServidorCargo" placeholder="Ex: Policial Penal">
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" onclick="enviarSolicitacaoServidor()">
                    <i class="bi bi-send me-1"></i> Enviar Solicitação
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEspelhar" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white border-0">
                <h6 class="modal-title fw-semibold">
                    <i class="bi bi-copy me-2"></i>Espelhar Escala de Mês Anterior
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info mb-3">
                    <i class="bi bi-info-circle me-1"></i>
                    Selecione a <strong>origem</strong> (módulo, equipe, mês e ano) para trazer os servidores escalados. 
                    Eles serão adicionados ao <strong>destino</strong> (módulo e equipe selecionados na página) <strong>sem dias alocados</strong>.
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="card bg-light border-0 mb-3">
                            <div class="card-header bg-secondary text-white py-2">
                                <i class="bi bi-box-arrow-right me-1"></i> Origem (De onde copiar)
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold">Módulo/Raio/Setor de Referência</label>
                                    <select id="espelharModulo" class="form-select form-select-sm">
                                        <option value="">Selecione...</option>
                                        @foreach($modulos as $mod)
                                            <option value="{{ $mod->id }}">{{ $mod->nome }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold">Equipe de Referência</label>
                                    <select id="espelharEquipe" class="form-select form-select-sm">
                                        <option value="">Selecione...</option>
                                        @foreach($equipes as $eq)
                                            <option value="{{ $eq->id }}">{{ $eq->nome }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-0">
                                    <label class="form-label small fw-semibold">Mês/Ano de Referência</label>
                                    <div class="d-flex gap-2">
                                        <select id="espelharMes" class="form-select form-select-sm">
                                            @php
                                                $mesesEspelhar = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 
                                                          'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
                                            @endphp
                                            @for($i = 1; $i <= 12; $i++)
                                                @php
                                                    $mesAnterior = $mes - 1;
                                                    if ($mesAnterior < 1) $mesAnterior = 12;
                                                @endphp
                                                <option value="{{ $i }}" {{ $i == $mesAnterior ? 'selected' : '' }}>{{ $mesesEspelhar[$i - 1] }}</option>
                                            @endfor
                                        </select>
                                        <select id="espelharAno" class="form-select form-select-sm" style="width: 90px;">
                                            @for($a = date('Y') - 1; $a <= date('Y') + 1; $a++)
                                                @php
                                                    $anoAnterior = $mes == 1 ? $ano - 1 : $ano;
                                                @endphp
                                                <option value="{{ $a }}" {{ $a == $anoAnterior ? 'selected' : '' }}>{{ $a }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light border-0 mb-3">
                            <div class="card-header bg-primary text-white py-2">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Destino (Para onde copiar)
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold">Módulo/Raio/Setor</label>
                                    <input type="text" class="form-control form-control-sm bg-white" id="espelharDestinoModulo" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold">Equipe</label>
                                    <input type="text" class="form-control form-control-sm bg-white" id="espelharDestinoEquipe" readonly>
                                </div>
                                <div class="mb-0">
                                    <label class="form-label small fw-semibold">Mês/Ano</label>
                                    <input type="text" class="form-control form-control-sm bg-white" id="espelharDestinoMesAno" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div id="espelharPreview" class="mt-2" style="display:none;">
                    <label class="form-label fw-semibold">Servidores encontrados:</label>
                    <div id="espelharServidoresLista" style="max-height: 200px; overflow-y: auto;">
                    </div>
                </div>
                <div id="espelharLoading" class="text-center py-4" style="display:none;">
                    <div class="spinner-border text-info" role="status"></div>
                    <p class="mt-2 text-muted">Buscando servidores...</p>
                </div>
                <div id="espelharEmpty" class="text-center py-4 text-muted" style="display:none;">
                    <i class="bi bi-inbox display-4"></i>
                    <p class="mt-2">Nenhum servidor encontrado com os critérios selecionados.</p>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-outline-info" onclick="buscarServidoresEspelhar()">
                    <i class="bi bi-search me-1"></i> Buscar
                </button>
                <button type="button" class="btn btn-info" id="btnConfirmarEspelhar" onclick="confirmarEspelhar()" disabled>
                    <i class="bi bi-copy me-1"></i> Espelhar
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const escalaId = {{ $escala->id }};
const limiteHoras = {{ $limiteHoras }};
const podeEditar = {{ $podeEditar ? 'true' : 'false' }};
const diasNoMes = {{ $diasNoMes }};
const diasInfo = @json($diasInfo);
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

let servidoresSelecionadosModal = new Set();
let servidoresEquipeAtual = [];
const horasMap = {};
let servidoresModuloEquipe = [];

const todosServidores = @json($servidores);
const escalaServidoresData = @json($escalaServidores);
const alocacoesData = @json($alocacoes);
const todasEquipes = @json($equipes);

async function onModuloChange() {
    const moduloId = document.getElementById('moduloSelect')?.value;
    const equipeSelect = document.getElementById('equipeSelect');
    const btn = document.getElementById('btnAddServidor');
    
    if (!moduloId) {
        equipeSelect.innerHTML = '<option value="">Primeiro selecione um módulo...</option>';
        equipeSelect.disabled = true;
        if (btn) btn.disabled = true;
        const btnEspelhar = document.getElementById('btnEspelhar');
        if (btnEspelhar) btnEspelhar.disabled = true;
        document.getElementById('equipeNomeHeader').textContent = 'Selecione módulo e equipe';
        servidoresEquipeAtual = [];
        renderizarCalendario();
        return;
    }
    
    equipeSelect.disabled = false;
    let equipesHtml = '<option value="">Escolha uma equipe...</option>';
    todasEquipes.forEach(e => {
        equipesHtml += `<option value="${e.id}">${e.nome}</option>`;
    });
    equipeSelect.innerHTML = equipesHtml;
    
    if (btn) btn.disabled = true;
    document.getElementById('equipeNomeHeader').textContent = 'Selecione uma equipe';
}

async function onEquipeChange() {
    const equipeId = document.getElementById('equipeSelect')?.value;
    const moduloId = document.getElementById('moduloSelect')?.value;
    const btn = document.getElementById('btnAddServidor');
    const btnEspelhar = document.getElementById('btnEspelhar');
    
    if (!equipeId || !moduloId) {
        if (btn) btn.disabled = true;
        if (btnEspelhar) btnEspelhar.disabled = true;
        return;
    }
    
    if (btn) btn.disabled = false;
    if (btnEspelhar) btnEspelhar.disabled = false;
    
    const moduloNome = document.getElementById('moduloSelect')?.options[document.getElementById('moduloSelect').selectedIndex]?.text || '';
    const equipeNome = document.getElementById('equipeSelect')?.options[document.getElementById('equipeSelect').selectedIndex]?.text || '';
    document.getElementById('equipeNomeHeader').textContent = `${equipeNome} - ${moduloNome}`;
    
    try {
        const response = await fetch(`/diretor/servidores-modulo-equipe?modulo_id=${moduloId}&equipe_id=${equipeId}`);
        const data = await response.json();
        servidoresModuloEquipe = data.servidores || [];
    } catch (error) {
        console.error('Erro ao carregar servidores:', error);
        servidoresModuloEquipe = [];
    }
    
    carregarServidoresEquipe();
}

function carregarServidoresEquipe() {
    const equipeId = document.getElementById('equipeSelect')?.value;
    const moduloId = document.getElementById('moduloSelect')?.value;
    
    if (!equipeId || !moduloId) {
        document.getElementById('emptyState').style.display = 'block';
        document.getElementById('tabelaCalendario').style.display = 'none';
        const btnEnviar = document.getElementById('btnEnviar');
        if (btnEnviar) btnEnviar.style.display = 'none';
        return;
    }
    
    if (equipeId === 'todas') {
        servidoresEquipeAtual = escalaServidoresData.map(es => ({
            id: es.servidor_id,
            nome: es.servidor?.nome || 'Servidor',
            matricula: es.servidor?.matricula || '',
            is_lider: es.lider,
            equipe_id: es.equipe_id,
            equipe_nome: es.equipe?.nome || '',
            modulo_id: es.modulo_id,
            total_horas: calcularHorasServidor(es.servidor_id)
        }));
    } else {
        servidoresEquipeAtual = escalaServidoresData
            .filter(es => es.equipe_id == equipeId && es.modulo_id == moduloId)
            .map(es => ({
                id: es.servidor_id,
                nome: es.servidor?.nome || 'Servidor',
                matricula: es.servidor?.matricula || '',
                is_lider: es.lider,
                equipe_id: es.equipe_id,
                equipe_nome: es.equipe?.nome || '',
                modulo_id: es.modulo_id,
                total_horas: calcularHorasServidor(es.servidor_id)
            }));
    }
    
    renderizarCalendario();
}

function calcularHorasServidor(servidorId) {
    return alocacoesData
        .filter(a => a.servidor_id == servidorId)
        .reduce((sum, a) => sum + (parseFloat(a.horas) || 0) + (parseFloat(a.horas_abono) || 0), 0);
}

function calcularHorasPorTipo(servidorId) {
    const alocacoes = alocacoesData.filter(a => a.servidor_id == servidorId);
    const diurna = alocacoes
        .filter(a => (a.tipo_extra || 'diurna') === 'diurna')
        .reduce((sum, a) => sum + (parseFloat(a.horas) || 0), 0);
    const noturna = alocacoes
        .filter(a => a.tipo_extra === 'noturna')
        .reduce((sum, a) => sum + (parseFloat(a.horas) || 0), 0);
    return { diurna, noturna, total: diurna + noturna };
}

function renderizarCalendario() {
    const tbody = document.getElementById('calendarioBody');
    const emptyState = document.getElementById('emptyState');
    const tabela = document.getElementById('tabelaCalendario');
    const btnEnviar = document.getElementById('btnEnviar');
    
    if (servidoresEquipeAtual.length === 0) {
        emptyState.style.display = 'block';
        tabela.style.display = 'none';
        if (btnEnviar) btnEnviar.style.display = 'none';
        return;
    }
    
    emptyState.style.display = 'none';
    tabela.style.display = 'table';
    if (btnEnviar) btnEnviar.style.display = 'inline-block';
    
    const equipeId = document.getElementById('equipeSelect')?.value;
    const mostrarEquipe = (equipeId === 'todas');
    
    let html = '';
    servidoresEquipeAtual.forEach(servidor => {
        const horasPorTipo = calcularHorasPorTipo(servidor.id);
        horasMap[servidor.id] = horasPorTipo.total;
        const horasClass = horasPorTipo.total >= limiteHoras ? 'text-danger' : 'text-success';
        const noturnasClass = horasPorTipo.noturna >= 20 ? 'text-danger' : 'text-primary';
        const equipeNome = servidor.equipe_nome || '';
        
        html += `<tr class="servidor-row" data-servidor-id="${servidor.id}">
            <td class="servidor-info">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="fw-semibold small">${servidor.nome}</div>
                        <small class="text-muted">${servidor.matricula}${mostrarEquipe ? ' - <span class="badge bg-primary badge-equipe">' + equipeNome + '</span>' : ''}</small>
                    </div>
                    ${podeEditar ? `<button class="btn btn-sm btn-outline-danger btn-remover-servidor" onclick="removerServidorEquipe(${servidor.id})" title="Remover da equipe">
                        <i class="bi bi-x"></i>
                    </button>` : ''}
                </div>
            </td>
            <td class="text-center">
                <span class="badge bg-light horas-servidor" id="horas-${servidor.id}">
                    <span class="text-warning">${horasPorTipo.diurna}</span>/<span class="${noturnasClass}">${horasPorTipo.noturna}</span>/<span class="${horasClass}">${horasPorTipo.total}h</span>
                </span>
            </td>
            <td class="text-center">
                <input type="checkbox" class="form-check-input" ${servidor.is_lider ? 'checked' : ''} 
                    onchange="atualizarLider(${servidor.id}, this.checked)" ${!podeEditar ? 'disabled' : ''}>
            </td>`;
        
        for (let d = 1; d <= diasNoMes; d++) {
            const info = diasInfo[d];
            let classe = '';
            
            if (info.isFeriado) classe = 'feriado';
            else if (info.diaSemana === 0) classe = 'domingo';
            else if (info.diaSemana === 6) classe = 'sabado';
            
            const alocacao = alocacoesData.find(a => a.servidor_id == servidor.id && a.dia == d);
            const isAlocado = !!alocacao;
            const tipoAlocado = alocacao?.tipo_extra || 'diurna';
            if (isAlocado) {
                classe += ' alocado';
                classe += tipoAlocado === 'diurna' ? ' alocado-diurna' : ' alocado-noturna';
            }
            
            html += `<td>
                <div class="dia-cell ${classe}" 
                     data-dia="${d}" 
                     data-servidor="${servidor.id}"
                     data-alocado="${isAlocado ? '1' : '0'}"
                     data-tipo-extra="${isAlocado ? tipoAlocado : ''}"
                     title="${info.isFeriado ? info.nomeFeriado : info.nomeDia + ', dia ' + d}${isAlocado ? ' - Alocado: ' + alocacao.horas + 'h (' + tipoAlocado.toUpperCase() + ')' : ''}"
                     onclick="${podeEditar ? 'toggleDia(this)' : ''}">
                    ${d}
                </div>
            </td>`;
        }
        
        html += '</tr>';
    });
    
    tbody.innerHTML = html;
}

async function toggleDia(el) {
    if (!podeEditar) return;
    
    const dia = parseInt(el.dataset.dia);
    const servidorId = parseInt(el.dataset.servidor);
    const alocado = el.dataset.alocado === '1';
    
    const equipe = document.getElementById('equipeSelect')?.value;
    const modulo = document.getElementById('moduloSelect')?.value;
    const horas = parseFloat(document.getElementById('horasInput')?.value || 10);
    const abono = parseFloat(document.getElementById('abonoInput')?.value || 0);
    const tipoExtra = document.getElementById('tipoExtraSelect')?.value || 'diurna';
    
    if (!equipe || equipe === 'todas') {
        alert('Selecione uma equipe primeiro.');
        return;
    }
    
    if (!modulo) {
        alert('Selecione um módulo primeiro.');
        return;
    }
    
    if (alocado) {
        if (confirm('Remover alocação deste dia?')) {
            await removerAlocacaoDia(servidorId, dia, el, horas, abono);
        }
        return;
    }
    
    const horasServidor = calcularHorasPorTipo(servidorId);
    if (tipoExtra === 'diurna' && horasServidor.diurna + horas > 60) {
        alert('Limite de 60h diurnas atingido para este servidor.');
        return;
    }
    if (tipoExtra === 'noturna' && horasServidor.noturna + horas > 20) {
        alert('Limite de 20h noturnas atingido para este servidor.');
        return;
    }
    if (horasServidor.total + horas > 60) {
        alert('Limite total de 60h atingido para este servidor.');
        return;
    }
    
    el.style.opacity = '0.5';
    el.style.pointerEvents = 'none';
    
    const form = new FormData();
    form.append('escala_id', escalaId);
    form.append('servidor_id', servidorId);
    form.append('equipe_id', equipe);
    form.append('modulo_id', modulo);
    form.append('dia', dia);
    form.append('horas', horas);
    form.append('horas_abono', abono);
    form.append('tipo_extra', tipoExtra);
    
    try {
        const response = await fetch('/diretor/alocar-dia', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            body: form
        });
        
        const result = await response.json();
        
        if (result.error) {
            alert(result.message || 'Erro ao alocar dia.');
        } else if (result.added) {
            el.dataset.alocado = '1';
            el.dataset.tipoExtra = tipoExtra;
            el.classList.add('alocado');
            el.classList.remove('alocado-diurna', 'alocado-noturna');
            el.classList.add(tipoExtra === 'diurna' ? 'alocado-diurna' : 'alocado-noturna');
            el.title = `Alocado: ${horas}h (${tipoExtra.toUpperCase()})`;
            
            alocacoesData.push({
                servidor_id: servidorId,
                dia: dia,
                horas: horas,
                horas_abono: abono,
                tipo_extra: tipoExtra
            });
            
            horasMap[servidorId] = (horasMap[servidorId] || 0) + horas + abono;
            atualizarHorasDisplay(servidorId);
        } else {
            alert('Erro ao alocar dia.');
        }
    } catch (error) {
        alert('Erro de conexão.');
    }
    
    el.style.opacity = '1';
    el.style.pointerEvents = 'auto';
}

async function removerAlocacaoDia(servidorId, dia, el, horas, abono) {
    const form = new FormData();
    form.append('escala_id', escalaId);
    form.append('servidor_id', servidorId);
    form.append('data', `{{ $ano }}-{{ str_pad($mes, 2, '0', STR_PAD_LEFT) }}-${String(dia).padStart(2, '0')}`);
    
    try {
        const response = await fetch('/diretor/alocar-dia', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            body: form
        });
        
        const result = await response.json();
        
        if (result.removed) {
            el.dataset.alocado = '0';
            el.dataset.tipoExtra = '';
            el.classList.remove('alocado', 'alocado-diurna', 'alocado-noturna');
            
            const alocacao = alocacoesData.find(a => a.servidor_id == servidorId && a.dia == dia);
            if (alocacao) {
                const idx = alocacoesData.indexOf(alocacao);
                if (idx > -1) alocacoesData.splice(idx, 1);
            }
            atualizarHorasDisplay(servidorId);
        }
    } catch (error) {
        alert('Erro ao remover alocação.');
    }
}

function atualizarHorasDisplay(servidorId) {
    const el = document.getElementById(`horas-${servidorId}`);
    if (el) {
        const horasPorTipo = calcularHorasPorTipo(servidorId);
        horasMap[servidorId] = horasPorTipo.total;
        const horasClass = horasPorTipo.total >= limiteHoras ? 'text-danger' : 'text-success';
        const noturnasClass = horasPorTipo.noturna >= 20 ? 'text-danger' : 'text-primary';
        el.innerHTML = `<span class="text-warning">${horasPorTipo.diurna}</span>/<span class="${noturnasClass}">${horasPorTipo.noturna}</span>/<span class="${horasClass}">${horasPorTipo.total}h</span>`;
    }
}

function abrirModalServidores() {
    servidoresSelecionadosModal.clear();
    document.getElementById('buscarServidor').value = '';
    
    const servidoresJaNaEscala = new Set(escalaServidoresData.map(es => es.servidor_id));
    
    let html = '';
    
    if (todosServidores.length === 0) {
        html = `<div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-2"></i>
            Nenhum servidor cadastrado na unidade.
        </div>`;
    } else {
        todosServidores.forEach(s => {
            const jaNaEscala = servidoresJaNaEscala.has(s.id);
            html += `<div class="servidor-item ${jaNaEscala ? 'disabled' : ''}" 
                          data-servidor-id="${s.id}"
                          data-nome="${(s.nome || '').toLowerCase()}"
                          data-matricula="${(s.matricula || '').toLowerCase()}"
                          onclick="${jaNaEscala ? 'alertarServidorJaEscalado()' : 'toggleServidorModal(this)'}">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-semibold">${s.nome}</div>
                        <small class="text-muted">${s.matricula}</small>
                    </div>
                    ${jaNaEscala ? '<span class="badge bg-warning text-dark">Já escalado</span>' : '<i class="bi bi-check-circle text-success" style="display:none;"></i>'}
                </div>
            </div>`;
        });
    }
    
    document.getElementById('listaServidoresModal').innerHTML = html;
    new bootstrap.Modal(document.getElementById('modalServidores')).show();
}

function alertarServidorJaEscalado() {
    alert('Este servidor já está escalado neste mês.');
}

function toggleServidorModal(el) {
    const servidorId = parseInt(el.dataset.servidorId);
    if (servidoresSelecionadosModal.has(servidorId)) {
        servidoresSelecionadosModal.delete(servidorId);
        el.classList.remove('selected');
        el.querySelector('.bi-check-circle').style.display = 'none';
    } else {
        servidoresSelecionadosModal.add(servidorId);
        el.classList.add('selected');
        el.querySelector('.bi-check-circle').style.display = 'inline';
    }
}

async function salvarServidoresSelecionados() {
    if (servidoresSelecionadosModal.size === 0) {
        alert('Selecione pelo menos um servidor.');
        return;
    }
    
    const equipeId = document.getElementById('equipeSelect').value;
    const moduloId = document.getElementById('moduloSelect').value;
    
    for (const servidorId of servidoresSelecionadosModal) {
        const form = new FormData();
        form.append('escala_id', escalaId);
        form.append('equipe_id', equipeId);
        form.append('servidor_id', servidorId);
        form.append('modulo_id', moduloId);
        
        await fetch('/diretor/adicionar-servidor', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            body: form
        });
    }
    
    location.reload();
}

async function removerServidorEquipe(servidorId) {
    if (!confirm('Remover servidor da equipe?')) return;
    
    const form = new FormData();
    form.append('escala_id', escalaId);
    form.append('servidor_id', servidorId);
    
    await fetch('/diretor/remover-servidor', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken
        },
        body: form
    });
    
    location.reload();
}

function atualizarLider(servidorId, isLider) {
    console.log('Atualizando líder:', servidorId, isLider);
}

document.getElementById('buscarServidor')?.addEventListener('input', function() {
    const termo = this.value.toLowerCase().trim();
    document.querySelectorAll('#listaServidoresModal .servidor-item').forEach(el => {
        const nome = el.dataset.nome || '';
        const matricula = el.dataset.matricula || '';
        const encontrado = nome.includes(termo) || matricula.includes(termo);
        el.style.display = encontrado ? 'block' : 'none';
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const equipeSelect = document.getElementById('equipeSelect');
    if (equipeSelect && (equipeSelect.value === 'todas' || !podeEditar)) {
        carregarServidoresEquipe();
    }
});

function abrirModalIncluirServidor() {
    document.getElementById('novoServidorMatricula').value = '';
    document.getElementById('novoServidorNome').value = '';
    document.getElementById('novoServidorCargo').value = '';
    
    bootstrap.Modal.getInstance(document.getElementById('modalServidores'))?.hide();
    
    setTimeout(() => {
        new bootstrap.Modal(document.getElementById('modalIncluirServidor')).show();
    }, 300);
}

async function enviarSolicitacaoServidor() {
    const matricula = document.getElementById('novoServidorMatricula').value.trim();
    const nome = document.getElementById('novoServidorNome').value.trim();
    const cargo = document.getElementById('novoServidorCargo').value.trim();
    
    if (!matricula || !nome) {
        alert('Preencha a matrícula e o nome do servidor.');
        return;
    }
    
    const form = new FormData();
    form.append('matricula', matricula);
    form.append('nome', nome);
    form.append('cargo', cargo);
    
    try {
        const response = await fetch('/diretor/solicitar-inclusao-servidor', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            body: form
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert(data.message);
            bootstrap.Modal.getInstance(document.getElementById('modalIncluirServidor'))?.hide();
        } else {
            alert(data.message || 'Erro ao enviar solicitação.');
        }
    } catch (error) {
        alert('Erro ao enviar solicitação. Tente novamente.');
    }
}

let servidoresEspelhar = [];
const mesesNomes = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 
                    'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];

function abrirModalEspelhar() {
    document.getElementById('espelharPreview').style.display = 'none';
    document.getElementById('espelharLoading').style.display = 'none';
    document.getElementById('espelharEmpty').style.display = 'none';
    document.getElementById('btnConfirmarEspelhar').disabled = true;
    servidoresEspelhar = [];
    
    const moduloDestinoSelect = document.getElementById('moduloSelect');
    const equipeDestinoSelect = document.getElementById('equipeSelect');
    
    const moduloDestinoNome = moduloDestinoSelect.options[moduloDestinoSelect.selectedIndex]?.text || '-';
    const equipeDestinoNome = equipeDestinoSelect.options[equipeDestinoSelect.selectedIndex]?.text || '-';
    
    document.getElementById('espelharDestinoModulo').value = moduloDestinoNome;
    document.getElementById('espelharDestinoEquipe').value = equipeDestinoNome;
    document.getElementById('espelharDestinoMesAno').value = `${mesesNomes[mesAtual - 1]}/${anoAtual}`;
    
    new bootstrap.Modal(document.getElementById('modalEspelhar')).show();
}

async function buscarServidoresEspelhar() {
    const mesRef = document.getElementById('espelharMes').value;
    const anoRef = document.getElementById('espelharAno').value;
    const moduloOrigemId = document.getElementById('espelharModulo').value;
    const equipeOrigemId = document.getElementById('espelharEquipe').value;
    
    const moduloDestinoId = document.getElementById('moduloSelect').value;
    const equipeDestinoId = document.getElementById('equipeSelect').value;
    
    if (!moduloOrigemId || !equipeOrigemId) {
        alert('Selecione o Módulo e Equipe de Referência (origem).');
        return;
    }
    
    document.getElementById('espelharPreview').style.display = 'none';
    document.getElementById('espelharEmpty').style.display = 'none';
    document.getElementById('espelharLoading').style.display = 'block';
    document.getElementById('btnConfirmarEspelhar').disabled = true;
    
    try {
        const response = await fetch(`/diretor/servidores-escala-anterior?mes=${mesRef}&ano=${anoRef}&modulo_id=${moduloOrigemId}&equipe_id=${equipeOrigemId}`);
        const data = await response.json();
        
        document.getElementById('espelharLoading').style.display = 'none';
        
        if (data.servidores && data.servidores.length > 0) {
            servidoresEspelhar = data.servidores;
            
            let html = '';
            servidoresEspelhar.forEach(s => {
                const jaAdicionado = escalaServidoresData.some(es => 
                    es.servidor_id == s.id && es.equipe_id == equipeDestinoId && es.modulo_id == moduloDestinoId
                );
                
                html += `<div class="servidor-item ${jaAdicionado ? 'disabled' : ''}" data-id="${s.id}">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-semibold">${s.nome}</div>
                            <small class="text-muted">${s.matricula}</small>
                        </div>
                        ${jaAdicionado ? '<span class="badge bg-secondary">Já adicionado</span>' : '<i class="bi bi-check-circle text-success"></i>'}
                    </div>
                </div>`;
            });
            
            document.getElementById('espelharServidoresLista').innerHTML = html;
            document.getElementById('espelharPreview').style.display = 'block';
            
            const novos = servidoresEspelhar.filter(s => 
                !escalaServidoresData.some(es => 
                    es.servidor_id == s.id && es.equipe_id == equipeDestinoId && es.modulo_id == moduloDestinoId
                )
            );
            document.getElementById('btnConfirmarEspelhar').disabled = novos.length === 0;
        } else {
            document.getElementById('espelharEmpty').style.display = 'block';
        }
    } catch (error) {
        console.error('Erro ao buscar servidores:', error);
        document.getElementById('espelharLoading').style.display = 'none';
        document.getElementById('espelharEmpty').style.display = 'block';
    }
}

async function confirmarEspelhar() {
    const moduloId = document.getElementById('moduloSelect').value;
    const equipeId = document.getElementById('equipeSelect').value;
    
    const novosServidores = servidoresEspelhar.filter(s => 
        !escalaServidoresData.some(es => 
            es.servidor_id == s.id && es.equipe_id == equipeId && es.modulo_id == moduloId
        )
    );
    
    if (novosServidores.length === 0) {
        alert('Nenhum servidor novo para adicionar.');
        return;
    }
    
    document.getElementById('btnConfirmarEspelhar').disabled = true;
    document.getElementById('btnConfirmarEspelhar').innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Adicionando...';
    
    let adicionados = 0;
    
    for (const servidor of novosServidores) {
        try {
            const form = new FormData();
            form.append('escala_id', escalaId);
            form.append('servidor_id', servidor.id);
            form.append('equipe_id', equipeId);
            form.append('modulo_id', moduloId);
            form.append('lider', '0');
            
            const response = await fetch('/diretor/adicionar-servidor-escala', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                body: form
            });
            
            const data = await response.json();
            if (data.success) {
                adicionados++;
                escalaServidoresData.push({
                    servidor_id: servidor.id,
                    equipe_id: parseInt(equipeId),
                    modulo_id: parseInt(moduloId),
                    lider: false,
                    servidor: { id: servidor.id, nome: servidor.nome, matricula: servidor.matricula },
                    equipe: { id: parseInt(equipeId), nome: '' },
                    modulo: { id: parseInt(moduloId), nome: '' }
                });
            }
        } catch (error) {
            console.error('Erro ao adicionar servidor:', error);
        }
    }
    
    document.getElementById('btnConfirmarEspelhar').innerHTML = '<i class="bi bi-copy me-1"></i> Espelhar';
    bootstrap.Modal.getInstance(document.getElementById('modalEspelhar'))?.hide();
    
    alert(`${adicionados} servidor(es) adicionado(s) com sucesso!`);
    
    carregarServidoresEquipe();
}
</script>
@endpush
