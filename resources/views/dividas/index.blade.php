@extends('layouts.app')

@section('page-title', 'Dívidas')

@section('page-actions')
    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalNovaDivida">
        <i class="bi bi-plus-lg"></i> <span class="d-none d-sm-inline">Nova Dívida</span>
    </button>
@endsection

@section('content')

{{-- Cards de Resumo --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card glow-purple" style="border-left: 4px solid #a855f7;">
            <div class="card-body py-3">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <i class="bi bi-credit-card" style="color:#a855f7;"></i>
                    <small class="text-muted">Total de Dívidas</small>
                </div>
                <div class="card-title h4 mb-0">{{ $totalDividas }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card card-despesa glow-red">
            <div class="card-body py-3">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <i class="bi bi-exclamation-circle" style="color:#ff4757;"></i>
                    <small class="text-muted">Total Devido</small>
                </div>
                <div class="card-title h4 mb-0 valor-negativo">
                    R$ {{ number_format($totalDevido, 2, ',', '.') }}
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card card-receita glow-green">
            <div class="card-body py-3">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <i class="bi bi-check-circle" style="color:#00ff88;"></i>
                    <small class="text-muted">Total Pago</small>
                </div>
                <div class="card-title h4 mb-0 valor-positivo">
                    R$ {{ number_format($totalPago, 2, ',', '.') }}
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card" style="border-left: 4px solid #ffc107;">
            <div class="card-body py-3">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <i class="bi bi-clock" style="color:#ffc107;"></i>
                    <small class="text-muted">Em Atraso</small>
                </div>
                <div class="card-title h4 mb-0" style="color:#ffc107;">
                    R$ {{ number_format($totalEmAtraso, 2, ',', '.') }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Lista de Dividas --}}
@if($dividas->isEmpty())
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-credit-card" style="font-size: 3rem; color:#666;"></i>
            <p class="mt-3 text-muted">Nenhuma dívida cadastrada.</p>
            <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#modalNovaDivida">
                <i class="bi bi-plus-lg"></i> Cadastrar Dívida
            </button>
        </div>
    </div>
@else
    <div class="row g-3">
        @foreach($dividas as $divida)
        @php
            $pagamentos = $divida->pagamentos ?? [];
            $valorPago = $divida->valor_pago_calc ?? collect($pagamentos)->sum('valor');
            $valorRestante = $divida->valor_restante_calc ?? max(0, $divida->valor_total - $valorPago);
            $percentual = $divida->percentual_calc ?? ($divida->valor_total > 0 ? min(100, round(($valorPago / $divida->valor_total) * 100, 1)) : 0);

            $statusColor = match($divida->status) {
                'quitada'   => '#00ff88',
                'em_atraso' => '#ffc107',
                default     => '#3742fa',
            };
            $statusLabel = match($divida->status) {
                'quitada'   => 'Quitada',
                'em_atraso' => 'Em Atraso',
                default     => 'Ativa',
            };
            $statusBg = match($divida->status) {
                'quitada'   => 'rgba(0,255,136,0.15)',
                'em_atraso' => 'rgba(255,193,7,0.15)',
                default     => 'rgba(55,66,250,0.15)',
            };
            $barColor = match($divida->status) {
                'quitada'   => 'bg-success',
                'em_atraso' => 'bg-warning',
                default     => 'bg-primary',
            };
        @endphp
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card h-100" style="border-left: 4px solid {{ $statusColor }};">
                <div class="card-body">
                    {{-- Header --}}
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="flex-grow-1 me-2">
                            <h6 class="mb-0 fw-bold">{{ $divida->descricao }}</h6>
                            @if($divida->credor)
                                <small class="text-muted"><i class="bi bi-person"></i> {{ $divida->credor }}</small>
                            @endif
                        </div>
                        <span class="badge rounded-pill" style="background:{{ $statusBg }}; color:{{ $statusColor }}; border: 1px solid {{ $statusColor }}40;">
                            {{ $statusLabel }}
                        </span>
                    </div>

                    {{-- Categoria --}}
                    @if($divida->categoria)
                        <div class="mb-2">
                            <span class="badge" style="background:rgba(255,255,255,0.08); color:#aaa;">
                                <i class="bi bi-tag"></i> {{ $divida->categoria }}
                            </span>
                        </div>
                    @endif

                    {{-- Valores --}}
                    <div class="row g-2 mb-3">
                        <div class="col-4 text-center">
                            <div class="small text-muted">Total</div>
                            <div class="fw-bold valor-negativo" style="font-size:0.9rem;">
                                R$ {{ number_format($divida->valor_total, 2, ',', '.') }}
                            </div>
                        </div>
                        <div class="col-4 text-center">
                            <div class="small text-muted">Pago</div>
                            <div class="fw-bold valor-positivo" style="font-size:0.9rem;">
                                R$ {{ number_format($valorPago, 2, ',', '.') }}
                            </div>
                        </div>
                        <div class="col-4 text-center">
                            <div class="small text-muted">Restante</div>
                            <div class="fw-bold" style="font-size:0.9rem; color:{{ $statusColor }};">
                                R$ {{ number_format($valorRestante, 2, ',', '.') }}
                            </div>
                        </div>
                    </div>

                    {{-- Progress Bar --}}
                    <div class="mb-2">
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted">Progresso</small>
                            <small style="color:{{ $statusColor }};">{{ $percentual }}%</small>
                        </div>
                        <div class="progress" style="height:8px;">
                            <div class="progress-bar {{ $barColor }}" role="progressbar"
                                 style="width:{{ $percentual }}%; background: linear-gradient(90deg, {{ $statusColor }}99, {{ $statusColor }}) !important;"
                                 aria-valuenow="{{ $percentual }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>

                    {{-- Datas --}}
                    <div class="d-flex gap-3 mb-3">
                        @if($divida->data_inicio)
                            <small class="text-muted">
                                <i class="bi bi-calendar-plus"></i>
                                {{ \Carbon\Carbon::parse($divida->data_inicio)->format('d/m/Y') }}
                            </small>
                        @endif
                        @if($divida->data_vencimento)
                            <small class="{{ $divida->status === 'em_atraso' ? '' : 'text-muted' }}" style="{{ $divida->status === 'em_atraso' ? 'color:#ffc107;' : '' }}">
                                <i class="bi bi-calendar-x"></i>
                                Vence: {{ \Carbon\Carbon::parse($divida->data_vencimento)->format('d/m/Y') }}
                            </small>
                        @endif
                    </div>

                    {{-- Pagamentos count --}}
                    <div class="mb-3">
                        <small class="text-muted">
                            <i class="bi bi-receipt"></i>
                            {{ count($pagamentos) }} pagamento(s) registrado(s)
                        </small>
                    </div>

                    {{-- Acoes --}}
                    <div class="d-flex gap-2 flex-wrap">
                        @if($divida->status !== 'quitada')
                            <button class="btn btn-sm btn-success"
                                    onclick="abrirModalPagamento('{{ $divida->_id }}', '{{ addslashes($divida->descricao) }}', {{ $valorRestante }})"
                                    title="Registrar Pagamento">
                                <i class="bi bi-cash-coin"></i> Pagar
                            </button>
                        @endif
                        <button class="btn btn-sm btn-outline-warning"
                                onclick="abrirModalPagamentos('{{ $divida->_id }}', '{{ addslashes($divida->descricao) }}', {{ json_encode($pagamentos) }})"
                                title="Ver Pagamentos">
                            <i class="bi bi-list-ul"></i> Pagamentos
                        </button>
                        <button class="btn btn-sm btn-outline-secondary"
                                onclick="abrirModalEditar(
                                    '{{ $divida->_id }}',
                                    '{{ addslashes($divida->descricao) }}',
                                    '{{ addslashes($divida->credor ?? '') }}',
                                    {{ $divida->valor_total }},
                                    '{{ $divida->data_inicio ? \Carbon\Carbon::parse($divida->data_inicio)->format('Y-m-d') : '' }}',
                                    '{{ $divida->data_vencimento ? \Carbon\Carbon::parse($divida->data_vencimento)->format('Y-m-d') : '' }}',
                                    '{{ addslashes($divida->categoria ?? '') }}',
                                    '{{ addslashes($divida->observacoes ?? '') }}'
                                )"
                                title="Editar">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger"
                                onclick="confirmarExclusao('{{ $divida->_id }}', '{{ addslashes($divida->descricao) }}')"
                                title="Excluir">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>

                    {{-- Observacoes --}}
                    @if($divida->observacoes)
                        <div class="mt-2 p-2 rounded" style="background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.06);">
                            <small class="text-muted"><i class="bi bi-info-circle"></i> {{ $divida->observacoes }}</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
@endif

{{-- ===== MODAL NOVA DIVIDA ===== --}}
<div class="modal fade" id="modalNovaDivida" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('dividas.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-credit-card me-2" style="color:#ffc107;"></i>Nova Dívida</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-8">
                            <label class="form-label">Descrição *</label>
                            <input type="text" name="descricao" class="form-control" placeholder="Ex: Financiamento do carro" required maxlength="255">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Credor</label>
                            <input type="text" name="credor" class="form-control" placeholder="Ex: Banco Itaú" maxlength="255">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Valor Total *</label>
                            <div class="input-group">
                                <span class="input-group-text" style="background:rgba(20,20,35,0.8);color:#aaa;border-color:rgba(255,255,255,0.08);">R$</span>
                                <input type="number" name="valor_total" class="form-control" placeholder="0,00" step="0.01" min="0.01" required>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Data de Início *</label>
                            <input type="date" name="data_inicio" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Data de Vencimento</label>
                            <input type="date" name="data_vencimento" class="form-control">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Categoria</label>
                            <input type="text" name="categoria" class="form-control" list="listaCategoriasDivida" placeholder="Selecione ou digite" maxlength="100">
                            <datalist id="listaCategoriasDivida">
                                @foreach($categorias as $cat)
                                    <option value="{{ $cat->nome }}">
                                @endforeach
                            </datalist>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Observações</label>
                            <input type="text" name="observacoes" class="form-control" placeholder="Notas adicionais..." maxlength="500">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-plus-lg"></i> Cadastrar Dívida
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ===== MODAL REGISTRAR PAGAMENTO ===== --}}
<div class="modal fade" id="modalPagamento" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formPagamento" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-cash-coin me-2" style="color:#00ff88;"></i>Registrar Pagamento</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-3" id="labelDividaPagamento"></p>
                    <div class="alert alert-info mb-3" id="infoRestante" style="display:none;">
                        <i class="bi bi-info-circle"></i> Valor restante: <strong id="textoRestante"></strong>
                    </div>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Valor do Pagamento *</label>
                            <div class="input-group">
                                <span class="input-group-text" style="background:rgba(20,20,35,0.8);color:#aaa;border-color:rgba(255,255,255,0.08);">R$</span>
                                <input type="number" name="valor" id="valorPagamento" class="form-control" placeholder="0,00" step="0.01" min="0.01" required>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Data do Pagamento *</label>
                            <input type="date" name="data" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descrição (opcional)</label>
                            <input type="text" name="descricao" class="form-control" placeholder="Ex: Parcela 3, Pagamento parcial..." maxlength="255">
                        </div>
                    </div>
                    <div class="alert alert-success mt-3 mb-0">
                        <i class="bi bi-arrow-right-circle"></i>
                        Uma <strong>despesa</strong> será criada automaticamente ao registrar o pagamento.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-lg"></i> Registrar Pagamento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ===== MODAL VER PAGAMENTOS ===== --}}
<div class="modal fade" id="modalVerPagamentos" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-list-ul me-2" style="color:#3742fa;"></i>Histórico de Pagamentos</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6 class="text-muted mb-3" id="labelDividaHistorico"></h6>
                <div id="listaPagamentos"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

{{-- ===== MODAL EDITAR DIVIDA ===== --}}
<div class="modal fade" id="modalEditarDivida" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="formEditarDivida" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil me-2" style="color:#ffc107;"></i>Editar Dívida</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-8">
                            <label class="form-label">Descrição *</label>
                            <input type="text" name="descricao" id="editDescricao" class="form-control" required maxlength="255">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Credor</label>
                            <input type="text" name="credor" id="editCredor" class="form-control" maxlength="255">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Valor Total *</label>
                            <div class="input-group">
                                <span class="input-group-text" style="background:rgba(20,20,35,0.8);color:#aaa;border-color:rgba(255,255,255,0.08);">R$</span>
                                <input type="number" name="valor_total" id="editValorTotal" class="form-control" step="0.01" min="0.01" required>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Data de Início *</label>
                            <input type="date" name="data_inicio" id="editDataInicio" class="form-control" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Data de Vencimento</label>
                            <input type="date" name="data_vencimento" id="editDataVencimento" class="form-control">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Categoria</label>
                            <input type="text" name="categoria" id="editCategoria" class="form-control" list="listaCategoriasDividaEdit" maxlength="100">
                            <datalist id="listaCategoriasDividaEdit">
                                @foreach($categorias as $cat)
                                    <option value="{{ $cat->nome }}">
                                @endforeach
                            </datalist>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Observações</label>
                            <input type="text" name="observacoes" id="editObservacoes" class="form-control" maxlength="500">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-save"></i> Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ===== FORM EXCLUSAO ===== --}}
<form id="formExcluirDivida" method="POST" style="display:none;">
    @csrf
    @method('DELETE')
</form>

@endsection

@section('scripts')
<script>
function abrirModalPagamento(dividaId, descricao, valorRestante) {
    const form = document.getElementById('formPagamento');
    form.action = '/dividas/' + dividaId + '/pagamentos';
    document.getElementById('labelDividaPagamento').textContent = 'Dívida: ' + descricao;

    if (valorRestante > 0) {
        document.getElementById('infoRestante').style.display = 'block';
        document.getElementById('textoRestante').textContent = 'R$ ' + valorRestante.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('valorPagamento').value = valorRestante.toFixed(2);
    } else {
        document.getElementById('infoRestante').style.display = 'none';
        document.getElementById('valorPagamento').value = '';
    }

    new bootstrap.Modal(document.getElementById('modalPagamento')).show();
}

function abrirModalPagamentos(dividaId, descricao, pagamentos) {
    document.getElementById('labelDividaHistorico').textContent = descricao;

    let html = '';
    if (!pagamentos || pagamentos.length === 0) {
        html = '<div class="text-center py-4 text-muted"><i class="bi bi-inbox" style="font-size:2rem;"></i><p class="mt-2">Nenhum pagamento registrado.</p></div>';
    } else {
        // Sort by date descending
        const sorted = [...pagamentos].sort((a, b) => new Date(b.data) - new Date(a.data));
        html = '<div class="table-responsive"><table class="table table-hover">';
        html += '<thead><tr><th>Data</th><th>Valor</th><th>Descrição</th><th>Despesa</th><th></th></tr></thead><tbody>';
        sorted.forEach(p => {
            const data = p.data ? new Date(p.data + 'T00:00:00').toLocaleDateString('pt-BR') : '-';
            const valor = parseFloat(p.valor || 0).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            const desc = p.descricao || '-';
            const hasDespesa = p.despesa_id ? '<span class="badge" style="background:rgba(0,255,136,0.15);color:#00ff88;"><i class="bi bi-check"></i> Despesa criada</span>' : '-';
            html += `<tr>
                <td>${data}</td>
                <td class="valor-positivo">R$ ${valor}</td>
                <td>${desc}</td>
                <td>${hasDespesa}</td>
                <td>
                    <form action="/dividas/${dividaId}/pagamentos/${p.id}" method="POST" onsubmit="return confirm('Remover este pagamento? A despesa vinculada também será excluída.')">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Remover pagamento">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>`;
        });
        html += '</tbody></table></div>';

        const total = pagamentos.reduce((sum, p) => sum + parseFloat(p.valor || 0), 0);
        html += `<div class="text-end mt-2"><strong class="valor-positivo">Total Pago: R$ ${total.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong></div>`;
    }

    document.getElementById('listaPagamentos').innerHTML = html;
    new bootstrap.Modal(document.getElementById('modalVerPagamentos')).show();
}

function abrirModalEditar(id, descricao, credor, valorTotal, dataInicio, dataVencimento, categoria, observacoes) {
    document.getElementById('formEditarDivida').action = '/dividas/' + id;
    document.getElementById('editDescricao').value = descricao;
    document.getElementById('editCredor').value = credor;
    document.getElementById('editValorTotal').value = valorTotal;
    document.getElementById('editDataInicio').value = dataInicio;
    document.getElementById('editDataVencimento').value = dataVencimento;
    document.getElementById('editCategoria').value = categoria;
    document.getElementById('editObservacoes').value = observacoes;

    new bootstrap.Modal(document.getElementById('modalEditarDivida')).show();
}

function confirmarExclusao(id, descricao) {
    if (confirm('Excluir a dívida "' + descricao + '"?\n\nTodos os pagamentos e as despesas vinculadas serão removidos.')) {
        const form = document.getElementById('formExcluirDivida');
        form.action = '/dividas/' + id;
        form.submit();
    }
}
</script>
@endsection
