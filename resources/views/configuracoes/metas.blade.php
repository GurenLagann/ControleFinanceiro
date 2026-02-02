@extends('layouts.app')

@section('page-title', 'Metas')

@section('page-actions')
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalMeta">
        <i class="bi bi-plus-lg"></i> <span class="d-none d-sm-inline">Meta</span>
    </button>
@endsection

@section('content')
    <!-- Cards de Resumo -->
    <div class="row mb-4">
        @php
            $metasAtivas = $metas->where('ativo', true);
            $concluidas = $metasAtivas->filter(fn($m) => $m->progresso >= 100)->count();
            $emAndamento = $metasAtivas->filter(fn($m) => $m->progresso < 100 && $m->dias_restantes > 0)->count();
            $vencidas = $metasAtivas->filter(fn($m) => $m->progresso < 100 && $m->dias_restantes <= 0)->count();
        @endphp
        <div class="col-md-3">
            <div class="card glow-blue" style="opacity: 1 !important; border-left: 4px solid #3742fa;">
                <div class="card-body text-center py-3">
                    <h6 class="text-muted mb-1"><i class="bi bi-bullseye"></i> Total</h6>
                    <h3 class="mb-0 text-white">{{ $metasAtivas->count() }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card glow-green" style="opacity: 1 !important; border-left: 4px solid #00ff88;">
                <div class="card-body text-center py-3">
                    <h6 class="text-muted mb-1"><i class="bi bi-check-circle"></i> Concluidas</h6>
                    <h3 class="mb-0 valor-positivo">{{ $concluidas }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card glow-purple" style="opacity: 1 !important; border-left: 4px solid #6f42c1;">
                <div class="card-body text-center py-3">
                    <h6 class="text-muted mb-1"><i class="bi bi-hourglass-split"></i> Em Andamento</h6>
                    <h3 class="mb-0" style="color: #a5b4fc;">{{ $emAndamento }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card glow-red" style="opacity: 1 !important; border-left: 4px solid #ff4757;">
                <div class="card-body text-center py-3">
                    <h6 class="text-muted mb-1"><i class="bi bi-exclamation-triangle"></i> Vencidas</h6>
                    <h3 class="mb-0 valor-negativo">{{ $vencidas }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Metas -->
    <div class="row">
        @forelse($metas as $meta)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100" style="opacity: 1 !important;">
                    <div class="card-header py-2 d-flex justify-content-between align-items-center
                        @if($meta->status === 'concluida') bg-success
                        @elseif($meta->status === 'vencida') bg-danger
                        @elseif($meta->status === 'urgente') bg-warning
                        @else bg-primary @endif">
                        <span class="text-white small">
                            @if($meta->tipo === 'economia')
                                <i class="bi bi-piggy-bank"></i> Economia
                            @elseif($meta->tipo === 'limite_gasto')
                                <i class="bi bi-shield-exclamation"></i> Limite
                            @else
                                <i class="bi bi-graph-up-arrow"></i> Receita
                            @endif
                        </span>
                        <span class="badge bg-light text-dark">
                            @if($meta->status === 'concluida')
                                <i class="bi bi-check"></i> Concluida
                            @elseif($meta->status === 'vencida')
                                <i class="bi bi-x"></i> Vencida
                            @elseif($meta->status === 'urgente')
                                <i class="bi bi-clock"></i> {{ $meta->dias_restantes }}d
                            @else
                                <i class="bi bi-calendar"></i> {{ $meta->dias_restantes }}d
                            @endif
                        </span>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title text-white fw-bold">{{ $meta->titulo }}</h5>
                        @if($meta->descricao)
                            <p class="card-text text-light small opacity-75">{{ $meta->descricao }}</p>
                        @endif

                        <div class="mb-3">
                            <div class="d-flex justify-content-between small mb-1">
                                <span class="text-white">Progresso</span>
                                <span class="fw-bold {{ $meta->progresso >= 100 ? 'valor-positivo' : 'text-white' }}">
                                    {{ number_format($meta->progresso, 1) }}%
                                </span>
                            </div>
                            <div class="progress" style="height: 10px;">
                                @php
                                    $corBarra = $meta->tipo === 'limite_gasto'
                                        ? ($meta->progresso > 100 ? 'bg-danger' : ($meta->progresso > 80 ? 'bg-warning' : 'bg-success'))
                                        : ($meta->progresso >= 100 ? 'bg-success' : 'bg-primary');
                                @endphp
                                <div class="progress-bar {{ $corBarra }}" style="width: {{ min(100, $meta->progresso) }}%"></div>
                            </div>
                        </div>

                        <div class="row text-center small mb-3">
                            <div class="col-6">
                                <span class="text-white-50 d-block">Atual</span>
                                <strong class="fs-6 {{ $meta->tipo === 'limite_gasto' && $meta->valor_atual > $meta->valor_alvo ? 'valor-negativo' : 'valor-positivo' }}">
                                    R$ {{ number_format($meta->valor_atual, 2, ',', '.') }}
                                </strong>
                            </div>
                            <div class="col-6">
                                <span class="text-white-50 d-block">Meta</span>
                                <strong class="fs-6 text-white">R$ {{ number_format($meta->valor_alvo, 2, ',', '.') }}</strong>
                            </div>
                        </div>

                        <div class="mt-auto">
                            @if($meta->categoria)
                                <div class="mb-2">
                                    <span class="badge bg-secondary"><i class="bi bi-tag"></i> {{ $meta->categoria }}</span>
                                </div>
                            @endif

                            <div class="small text-white-50">
                                <i class="bi bi-calendar-range"></i>
                                {{ $meta->data_inicio ? $meta->data_inicio->format('d/m/Y') : '-' }} -
                                {{ $meta->data_fim ? $meta->data_fim->format('d/m/Y') : '-' }}
                            </div>
                        </div>
                    </div>
                    <div class="card-footer py-2 d-flex justify-content-end gap-1">
                        <button type="button" class="btn btn-outline-warning btn-sm"
                            onclick="editarMeta('{{ $meta->_id }}', '{{ addslashes($meta->titulo) }}', '{{ addslashes($meta->descricao ?? '') }}', '{{ $meta->valor_alvo }}', '{{ $meta->data_inicio ? $meta->data_inicio->format('Y-m-d') : '' }}', '{{ $meta->data_fim ? $meta->data_fim->format('Y-m-d') : '' }}', '{{ addslashes($meta->categoria ?? '') }}', '{{ $meta->tipo }}')">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <form action="{{ route('metas.destroy', $meta->_id) }}" method="POST" class="d-inline" onsubmit="return confirm('Excluir esta meta?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card" style="opacity: 1 !important;">
                    <div class="card-body text-center py-5 text-muted">
                        <i class="bi bi-bullseye" style="font-size: 3rem;"></i>
                        <p class="mt-2">Nenhuma meta cadastrada</p>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalMeta">
                            <i class="bi bi-plus-lg"></i> Criar Primeira Meta
                        </button>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Modal Nova/Editar Meta -->
    <div class="modal fade" id="modalMeta" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary py-2">
                    <h6 class="modal-title text-white" id="modalMetaTitle"><i class="bi bi-bullseye"></i> Nova Meta</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formMeta" method="POST" action="{{ route('metas.store') }}">
                    @csrf
                    <div id="metaMethodField"></div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small">Titulo *</label>
                            <input type="text" name="titulo" id="metaTitulo" class="form-control" required placeholder="Ex: Economizar para viagem">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Descricao</label>
                            <textarea name="descricao" id="metaDescricao" class="form-control" rows="2" placeholder="Opcional"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label small">Tipo *</label>
                                <select name="tipo" id="metaTipo" class="form-select" required>
                                    <option value="economia">Economia (Poupar)</option>
                                    <option value="limite_gasto">Limite de Gasto</option>
                                    <option value="receita">Meta de Receita</option>
                                </select>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label small">Valor Alvo (R$) *</label>
                                <input type="number" name="valor_alvo" id="metaValorAlvo" class="form-control" step="0.01" min="0.01" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label small">Data Inicio *</label>
                                <input type="date" name="data_inicio" id="metaDataInicio" class="form-control" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label small">Data Fim *</label>
                                <input type="date" name="data_fim" id="metaDataFim" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Categoria (para filtrar)</label>
                            <input type="text" name="categoria" id="metaCategoria" class="form-control" placeholder="Opcional - Ex: Alimentacao">
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
    // Definir datas padrao
    document.addEventListener('DOMContentLoaded', function() {
        const hoje = new Date();
        const inicioMes = new Date(hoje.getFullYear(), hoje.getMonth(), 1);
        const fimMes = new Date(hoje.getFullYear(), hoje.getMonth() + 1, 0);

        document.getElementById('metaDataInicio').value = inicioMes.toISOString().split('T')[0];
        document.getElementById('metaDataFim').value = fimMes.toISOString().split('T')[0];
    });

    function editarMeta(id, titulo, descricao, valorAlvo, dataInicio, dataFim, categoria, tipo) {
        document.getElementById('modalMetaTitle').innerHTML = '<i class="bi bi-pencil"></i> Editar Meta';
        document.getElementById('formMeta').action = '/metas/' + id;
        document.getElementById('metaMethodField').innerHTML = '@method("PUT")';

        document.getElementById('metaTitulo').value = titulo;
        document.getElementById('metaDescricao').value = descricao;
        document.getElementById('metaValorAlvo').value = valorAlvo;
        document.getElementById('metaDataInicio').value = dataInicio;
        document.getElementById('metaDataFim').value = dataFim;
        document.getElementById('metaCategoria').value = categoria;
        document.getElementById('metaTipo').value = tipo;

        new bootstrap.Modal(document.getElementById('modalMeta')).show();
    }

    // Resetar modal ao fechar
    document.getElementById('modalMeta').addEventListener('hidden.bs.modal', function() {
        document.getElementById('modalMetaTitle').innerHTML = '<i class="bi bi-bullseye"></i> Nova Meta';
        document.getElementById('formMeta').action = '{{ route("metas.store") }}';
        document.getElementById('metaMethodField').innerHTML = '';

        const hoje = new Date();
        const inicioMes = new Date(hoje.getFullYear(), hoje.getMonth(), 1);
        const fimMes = new Date(hoje.getFullYear(), hoje.getMonth() + 1, 0);

        document.getElementById('metaDataInicio').value = inicioMes.toISOString().split('T')[0];
        document.getElementById('metaDataFim').value = fimMes.toISOString().split('T')[0];
        document.getElementById('metaTitulo').value = '';
        document.getElementById('metaDescricao').value = '';
        document.getElementById('metaValorAlvo').value = '';
        document.getElementById('metaCategoria').value = '';
        document.getElementById('metaTipo').value = 'economia';
    });
</script>
@endsection
