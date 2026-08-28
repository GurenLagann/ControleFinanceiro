@extends('layouts.app')

@section('page-title', 'Cartões')

@section('page-actions')
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalCartao">
        <i class="bi bi-plus-lg"></i> <span class="d-none d-sm-inline">Cartão</span>
    </button>
@endsection

@section('content')
@php
    $totalFaturas = collect($faturas)->sum('total');
    $cartoesAtivos = collect($faturas)->filter(fn($f) => $f['cartao']['ativo'])->count();
    $maiorFatura = collect($faturas)->max('total');
@endphp

@if(count($faturas) > 0)
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body d-flex flex-wrap gap-4 justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon r"><i class="bi bi-credit-card-2-front" aria-hidden="true"></i></div>
                    <div>
                        <div class="stat-label">Total em faturas abertas</div>
                        <div class="stat-value valor-negativo">R$ {{ number_format($totalFaturas, 2, ',', '.') }}</div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon g"><i class="bi bi-check-circle" aria-hidden="true"></i></div>
                    <div>
                        <div class="stat-label">Cartões ativos</div>
                        <div class="stat-value">{{ $cartoesAtivos }} de {{ count($faturas) }}</div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon p"><i class="bi bi-graph-up-arrow" aria-hidden="true"></i></div>
                    <div>
                        <div class="stat-label">Maior fatura</div>
                        <div class="stat-value">R$ {{ number_format($maiorFatura ?? 0, 2, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="row g-3">
    @forelse($faturas as $item)
    @php
        $pctLimite = $item['cartao']['limite'] ? min(100, ($item['total'] / $item['cartao']['limite']) * 100) : null;
    @endphp
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card h-100" style="opacity: 1 !important; {{ !$item['cartao']['ativo'] ? 'opacity: .5 !important;' : '' }}">
            <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                <span><i class="bi bi-credit-card-2-front"></i> {{ $item['cartao']['nome'] }}</span>
                <div>
                    <button type="button" class="btn btn-outline-warning btn-sm btn-icon"
                        onclick="editarCartao('{{ $item['cartao']['id'] }}', '{{ addslashes($item['cartao']['nome']) }}', {{ $item['cartao']['dia_fechamento'] }}, {{ $item['cartao']['dia_vencimento'] }}, '{{ $item['cartao']['limite'] }}')"
                        aria-label="Editar cartão {{ $item['cartao']['nome'] }}">
                        <i class="bi bi-pencil" aria-hidden="true"></i>
                    </button>
                    <form action="{{ route('cartoes.toggle', $item['cartao']['id']) }}" method="POST" class="d-inline">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-outline-secondary btn-sm btn-icon" title="{{ $item['cartao']['ativo'] ? 'Desativar' : 'Ativar' }}">
                            <i class="bi bi-{{ $item['cartao']['ativo'] ? 'pause' : 'play' }}" aria-hidden="true"></i>
                        </button>
                    </form>
                    <form action="{{ route('cartoes.destroy', $item['cartao']['id']) }}" method="POST" class="d-inline" onsubmit="return confirm('Excluir este cartão?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm btn-icon" aria-label="Excluir cartão {{ $item['cartao']['nome'] }}">
                            <i class="bi bi-trash" aria-hidden="true"></i>
                        </button>
                    </form>
                </div>
            </div>
            <div class="card-body">
                <div class="small text-muted mb-1">Fatura atual</div>
                <h3 class="valor-negativo mb-2">R$ {{ number_format($item['total'], 2, ',', '.') }}</h3>
                <div class="small text-muted">
                    Ciclo: {{ $item['ciclo']['inicio']->format('d/m') }} — {{ $item['ciclo']['fim']->format('d/m') }}
                </div>
                <div class="small text-muted">Fecha dia {{ $item['cartao']['dia_fechamento'] }}, vence dia {{ $item['cartao']['dia_vencimento'] }}</div>
                @if($item['cartao']['limite'])
                    <div class="small text-muted mb-1">Limite: R$ {{ number_format($item['cartao']['limite'], 2, ',', '.') }}</div>
                    <div class="progress" style="height: 6px;" role="progressbar" aria-valuenow="{{ round($pctLimite) }}" aria-valuemin="0" aria-valuemax="100" aria-label="{{ round($pctLimite) }}% do limite usado">
                        <div class="progress-bar {{ $pctLimite >= 90 ? 'bg-danger' : ($pctLimite >= 70 ? 'bg-warning' : 'bg-success') }}" style="width: {{ $pctLimite }}%"></div>
                    </div>
                    <div class="small text-muted mt-1">{{ number_format($pctLimite, 0) }}% do limite usado</div>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card">
            <div class="empty-state">
                <i class="bi bi-credit-card-2-front" aria-hidden="true"></i>
                Nenhum cartão cadastrado
            </div>
        </div>
    </div>
    @endforelse
</div>

<!-- Modal Novo/Editar Cartao -->
<div class="modal fade" id="modalCartao" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary py-2">
                <h6 class="modal-title text-white" id="modalCartaoTitle"><i class="bi bi-credit-card-2-front"></i> Novo Cartão</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formCartao" method="POST" action="{{ route('cartoes.store') }}">
                @csrf
                <div id="methodFieldCartao"></div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small" for="cartaoNome">Nome *</label>
                        <input type="text" id="cartaoNome" name="nome" class="form-control" required placeholder="Ex: Nubank">
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label small" for="cartaoFechamento">Dia de Fechamento *</label>
                            <input type="number" id="cartaoFechamento" name="dia_fechamento" class="form-control" min="1" max="31" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label small" for="cartaoVencimento">Dia de Vencimento *</label>
                            <input type="number" id="cartaoVencimento" name="dia_vencimento" class="form-control" min="1" max="31" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small" for="cartaoLimite">Limite (R$) <span class="text-muted">— opcional</span></label>
                        <input type="number" id="cartaoLimite" name="limite" class="form-control" step="0.01" min="0" placeholder="Ex: 3000.00">
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-check-lg"></i> Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function editarCartao(id, nome, diaFechamento, diaVencimento, limite) {
        document.getElementById('modalCartaoTitle').innerHTML = '<i class="bi bi-pencil"></i> Editar Cartão';
        document.getElementById('formCartao').action = '/cartoes/' + id;
        document.getElementById('methodFieldCartao').innerHTML = '@method("PUT")';

        document.getElementById('cartaoNome').value = nome;
        document.getElementById('cartaoFechamento').value = diaFechamento;
        document.getElementById('cartaoVencimento').value = diaVencimento;
        document.getElementById('cartaoLimite').value = (limite && limite !== '') ? limite : '';

        new bootstrap.Modal(document.getElementById('modalCartao')).show();
    }

    document.getElementById('modalCartao').addEventListener('hidden.bs.modal', function() {
        document.getElementById('modalCartaoTitle').innerHTML = '<i class="bi bi-credit-card-2-front"></i> Novo Cartão';
        document.getElementById('formCartao').action = '{{ route("cartoes.store") }}';
        document.getElementById('methodFieldCartao').innerHTML = '';
        document.getElementById('formCartao').reset();
    });
</script>
@endsection
