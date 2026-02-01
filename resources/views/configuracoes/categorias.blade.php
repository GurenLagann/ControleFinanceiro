@extends('layouts.app')

@section('content')
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="bi bi-tags"></i> Categorias Personalizadas</h4>
                <div>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalCategoria">
                        <i class="bi bi-plus-lg"></i> Nova Categoria
                    </button>
                    <a href="{{ route('financas.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card" style="opacity: 1 !important;">
                <div class="card-header bg-light py-2">
                    <span><i class="bi bi-palette"></i> Categorias</span>
                    <span class="badge bg-secondary float-end">{{ $categorias->count() }} categorias</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Cor</th>
                                    <th>Nome</th>
                                    <th>Icone</th>
                                    <th>Tipo</th>
                                    <th>Status</th>
                                    <th class="text-center">Acoes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($categorias as $categoria)
                                    <tr>
                                        <td>
                                            <span class="d-inline-block rounded-circle" style="width: 24px; height: 24px; background-color: {{ $categoria->cor }}"></span>
                                        </td>
                                        <td>{{ $categoria->nome }}</td>
                                        <td>
                                            @if($categoria->icone)
                                                <i class="bi bi-{{ $categoria->icone }}"></i> {{ $categoria->icone }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if($categoria->tipo === 'receita')
                                                <span class="badge bg-success">Receita</span>
                                            @elseif($categoria->tipo === 'despesa')
                                                <span class="badge bg-danger">Despesa</span>
                                            @else
                                                <span class="badge bg-info">Ambos</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($categoria->ativo)
                                                <span class="badge bg-success">Ativa</span>
                                            @else
                                                <span class="badge bg-secondary">Inativa</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-outline-warning btn-sm"
                                                onclick="editarCategoria('{{ $categoria->_id }}', '{{ addslashes($categoria->nome) }}', '{{ $categoria->cor }}', '{{ $categoria->icone }}', '{{ $categoria->tipo }}')">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form action="{{ route('categorias.toggle', $categoria->_id) }}" method="POST" class="d-inline">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="btn btn-outline-{{ $categoria->ativo ? 'secondary' : 'success' }} btn-sm" title="{{ $categoria->ativo ? 'Desativar' : 'Ativar' }}">
                                                    <i class="bi bi-{{ $categoria->ativo ? 'pause' : 'play' }}"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('categorias.destroy', $categoria->_id) }}" method="POST" class="d-inline" onsubmit="return confirm('Excluir esta categoria?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            <i class="bi bi-inbox" style="font-size: 2rem;"></i><br>
                                            Nenhuma categoria cadastrada
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cores Predefinidas -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card" style="opacity: 1 !important;">
                <div class="card-header bg-light py-2">
                    <span><i class="bi bi-info-circle"></i> Icones Disponiveis</span>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-2">Clique para copiar o nome do icone:</p>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach(['cart', 'house', 'car-front', 'fuel-pump', 'lightning', 'droplet', 'wifi', 'phone', 'credit-card', 'piggy-bank', 'cash', 'wallet', 'gift', 'heart', 'star', 'bag', 'basket', 'cup-hot', 'egg-fried', 'airplane', 'bus-front', 'train-front', 'bicycle', 'hospital', 'capsule', 'bandaid', 'book', 'mortarboard', 'music-note', 'film', 'controller', 'tools', 'wrench', 'hammer', 'scissors', 'brush', 'palette'] as $icone)
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="copiarIcone('{{ $icone }}')" title="{{ $icone }}">
                                <i class="bi bi-{{ $icone }}"></i>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Nova/Editar Categoria -->
    <div class="modal fade" id="modalCategoria" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary py-2">
                    <h6 class="modal-title text-white" id="modalCategoriaTitle"><i class="bi bi-tag"></i> Nova Categoria</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formCategoria" method="POST" action="{{ route('categorias.store') }}">
                    @csrf
                    <div id="methodField"></div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small">Nome *</label>
                            <input type="text" name="nome" id="categoriaNome" class="form-control" required placeholder="Ex: Alimentacao">
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label small">Cor *</label>
                                <div class="input-group">
                                    <input type="color" name="cor" id="categoriaCor" class="form-control form-control-color" value="#00ff88" required>
                                    <input type="text" id="categoriaCorTexto" class="form-control" value="#00ff88" readonly>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label small">Icone</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi" id="iconePreview"></i></span>
                                    <input type="text" name="icone" id="categoriaIcone" class="form-control" placeholder="cart" readonly>
                                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#iconePicker">
                                        <i class="bi bi-grid"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="collapse mb-3" id="iconePicker">
                            <div class="card card-body p-2" style="max-height: 200px; overflow-y: auto; background: rgba(20,20,35,0.9);">
                                <div class="d-flex flex-wrap gap-1 justify-content-center">
                                    @foreach(['cart', 'cart-fill', 'bag', 'bag-fill', 'basket', 'basket-fill', 'house', 'house-fill', 'building', 'buildings', 'car-front', 'car-front-fill', 'fuel-pump', 'fuel-pump-fill', 'lightning', 'lightning-fill', 'droplet', 'droplet-fill', 'wifi', 'phone', 'phone-fill', 'credit-card', 'credit-card-fill', 'piggy-bank', 'piggy-bank-fill', 'cash', 'cash-stack', 'wallet', 'wallet-fill', 'gift', 'gift-fill', 'heart', 'heart-fill', 'star', 'star-fill', 'cup-hot', 'cup-hot-fill', 'egg-fried', 'airplane', 'airplane-fill', 'bus-front', 'bus-front-fill', 'train-front', 'train-front-fill', 'bicycle', 'hospital', 'hospital-fill', 'capsule', 'bandaid', 'bandaid-fill', 'book', 'book-fill', 'mortarboard', 'mortarboard-fill', 'music-note', 'music-note-beamed', 'film', 'controller', 'tools', 'wrench', 'hammer', 'scissors', 'brush', 'brush-fill', 'palette', 'palette-fill', 'tv', 'tv-fill', 'laptop', 'phone-vibrate', 'printer', 'printer-fill', 'headphones', 'camera', 'camera-fill', 'box', 'box-fill', 'archive', 'archive-fill', 'truck', 'truck-front-fill', 'receipt', 'receipt-cutoff', 'tags', 'tags-fill', 'percent', 'currency-dollar', 'graph-up', 'graph-down', 'bar-chart', 'pie-chart', 'clipboard', 'clipboard-check', 'calendar', 'calendar-event', 'clock', 'clock-fill', 'alarm', 'alarm-fill', 'bell', 'bell-fill', 'envelope', 'envelope-fill', 'chat', 'chat-fill', 'person', 'people', 'people-fill', 'trophy', 'trophy-fill', 'award', 'award-fill', 'shield', 'shield-fill', 'lock', 'lock-fill', 'key', 'key-fill', 'gear', 'gear-fill', 'sliders', 'filter', 'funnel', 'search', 'zoom-in', 'globe', 'globe2', 'pin-map', 'geo-alt', 'compass', 'signpost', 'flower1', 'tree', 'sun', 'moon', 'cloud', 'umbrella', 'snow', 'thermometer', 'fire', 'water', 'wind', 'rainbow'] as $icone)
                                        <button type="button" class="btn btn-outline-secondary btn-sm icone-option" onclick="selecionarIcone('{{ $icone }}')" title="{{ $icone }}" style="width: 36px; height: 36px; padding: 0;">
                                            <i class="bi bi-{{ $icone }}"></i>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Tipo *</label>
                            <select name="tipo" id="categoriaTipo" class="form-select" required>
                                <option value="ambos">Ambos (Receita e Despesa)</option>
                                <option value="receita">Apenas Receita</option>
                                <option value="despesa">Apenas Despesa</option>
                            </select>
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
    // Atualizar preview da cor
    document.getElementById('categoriaCor').addEventListener('input', function() {
        document.getElementById('categoriaCorTexto').value = this.value;
    });

    // Atualizar preview do icone
    document.getElementById('categoriaIcone').addEventListener('input', function() {
        const preview = document.getElementById('iconePreview');
        preview.className = 'bi bi-' + this.value;
    });

    function copiarIcone(icone) {
        document.getElementById('categoriaIcone').value = icone;
        document.getElementById('iconePreview').className = 'bi bi-' + icone;
    }

    function selecionarIcone(icone) {
        document.getElementById('categoriaIcone').value = icone;
        document.getElementById('iconePreview').className = 'bi bi-' + icone;

        // Destacar icone selecionado
        document.querySelectorAll('.icone-option').forEach(btn => {
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-outline-secondary');
        });
        event.target.closest('.icone-option').classList.remove('btn-outline-secondary');
        event.target.closest('.icone-option').classList.add('btn-primary');

        // Fechar o picker
        const picker = document.getElementById('iconePicker');
        bootstrap.Collapse.getInstance(picker)?.hide();
    }

    function editarCategoria(id, nome, cor, icone, tipo) {
        document.getElementById('modalCategoriaTitle').innerHTML = '<i class="bi bi-pencil"></i> Editar Categoria';
        document.getElementById('formCategoria').action = '/categorias/' + id;
        document.getElementById('methodField').innerHTML = '@method("PUT")';

        document.getElementById('categoriaNome').value = nome;
        document.getElementById('categoriaCor').value = cor;
        document.getElementById('categoriaCorTexto').value = cor;
        document.getElementById('categoriaIcone').value = icone || '';
        document.getElementById('iconePreview').className = 'bi bi-' + (icone || '');
        document.getElementById('categoriaTipo').value = tipo;

        // Destacar icone atual
        document.querySelectorAll('.icone-option').forEach(btn => {
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-outline-secondary');
            if (icone && btn.title === icone) {
                btn.classList.remove('btn-outline-secondary');
                btn.classList.add('btn-primary');
            }
        });

        new bootstrap.Modal(document.getElementById('modalCategoria')).show();
    }

    // Resetar modal ao fechar
    document.getElementById('modalCategoria').addEventListener('hidden.bs.modal', function() {
        document.getElementById('modalCategoriaTitle').innerHTML = '<i class="bi bi-tag"></i> Nova Categoria';
        document.getElementById('formCategoria').action = '{{ route("categorias.store") }}';
        document.getElementById('methodField').innerHTML = '';
        document.getElementById('formCategoria').reset();
        document.getElementById('categoriaCor').value = '#00ff88';
        document.getElementById('categoriaCorTexto').value = '#00ff88';
        document.getElementById('iconePreview').className = 'bi';
        document.getElementById('categoriaIcone').value = '';

        // Resetar selecao de icones
        document.querySelectorAll('.icone-option').forEach(btn => {
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-outline-secondary');
        });

        // Fechar picker de icones
        const picker = document.getElementById('iconePicker');
        if (picker.classList.contains('show')) {
            bootstrap.Collapse.getInstance(picker)?.hide();
        }
    });
</script>
@endsection
