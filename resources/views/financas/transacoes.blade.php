@extends('layouts.app')

@section('page-title', 'Transações')

@section('page-actions')
    <a href="{{ route('financas.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left" aria-hidden="true"></i> <span class="d-none d-sm-inline">Dashboard</span>
    </a>
@endsection

@section('content')
<style>
    /* ── Filter bar ──────────────────────────────────────── */
    .filter-bar {
        background: rgba(10,10,20,0.6);
        border: 1px solid rgba(255,255,255,0.07);
        border-radius: 12px;
        padding: 14px 16px;
        margin-bottom: 14px;
    }
    .filter-bar .form-label {
        font-size: 0.65rem;
        text-transform: uppercase;
        letter-spacing: 0.07em;
        color: rgba(255,255,255,0.35);
        margin-bottom: 4px;
    }

    /* ── Active filter chips ─────────────────────────────── */
    .filter-chips { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 12px; }
    .filter-chip {
        display: inline-flex; align-items: center; gap: 5px;
        background: rgba(55,66,250,0.12);
        border: 1px solid rgba(55,66,250,0.35);
        border-radius: 20px;
        padding: 2px 10px 2px 8px;
        font-size: 0.72rem;
        color: #a5b4fc;
    }
    .filter-chip a {
        color: #a5b4fc; line-height: 1; opacity: .6; text-decoration: none;
        transition: opacity 0.15s;
    }
    .filter-chip a:hover { opacity: 1; }

    /* ── Stats bar ───────────────────────────────────────── */
    .stats-bar { display: flex; gap: 10px; margin-bottom: 14px; flex-wrap: wrap; }
    .stat-pill {
        background: rgba(15,15,26,0.8);
        border: 1px solid rgba(255,255,255,0.07);
        border-radius: 10px;
        padding: 8px 16px;
        display: flex; flex-direction: column; gap: 1px;
    }
    .stat-pill .stat-label {
        font-size: 0.6rem; text-transform: uppercase; letter-spacing: 0.08em;
        color: rgba(255,255,255,0.3);
    }
    .stat-pill .stat-value { font-size: 0.9rem; font-weight: 600; }

    /* ── Table ───────────────────────────────────────────── */
    .trans-table thead th {
        background: rgba(8,8,18,0.9);
        color: rgba(255,255,255,0.4);
        font-size: 0.65rem;
        text-transform: uppercase;
        letter-spacing: 0.09em;
        border-bottom: 1px solid rgba(255,255,255,0.07);
        padding: 10px 14px;
        white-space: nowrap;
        position: sticky; top: 0; z-index: 1;
    }
    .trans-table thead th.sortable { cursor: pointer; user-select: none; }
    .trans-table thead th.sortable:hover { color: rgba(255,255,255,0.75); }
    .trans-table thead th .sort-icon { font-size: 0.6rem; opacity: 0.3; margin-left: 3px; }
    .trans-table thead th.sort-asc .sort-icon,
    .trans-table thead th.sort-desc .sort-icon { opacity: 1; color: #00ff88; }

    .trans-table tbody tr {
        border-left: 3px solid transparent;
        transition: background-color 0.12s ease, border-color 0.12s ease;
    }
    .trans-table tbody tr td {
        padding: 11px 14px;
        border-bottom: 1px solid rgba(255,255,255,0.04);
        vertical-align: middle;
    }
    .trans-table tbody tr.row-receita:hover {
        background: rgba(0,255,136,0.04);
        border-left-color: #00ff88;
    }
    .trans-table tbody tr.row-despesa:hover {
        background: rgba(255,71,87,0.04);
        border-left-color: #ff4757;
    }

    /* ── Type badges ─────────────────────────────────────── */
    .badge-rec {
        display: inline-flex; align-items: center; gap: 4px;
        background: rgba(0,255,136,0.12);
        color: #00ff88;
        border: 1px solid rgba(0,255,136,0.25);
        border-radius: 20px; padding: 2px 9px;
        font-size: 0.68rem; font-weight: 600; white-space: nowrap;
    }
    .badge-dep {
        display: inline-flex; align-items: center; gap: 4px;
        background: rgba(255,71,87,0.12);
        color: #ff4757;
        border: 1px solid rgba(255,71,87,0.25);
        border-radius: 20px; padding: 2px 9px;
        font-size: 0.68rem; font-weight: 600; white-space: nowrap;
    }
    .badge-parcela {
        background: rgba(255,193,7,0.12);
        color: #ffc107;
        border: 1px solid rgba(255,193,7,0.25);
        border-radius: 10px; padding: 1px 7px;
        font-size: 0.62rem; font-weight: 600;
    }

    /* ── Category dot ────────────────────────────────────── */
    .cat-dot {
        width: 8px; height: 8px; border-radius: 50%;
        display: inline-block; flex-shrink: 0;
    }
    .cat-name { font-size: 0.78rem; color: rgba(255,255,255,0.65); }

    /* ── Mobile ──────────────────────────────────────────── */
    @media (max-width: 575px) {
        .filter-bar { padding: 12px; }
        .stat-pill { padding: 7px 12px; flex: 1 1 calc(50% - 5px); }
        .stats-bar { gap: 6px; }
    }
</style>

@php
    $temFiltros = array_filter([
        $filtros['busca'],
        $filtros['filtroTipo'],
        $filtros['filtroCategoria'],
        $filtros['dataInicio'],
        $filtros['dataFim'],
    ]);
    $catMap = $categorias->pluck('cor', 'nome');
    $hoje   = now()->startOfDay();
@endphp

{{-- ── Filter bar ──────────────────────────────────────────── --}}
<form method="GET" action="{{ route('financas.transacoes') }}" id="formFiltros">
    @if($filtros['perPage'] != 20)
        <input type="hidden" name="per_page" value="{{ $filtros['perPage'] }}">
    @endif
    <div class="filter-bar">
        <div class="row g-2 align-items-end">
            {{-- Busca --}}
            <div class="col-12 col-lg">
                <label class="form-label">Buscar</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text" style="background:rgba(20,20,35,0.8);border-color:rgba(255,255,255,0.08);color:rgba(255,255,255,0.3);">
                        <i class="bi bi-search" aria-hidden="true"></i>
                    </span>
                    <input type="text" name="busca" class="form-control"
                           value="{{ $filtros['busca'] }}" placeholder="Descrição ou categoria..."
                           aria-label="Buscar transação">
                </div>
            </div>

            {{-- Tipo --}}
            <div class="col-6 col-sm-4 col-lg-auto">
                <label class="form-label">Tipo</label>
                <select name="tipo" class="form-select form-select-sm" style="min-width:120px;" aria-label="Filtrar por tipo">
                    <option value="">Todos</option>
                    <option value="receita"  {{ $filtros['filtroTipo'] === 'receita'  ? 'selected' : '' }}>Receitas</option>
                    <option value="despesa"  {{ $filtros['filtroTipo'] === 'despesa'  ? 'selected' : '' }}>Despesas</option>
                </select>
            </div>

            {{-- Categoria --}}
            <div class="col-6 col-sm-4 col-lg-auto">
                <label class="form-label">Categoria</label>
                <select name="categoria" class="form-select form-select-sm" style="min-width:150px;" aria-label="Filtrar por categoria">
                    <option value="">Todas</option>
                    @foreach($categorias as $cat)
                        <option value="{{ $cat->nome }}" {{ $filtros['filtroCategoria'] === $cat->nome ? 'selected' : '' }}>
                            {{ $cat->nome }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Período --}}
            <div class="col-6 col-sm-4 col-lg-auto">
                <label class="form-label">De</label>
                <input type="date" name="data_inicio" class="form-control form-control-sm"
                       value="{{ $filtros['dataInicio'] }}" aria-label="Data início">
            </div>
            <div class="col-6 col-sm-4 col-lg-auto">
                <label class="form-label">Até</label>
                <input type="date" name="data_fim" class="form-control form-control-sm"
                       value="{{ $filtros['dataFim'] }}" aria-label="Data fim">
            </div>

            {{-- Botões --}}
            <div class="col-12 col-sm-auto">
                <label class="form-label d-none d-sm-block">&nbsp;</label>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-funnel-fill" aria-hidden="true"></i> Filtrar
                    </button>
                    @if($temFiltros)
                        <a href="{{ route('financas.transacoes') }}" class="btn btn-outline-secondary btn-sm" title="Limpar filtros" aria-label="Limpar filtros">
                            <i class="bi bi-x-lg" aria-hidden="true"></i>
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- Atalhos de período --}}
        <div class="d-flex gap-1 mt-2 flex-wrap" role="group" aria-label="Atalhos de período">
            @php
                $atalhos = [
                    'Hoje'       => [now()->toDateString(), now()->toDateString()],
                    'Esta semana'=> [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()],
                    'Este mês'   => [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()],
                    'Últimos 30' => [now()->subDays(30)->toDateString(), now()->toDateString()],
                    'Este ano'   => [now()->startOfYear()->toDateString(), now()->endOfYear()->toDateString()],
                ];
            @endphp
            @foreach($atalhos as $label => [$ini, $fim])
                @php
                    $ativo = $filtros['dataInicio'] === $ini && $filtros['dataFim'] === $fim;
                @endphp
                <a href="{{ route('financas.transacoes', array_filter(array_merge($filtros, ['dataInicio' => $ini, 'dataFim' => $fim, 'data_inicio' => $ini, 'data_fim' => $fim]), fn($v) => $v !== '' && $v !== 20)) }}"
                   class="btn btn-sm {{ $ativo ? 'btn-primary' : 'btn-outline-secondary' }}"
                   style="font-size:0.7rem;padding:3px 10px;border-radius:20px;">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>
</form>

{{-- ── Active filter chips ──────────────────────────────────── --}}
@if($temFiltros)
<div class="filter-chips" aria-label="Filtros ativos">
    @if($filtros['busca'])
        <span class="filter-chip">
            <i class="bi bi-search" aria-hidden="true"></i> "{{ $filtros['busca'] }}"
            <a href="{{ route('financas.transacoes', array_merge(array_filter($filtros, fn($v,$k) => $k !== 'busca' && $v !== '' && $v !== 20, ARRAY_FILTER_USE_BOTH), ['tipo' => $filtros['filtroTipo'], 'categoria' => $filtros['filtroCategoria'], 'data_inicio' => $filtros['dataInicio'], 'data_fim' => $filtros['dataFim']])) }}" aria-label="Remover filtro de busca">×</a>
        </span>
    @endif
    @if($filtros['filtroTipo'])
        <span class="filter-chip">
            <i class="bi bi-tag" aria-hidden="true"></i> {{ ucfirst($filtros['filtroTipo']) . 's' }}
            <a href="{{ route('financas.transacoes', array_filter(['busca' => $filtros['busca'], 'categoria' => $filtros['filtroCategoria'], 'data_inicio' => $filtros['dataInicio'], 'data_fim' => $filtros['dataFim']])) }}" aria-label="Remover filtro de tipo">×</a>
        </span>
    @endif
    @if($filtros['filtroCategoria'])
        <span class="filter-chip">
            @php $cc = $catMap->get($filtros['filtroCategoria'], '#999'); @endphp
            <span class="cat-dot" style="background:{{ $cc }};"></span> {{ $filtros['filtroCategoria'] }}
            <a href="{{ route('financas.transacoes', array_filter(['busca' => $filtros['busca'], 'tipo' => $filtros['filtroTipo'], 'data_inicio' => $filtros['dataInicio'], 'data_fim' => $filtros['dataFim']])) }}" aria-label="Remover filtro de categoria">×</a>
        </span>
    @endif
    @if($filtros['dataInicio'] || $filtros['dataFim'])
        <span class="filter-chip">
            <i class="bi bi-calendar3" aria-hidden="true"></i>
            {{ $filtros['dataInicio'] ? \Carbon\Carbon::parse($filtros['dataInicio'])->format('d/m/y') : '…' }}
            →
            {{ $filtros['dataFim'] ? \Carbon\Carbon::parse($filtros['dataFim'])->format('d/m/y') : '…' }}
            <a href="{{ route('financas.transacoes', array_filter(['busca' => $filtros['busca'], 'tipo' => $filtros['filtroTipo'], 'categoria' => $filtros['filtroCategoria']])) }}" aria-label="Remover filtro de período">×</a>
        </span>
    @endif
</div>
@endif

{{-- ── Stats bar ────────────────────────────────────────────── --}}
<div class="stats-bar" role="region" aria-label="Resumo das transações">
    <div class="stat-pill">
        <span class="stat-label"><i class="bi bi-arrow-up-circle" aria-hidden="true"></i> Receitas</span>
        <span class="stat-value valor-positivo">R$ {{ number_format($totalReceitasFiltrado, 2, ',', '.') }}</span>
    </div>
    <div class="stat-pill">
        <span class="stat-label"><i class="bi bi-arrow-down-circle" aria-hidden="true"></i> Despesas</span>
        <span class="stat-value valor-negativo">R$ {{ number_format($totalDespesasFiltrado, 2, ',', '.') }}</span>
    </div>
    <div class="stat-pill">
        <span class="stat-label"><i class="bi bi-wallet2" aria-hidden="true"></i> Saldo</span>
        <span class="stat-value {{ $saldoFiltrado >= 0 ? 'valor-positivo' : 'valor-negativo' }}">
            R$ {{ number_format($saldoFiltrado, 2, ',', '.') }}
        </span>
    </div>
    <div class="stat-pill" style="margin-left:auto;">
        <span class="stat-label"><i class="bi bi-list-ul" aria-hidden="true"></i> Registros</span>
        <span class="stat-value text-white">{{ $totalTransacoes }}</span>
    </div>
</div>

{{-- ── Table ────────────────────────────────────────────────── --}}
<div class="card" style="opacity:1!important; border-radius:12px; overflow:hidden;">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0 trans-table" id="tabelaTransacoes" aria-label="Lista de transações">
                <thead>
                    <tr>
                        <th class="sortable" data-col="0" aria-sort="none">
                            Data <i class="bi bi-arrow-down-up sort-icon" aria-hidden="true"></i>
                        </th>
                        <th>Tipo</th>
                        <th class="sortable" data-col="2" aria-sort="none">
                            Descrição <i class="bi bi-arrow-down-up sort-icon" aria-hidden="true"></i>
                        </th>
                        <th class="d-none d-md-table-cell">Categoria</th>
                        <th class="sortable text-end" data-col="4" aria-sort="none">
                            Valor <i class="bi bi-arrow-down-up sort-icon" aria-hidden="true"></i>
                        </th>
                        <th class="text-center" style="width:90px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transacoes as $transacao)
                        @php
                            $dataFmt  = '';
                            $dataDisp = '–';
                            $dataFull = '';
                            $dataAttr = '';
                            if ($transacao->data) {
                                $c = $transacao->data instanceof \Carbon\Carbon
                                    ? $transacao->data
                                    : \Carbon\Carbon::parse($transacao->data);
                                $dataFmt  = $c->format('Y-m-d');
                                $dataAttr = $c->format('Y-m-d');
                                $dataFull = $c->format('d/m/y');
                                if ($c->copy()->startOfDay()->eq($hoje)) {
                                    $dataDisp = 'Hoje';
                                } elseif ($c->copy()->startOfDay()->eq($hoje->copy()->subDay())) {
                                    $dataDisp = 'Ontem';
                                } else {
                                    $dataDisp = $dataFull;
                                }
                            }
                            $catColor = $catMap->get($transacao->categoria ?? '', '#888');
                        @endphp
                        <tr class="row-{{ $transacao->tipo }}" data-date="{{ $dataAttr }}">
                            {{-- Data --}}
                            <td>
                                <span class="text-white" style="font-size:0.85rem;">{{ $dataDisp }}</span>
                                @if($dataDisp === 'Hoje' || $dataDisp === 'Ontem')
                                    <br><small class="text-muted" style="font-size:0.65rem;">{{ $dataFull }}</small>
                                @endif
                            </td>

                            {{-- Tipo + flags --}}
                            <td>
                                <div class="d-flex align-items-center gap-1 flex-wrap">
                                    @if($transacao->tipo === 'receita')
                                        <span class="badge-rec"><i class="bi bi-arrow-up" aria-hidden="true"></i> Receita</span>
                                    @else
                                        <span class="badge-dep"><i class="bi bi-arrow-down" aria-hidden="true"></i> Despesa</span>
                                    @endif
                                    @if($transacao->recorrente)
                                        <i class="bi bi-arrow-repeat text-info" style="font-size:0.7rem;" title="Recorrente" aria-label="Recorrente"></i>
                                    @endif
                                    @if($transacao->parcelado)
                                        <span class="badge-parcela" aria-label="Parcela {{ $transacao->parcela_atual }} de {{ $transacao->total_parcelas }}">
                                            {{ $transacao->parcela_atual }}/{{ $transacao->total_parcelas }}
                                        </span>
                                    @endif
                                </div>
                            </td>

                            {{-- Descrição --}}
                            <td>
                                <span class="text-white" style="font-size:0.88rem;">{{ $transacao->descricao }}</span>
                            </td>

                            {{-- Categoria --}}
                            <td class="d-none d-md-table-cell">
                                @if($transacao->categoria)
                                    <span class="d-inline-flex align-items-center gap-2">
                                        <span class="cat-dot" style="background:{{ $catColor }};box-shadow:0 0 5px {{ $catColor }}55;"></span>
                                        <span class="cat-name">{{ $transacao->categoria }}</span>
                                    </span>
                                @else
                                    <span class="text-muted" style="font-size:0.8rem;">—</span>
                                @endif
                            </td>

                            {{-- Valor --}}
                            <td class="text-end">
                                <span class="{{ $transacao->tipo === 'receita' ? 'valor-positivo' : 'valor-negativo' }}"
                                      style="font-weight:600;font-size:0.9rem;">
                                    R$ {{ number_format($transacao->valor, 2, ',', '.') }}
                                </span>
                            </td>

                            {{-- Ações --}}
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    <button type="button" class="btn btn-outline-warning btn-sm btn-icon"
                                        onclick="editarTransacao('{{ $transacao->tipo }}', '{{ $transacao->_id }}', '{{ addslashes($transacao->descricao) }}', '{{ $transacao->valor }}', '{{ $dataFmt }}', '{{ addslashes($transacao->categoria ?? '') }}')"
                                        aria-label="Editar {{ $transacao->descricao }}">
                                        <i class="bi bi-pencil" aria-hidden="true"></i>
                                    </button>
                                    <form action="{{ $transacao->tipo === 'receita' ? route('receitas.destroy', $transacao->_id) : route('despesas.destroy', $transacao->_id) }}"
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('Excluir esta transação?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm btn-icon"
                                            aria-label="Excluir {{ $transacao->descricao }}">
                                            <i class="bi bi-trash" aria-hidden="true"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <i class="bi bi-{{ $temFiltros ? 'funnel' : 'inbox' }}" aria-hidden="true"></i>
                                    <p>Nenhuma transação encontrada</p>
                                    @if($temFiltros)
                                        <small>
                                            Tente ajustar os filtros ou
                                            <a href="{{ route('financas.transacoes') }}" style="color:#3742fa;">limpar a busca</a>
                                        </small>
                                    @else
                                        <small>
                                            Adicione receitas ou despesas no
                                            <a href="{{ route('financas.index') }}" style="color:#00ff88;">Dashboard</a>
                                        </small>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Footer: paginação + por página --}}
    @if($transacoes->hasPages() || $totalTransacoes > 10)
    <div class="card-footer py-2">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-3">
                @if($transacoes->hasPages())
                    <small class="text-muted">
                        {{ $transacoes->firstItem() }}–{{ $transacoes->lastItem() }} de {{ $transacoes->total() }}
                    </small>
                @endif
                <div class="d-flex align-items-center gap-2">
                    <small class="text-muted d-none d-sm-inline">Por página:</small>
                    <select class="form-select form-select-sm" style="width:auto;" aria-label="Registros por página"
                            onchange="mudarPorPagina(this.value)">
                        @foreach([10, 20, 50, 100] as $pp)
                            <option value="{{ $pp }}" {{ $filtros['perPage'] == $pp ? 'selected' : '' }}>{{ $pp }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            @if($transacoes->hasPages())
                <nav aria-label="Paginação">
                    {{ $transacoes->links('pagination::bootstrap-5') }}
                </nav>
            @endif
        </div>
    </div>
    @endif
</div>

{{-- ── Modal Editar ─────────────────────────────────────────── --}}
<div class="modal fade" id="modalEditarTransacao" tabindex="-1" aria-labelledby="modalEditarTransacaoLabel">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-2" id="modalEditarHeader">
                <h6 class="modal-title" id="modalEditarTransacaoLabel">
                    <i class="bi bi-pencil" aria-hidden="true"></i> Editar Transação
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <form id="formEditarTransacao" method="POST">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small" for="editTransDescricao">Descrição *</label>
                        <input type="text" name="descricao" id="editTransDescricao" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-12 col-sm-6 mb-3">
                            <label class="form-label small" for="editTransValor">Valor (R$) *</label>
                            <input type="number" name="valor" id="editTransValor" class="form-control"
                                   step="0.01" min="0.01" inputmode="decimal" required>
                        </div>
                        <div class="col-12 col-sm-6 mb-3">
                            <label class="form-label small" for="editTransData">Data *</label>
                            <input type="date" name="data" id="editTransData" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small" for="editTransCategoria">Categoria</label>
                        <input type="text" name="categoria" id="editTransCategoria" class="form-control" placeholder="Opcional">
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning btn-sm">
                        <i class="bi bi-check-lg" aria-hidden="true"></i> Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // ── Modal editar ──────────────────────────────────────────
    function editarTransacao(tipo, id, descricao, valor, data, categoria) {
        const header = document.getElementById('modalEditarHeader');
        const form   = document.getElementById('formEditarTransacao');

        header.className = tipo === 'receita'
            ? 'modal-header py-2 bg-success'
            : 'modal-header py-2 bg-danger';
        form.action = tipo === 'receita' ? '/receitas/' + id : '/despesas/' + id;

        document.getElementById('editTransDescricao').value  = descricao;
        document.getElementById('editTransValor').value      = valor;
        document.getElementById('editTransData').value       = data;
        document.getElementById('editTransCategoria').value  = categoria || '';

        new bootstrap.Modal(document.getElementById('modalEditarTransacao')).show();
    }

    // ── Ordenação de colunas ──────────────────────────────────
    const _sortState = {};

    document.querySelectorAll('.trans-table thead th.sortable').forEach(th => {
        th.addEventListener('click', () => {
            const col = parseInt(th.dataset.col);
            ordenarTabela(col, th);
        });
    });

    function ordenarTabela(col, thEl) {
        const tabela  = document.getElementById('tabelaTransacoes');
        const tbody   = tabela.querySelector('tbody');
        const linhas  = Array.from(tbody.querySelectorAll('tr'));
        if (!linhas.length || linhas[0].cells.length === 1) return;

        _sortState[col] = !_sortState[col];
        const asc = _sortState[col];

        // Reset all headers
        document.querySelectorAll('.trans-table thead th').forEach(h => {
            h.classList.remove('sort-asc', 'sort-desc');
            h.setAttribute('aria-sort', 'none');
        });
        if (thEl) {
            thEl.classList.add(asc ? 'sort-asc' : 'sort-desc');
            thEl.setAttribute('aria-sort', asc ? 'ascending' : 'descending');
        }

        linhas.sort((a, b) => {
            if (a.cells.length === 1 || b.cells.length === 1) return 0;

            let va = a.cells[col]?.textContent.trim() ?? '';
            let vb = b.cells[col]?.textContent.trim() ?? '';

            if (col === 0) {
                // Sort by data-date attr for reliability
                va = a.dataset.date || '0000-00-00';
                vb = b.dataset.date || '0000-00-00';
            } else if (col === 4) {
                va = parseFloat(va.replace('R$', '').replace(/\./g, '').replace(',', '.').trim()) || 0;
                vb = parseFloat(vb.replace('R$', '').replace(/\./g, '').replace(',', '.').trim()) || 0;
            }

            if (va < vb) return asc ? -1 :  1;
            if (va > vb) return asc ?  1 : -1;
            return 0;
        });

        linhas.forEach(l => tbody.appendChild(l));
    }

    // ── Por página ────────────────────────────────────────────
    function mudarPorPagina(val) {
        const url = new URL(window.location.href);
        url.searchParams.set('per_page', val);
        url.searchParams.delete('page');
        window.location.href = url.toString();
    }

    // ── Debounce no campo de busca (envia form ao parar de digitar) ──
    const buscaInput = document.querySelector('input[name="busca"]');
    if (buscaInput) {
        let _timer;
        buscaInput.addEventListener('input', () => {
            clearTimeout(_timer);
            _timer = setTimeout(() => {
                document.getElementById('formFiltros').submit();
            }, 500);
        });
    }
</script>
@endsection
