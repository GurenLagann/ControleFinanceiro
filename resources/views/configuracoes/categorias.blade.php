@extends('layouts.app')

@section('page-title', 'Categorias')

@section('page-actions')
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalCategoria">
        <i class="bi bi-plus-lg"></i> <span class="d-none d-sm-inline">Categoria</span>
    </button>
@endsection

@section('content')
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
                                    <span class="input-group-text" style="min-width: 42px; justify-content: center; background: rgba(40,40,60,0.9);"><i class="bi" id="iconePreview" style="font-size: 1.2rem; color: #fff;"></i></span>
                                    <input type="text" name="icone" id="categoriaIcone" class="form-control" placeholder="Ex: cart" oninput="atualizarPreviewIcone()">
                                    <button type="button" class="btn btn-outline-light" onclick="toggleIconePicker()">
                                        <i class="bi bi-grid-3x3-gap-fill"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div id="iconePicker" style="display: none;" class="mb-3">
                            <label class="form-label small">Selecione um ícone:</label>
                            <div class="p-2 rounded" style="max-height: 180px; overflow-y: auto; background: rgba(40,40,60,0.9); border: 1px solid rgba(255,255,255,0.2);">
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach(['cart', 'bag', 'basket', 'house', 'building', 'car-front', 'fuel-pump', 'lightning', 'droplet', 'wifi', 'phone', 'credit-card', 'piggy-bank', 'cash', 'cash-stack', 'wallet', 'gift', 'heart', 'star', 'cup-hot', 'egg-fried', 'airplane', 'bus-front', 'train-front', 'bicycle', 'hospital', 'capsule', 'bandaid', 'book', 'mortarboard', 'music-note', 'film', 'controller', 'tools', 'wrench', 'hammer', 'scissors', 'brush', 'palette', 'tv', 'laptop', 'printer', 'headphones', 'camera', 'box', 'archive', 'truck', 'receipt', 'tags', 'percent', 'currency-dollar', 'graph-up', 'bar-chart', 'pie-chart', 'clipboard', 'calendar', 'clock', 'alarm', 'bell', 'envelope', 'chat', 'person', 'people', 'trophy', 'award', 'shield', 'lock', 'key', 'gear', 'sliders', 'globe', 'pin-map', 'geo-alt', 'tree', 'sun', 'moon', 'cloud', 'fire', 'water'] as $icone)
                                        <button type="button" class="btn btn-sm icone-option" onclick="selecionarIcone('{{ $icone }}')" title="{{ $icone }}" style="width: 38px; height: 38px; padding: 0; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: #fff;">
                                            <i class="bi bi-{{ $icone }}" style="font-size: 1.1rem;"></i>
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

    // Atualizar preview do icone ao digitar
    function atualizarPreviewIcone() {
        const icone = document.getElementById('categoriaIcone').value;
        const preview = document.getElementById('iconePreview');
        preview.className = 'bi bi-' + icone;
        preview.style.color = '#fff';
    }

    // Toggle do picker de icones
    function toggleIconePicker() {
        const picker = document.getElementById('iconePicker');
        picker.style.display = picker.style.display === 'none' ? 'block' : 'none';
    }

    function copiarIcone(icone) {
        document.getElementById('categoriaIcone').value = icone;
        document.getElementById('iconePreview').className = 'bi bi-' + icone;
        document.getElementById('iconePreview').style.color = '#fff';
    }

    function selecionarIcone(icone) {
        document.getElementById('categoriaIcone').value = icone;
        document.getElementById('iconePreview').className = 'bi bi-' + icone;
        document.getElementById('iconePreview').style.color = '#fff';

        // Destacar icone selecionado
        document.querySelectorAll('.icone-option').forEach(btn => {
            btn.style.background = 'rgba(255,255,255,0.1)';
            btn.style.borderColor = 'rgba(255,255,255,0.2)';
        });
        const btnClicked = event.target.closest('.icone-option');
        if (btnClicked) {
            btnClicked.style.background = '#6366f1';
            btnClicked.style.borderColor = '#6366f1';
        }

        // Fechar o picker
        document.getElementById('iconePicker').style.display = 'none';
    }

    function editarCategoria(id, nome, cor, icone, tipo) {
        document.getElementById('modalCategoriaTitle').innerHTML = '<i class="bi bi-pencil"></i> Editar Categoria';
        document.getElementById('formCategoria').action = '/categorias/' + id;
        document.getElementById('methodField').innerHTML = '@method("PUT")';

        document.getElementById('categoriaNome').value = nome;
        document.getElementById('categoriaCor').value = cor;
        document.getElementById('categoriaCorTexto').value = cor;
        document.getElementById('categoriaIcone').value = icone || '';

        const preview = document.getElementById('iconePreview');
        preview.className = 'bi bi-' + (icone || '');
        preview.style.color = '#fff';

        document.getElementById('categoriaTipo').value = tipo;

        // Destacar icone atual
        document.querySelectorAll('.icone-option').forEach(btn => {
            btn.style.background = 'rgba(255,255,255,0.1)';
            btn.style.borderColor = 'rgba(255,255,255,0.2)';
            if (icone && btn.title === icone) {
                btn.style.background = '#6366f1';
                btn.style.borderColor = '#6366f1';
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
            btn.style.background = 'rgba(255,255,255,0.1)';
            btn.style.borderColor = 'rgba(255,255,255,0.2)';
        });

        // Fechar picker de icones
        document.getElementById('iconePicker').style.display = 'none';
    });
</script>
@endsection
