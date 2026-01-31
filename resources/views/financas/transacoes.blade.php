@extends('layouts.app')

@section('content')
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="bi bi-list-ul"></i> Todas as Transacoes</h4>
                <a href="{{ route('financas.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card" style="opacity: 1 !important;">
                <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-table"></i> Transacoes</span>
                    <span class="badge bg-secondary">{{ $totalTransacoes ?? $transacoes->total() }} registros</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="tabelaTransacoes">
                            <thead>
                                <tr>
                                    <th style="cursor: pointer;" onclick="ordenarTabela(0)">
                                        Data <i class="bi bi-arrow-down-up"></i>
                                    </th>
                                    <th style="cursor: pointer;" onclick="ordenarTabela(1)">
                                        Tipo <i class="bi bi-arrow-down-up"></i>
                                    </th>
                                    <th style="cursor: pointer;" onclick="ordenarTabela(2)">
                                        Descricao <i class="bi bi-arrow-down-up"></i>
                                    </th>
                                    <th style="cursor: pointer;" onclick="ordenarTabela(3)">
                                        Categoria <i class="bi bi-arrow-down-up"></i>
                                    </th>
                                    <th style="cursor: pointer;" onclick="ordenarTabela(4)">
                                        Valor <i class="bi bi-arrow-down-up"></i>
                                    </th>
                                    <th class="text-center">Acoes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transacoes as $transacao)
                                    <tr>
                                        <td>
                                            @if($transacao->data)
                                                {{ $transacao->data instanceof \Carbon\Carbon ? $transacao->data->format('d/m/Y') : \Carbon\Carbon::parse($transacao->data)->format('d/m/Y') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if($transacao->tipo === 'receita')
                                                <span class="badge bg-success"><i class="bi bi-arrow-up"></i> Receita</span>
                                            @else
                                                <span class="badge bg-danger"><i class="bi bi-arrow-down"></i> Despesa</span>
                                            @endif
                                            @if($transacao->recorrente)
                                                <span class="badge bg-info"><i class="bi bi-arrow-repeat"></i></span>
                                            @endif
                                            @if($transacao->parcelado)
                                                <span class="badge bg-warning text-dark">{{ $transacao->parcela_atual }}/{{ $transacao->total_parcelas }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $transacao->descricao }}</td>
                                        <td>{{ $transacao->categoria ?: '-' }}</td>
                                        <td class="{{ $transacao->tipo === 'receita' ? 'valor-positivo' : 'valor-negativo' }}">
                                            R$ {{ number_format($transacao->valor, 2, ',', '.') }}
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $dataFormatada = '';
                                                if ($transacao->data) {
                                                    $dataFormatada = $transacao->data instanceof \Carbon\Carbon
                                                        ? $transacao->data->format('Y-m-d')
                                                        : \Carbon\Carbon::parse($transacao->data)->format('Y-m-d');
                                                }
                                            @endphp
                                            <button type="button" class="btn btn-outline-warning btn-sm"
                                                onclick="editarTransacao('{{ $transacao->tipo }}', '{{ $transacao->_id }}', '{{ addslashes($transacao->descricao) }}', '{{ $transacao->valor }}', '{{ $dataFormatada }}', '{{ addslashes($transacao->categoria ?? '') }}')">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form action="{{ $transacao->tipo === 'receita' ? route('receitas.destroy', $transacao->_id) : route('despesas.destroy', $transacao->_id) }}"
                                                  method="POST" class="d-inline" onsubmit="return confirm('Excluir esta transacao?')">
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
                                            Nenhuma transacao encontrada
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($transacoes->hasPages())
                <div class="card-footer py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            Mostrando {{ $transacoes->firstItem() }} - {{ $transacoes->lastItem() }} de {{ $transacoes->total() }}
                        </small>
                        <nav>
                            {{ $transacoes->links('pagination::bootstrap-5') }}
                        </nav>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal Editar Transacao -->
    <div class="modal fade" id="modalEditarTransacao" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header py-2" id="modalEditarHeader">
                    <h6 class="modal-title"><i class="bi bi-pencil"></i> Editar Transacao</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formEditarTransacao" method="POST">
                    @csrf @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small">Descricao *</label>
                            <input type="text" name="descricao" id="editTransDescricao" class="form-control" required>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label small">Valor (R$) *</label>
                                <input type="number" name="valor" id="editTransValor" class="form-control" step="0.01" min="0.01" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label small">Data *</label>
                                <input type="date" name="data" id="editTransData" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Categoria</label>
                            <input type="text" name="categoria" id="editTransCategoria" class="form-control" placeholder="Opcional">
                        </div>
                    </div>
                    <div class="modal-footer py-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning btn-sm"><i class="bi bi-check-lg"></i> Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    function editarTransacao(tipo, id, descricao, valor, data, categoria) {
        const header = document.getElementById('modalEditarHeader');
        const form = document.getElementById('formEditarTransacao');

        if (tipo === 'receita') {
            header.className = 'modal-header py-2 bg-success';
            form.action = '/receitas/' + id;
        } else {
            header.className = 'modal-header py-2 bg-danger';
            form.action = '/despesas/' + id;
        }

        document.getElementById('editTransDescricao').value = descricao;
        document.getElementById('editTransValor').value = valor;
        document.getElementById('editTransData').value = data;
        document.getElementById('editTransCategoria').value = categoria || '';

        new bootstrap.Modal(document.getElementById('modalEditarTransacao')).show();
    }

    // Funcao para ordenar tabela
    let ordemAtual = {};
    function ordenarTabela(coluna) {
        const tabela = document.getElementById('tabelaTransacoes');
        const tbody = tabela.querySelector('tbody');
        const linhas = Array.from(tbody.querySelectorAll('tr'));

        if (linhas.length === 0 || linhas[0].cells.length === 1) return;

        ordemAtual[coluna] = !ordemAtual[coluna];
        const crescente = ordemAtual[coluna];

        linhas.sort((a, b) => {
            let valorA = a.cells[coluna].textContent.trim();
            let valorB = b.cells[coluna].textContent.trim();

            // Para coluna de data
            if (coluna === 0) {
                const parseData = (str) => {
                    if (str === '-') return new Date(0);
                    const [dia, mes, ano] = str.split('/');
                    return new Date(ano, mes - 1, dia);
                };
                valorA = parseData(valorA);
                valorB = parseData(valorB);
            }
            // Para coluna de valor
            else if (coluna === 4) {
                valorA = parseFloat(valorA.replace('R$', '').replace(/\./g, '').replace(',', '.').trim()) || 0;
                valorB = parseFloat(valorB.replace('R$', '').replace(/\./g, '').replace(',', '.').trim()) || 0;
            }

            if (valorA < valorB) return crescente ? -1 : 1;
            if (valorA > valorB) return crescente ? 1 : -1;
            return 0;
        });

        linhas.forEach(linha => tbody.appendChild(linha));
    }

    // Filtro de busca
    document.addEventListener('DOMContentLoaded', function() {
        // Adicionar campo de busca
        const cardHeader = document.querySelector('.card-header');
        if (!cardHeader) return;

        const searchDiv = document.createElement('div');
        searchDiv.className = 'd-flex align-items-center gap-2';
        searchDiv.innerHTML = `
            <input type="text" id="buscaTransacao" class="form-control form-control-sm" placeholder="Buscar..." style="width: 200px;">
            <select id="filtroTipo" class="form-select form-select-sm" style="width: 120px;">
                <option value="">Todos</option>
                <option value="Receita">Receitas</option>
                <option value="Despesa">Despesas</option>
            </select>
        `;

        const badge = cardHeader.querySelector('.badge');
        if (badge) {
            cardHeader.insertBefore(searchDiv, badge);
        }

        // Funcao de filtro
        const filtrar = () => {
            const busca = document.getElementById('buscaTransacao').value.toLowerCase();
            const tipo = document.getElementById('filtroTipo').value;
            const linhas = document.querySelectorAll('#tabelaTransacoes tbody tr');
            let visivel = 0;

            linhas.forEach(linha => {
                if (linha.cells.length === 1) return;

                const texto = linha.textContent.toLowerCase();
                const tipoLinha = linha.cells[1].textContent;

                const matchBusca = texto.includes(busca);
                const matchTipo = !tipo || tipoLinha.includes(tipo);

                if (matchBusca && matchTipo) {
                    linha.style.display = '';
                    visivel++;
                } else {
                    linha.style.display = 'none';
                }
            });

            if (badge) {
                badge.textContent = visivel + ' registros';
            }
        };

        const buscaInput = document.getElementById('buscaTransacao');
        const filtroSelect = document.getElementById('filtroTipo');

        if (buscaInput) buscaInput.addEventListener('input', filtrar);
        if (filtroSelect) filtroSelect.addEventListener('change', filtrar);
    });
</script>
@endsection
