@extends('layouts.app')

@section('content')
    <!-- Cards de Resumo -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card card-receita h-100 glow-green">
                <div class="card-body text-center">
                    <h6 class="card-subtitle mb-2 text-muted">
                        <i class="bi bi-arrow-up-circle"></i> Total Receitas
                    </h6>
                    <h3 class="card-title valor-positivo" data-value="{{ $totalReceitas }}">
                        R$ {{ number_format($totalReceitas, 2, ',', '.') }}
                    </h3>
                    <button class="btn btn-success btn-sm mt-2 w-100" data-bs-toggle="modal" data-bs-target="#modalReceita">
                        <i class="bi bi-plus-lg"></i> Nova Receita
                    </button>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-despesa h-100 glow-red" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#modalDespesasTotais">
                <div class="card-body text-center">
                    <h6 class="card-subtitle mb-2 text-muted">
                        <i class="bi bi-arrow-down-circle"></i> Total Despesas <i class="bi bi-eye ms-1" title="Ver detalhes"></i>
                    </h6>
                    <h4 class="card-title valor-negativo mb-0" data-value="{{ $totalDespesasMesAtual }}">
                        R$ {{ number_format($totalDespesasMesAtual, 2, ',', '.') }}
                    </h4>
                    <small class="text-muted">este mes</small>
                    <div class="mt-1 pt-1 border-top border-secondary">
                        <small class="text-muted">Total geral: </small>
                        <span class="valor-negativo">R$ {{ number_format($totalDespesas, 2, ',', '.') }}</span>
                    </div>
                    <button class="btn btn-danger btn-sm mt-2 w-100" data-bs-toggle="modal" data-bs-target="#modalDespesa" onclick="event.stopPropagation();">
                        <i class="bi bi-plus-lg"></i> Nova Despesa
                    </button>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-saldo h-100 glow-blue">
                <div class="card-body text-center">
                    <h6 class="card-subtitle mb-2 text-muted">
                        <i class="bi bi-wallet2"></i> Saldo Atual
                    </h6>
                    <h4 class="card-title {{ $saldoMesAtual >= 0 ? 'valor-positivo' : 'valor-negativo' }} mb-0" data-value="{{ $saldoMesAtual }}">
                        R$ {{ number_format($saldoMesAtual, 2, ',', '.') }}
                    </h4>
                    <small class="text-muted">este mes</small>
                    <div class="progress mt-2" style="height: 8px;">
                        @php
                            $totalMes = $totalReceitasMesAtual + $totalDespesasMesAtual;
                            $percentReceita = $totalMes > 0 ? ($totalReceitasMesAtual / $totalMes) * 100 : 50;
                        @endphp
                        <div class="progress-bar bg-success progress-animated" style="width: 0%" data-width="{{ $percentReceita }}"></div>
                        <div class="progress-bar bg-danger progress-animated" style="width: 0%" data-width="{{ 100 - $percentReceita }}"></div>
                    </div>
                    <div class="mt-1 pt-1 border-top border-secondary">
                        <small class="text-muted">Total geral: </small>
                        <span class="{{ $saldo >= 0 ? 'valor-positivo' : 'valor-negativo' }}">R$ {{ number_format($saldo, 2, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 glow-purple" style="border-left: 4px solid #6f42c1;">
                <div class="card-body text-center">
                    <h6 class="card-subtitle mb-2 text-muted">
                        <i class="bi bi-calendar-check"></i> Previsao Mensal
                    </h6>
                    <h3 class="card-title {{ $previsaoSaldo >= 0 ? 'valor-positivo' : 'valor-negativo' }}" data-value="{{ $previsaoSaldo }}">
                        R$ {{ number_format($previsaoSaldo, 2, ',', '.') }}
                    </h3>
                    <small class="text-muted">
                        <span class="valor-positivo">+{{ number_format($previsaoReceitas, 2, ',', '.') }}</span> /
                        <span class="valor-negativo">-{{ number_format($previsaoDespesas, 2, ',', '.') }}</span>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Graficos -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card chart-card" style="cursor: pointer;" onclick="ampliarGrafico('pizza', 'Receitas vs Despesas')">
                <div class="card-header bg-light py-2">
                    <small><i class="bi bi-pie-chart"></i> Receitas vs Despesas <i class="bi bi-arrows-fullscreen float-end"></i></small>
                </div>
                <div class="card-body p-2">
                    <canvas id="chartPizza" height="160"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card chart-card" style="cursor: pointer;" onclick="ampliarGrafico('despesasCategoria', 'Despesas por Categoria')">
                <div class="card-header bg-light py-2">
                    <small><i class="bi bi-bar-chart"></i> Despesas/Categoria <i class="bi bi-arrows-fullscreen float-end"></i></small>
                </div>
                <div class="card-body p-2">
                    <canvas id="chartDespesasCategoria" height="160"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card chart-card" style="cursor: pointer;" onclick="ampliarGrafico('receitasCategoria', 'Receitas por Categoria')">
                <div class="card-header bg-light py-2">
                    <small><i class="bi bi-bar-chart"></i> Receitas/Categoria <i class="bi bi-arrows-fullscreen float-end"></i></small>
                </div>
                <div class="card-body p-2">
                    <canvas id="chartReceitasCategoria" height="160"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card chart-card" style="cursor: pointer;" onclick="ampliarGrafico('evolucao', 'Evolucao Ultimos 7 Dias')">
                <div class="card-header bg-light py-2">
                    <small><i class="bi bi-graph-up"></i> Ultimos 7 Dias <i class="bi bi-arrows-fullscreen float-end"></i></small>
                </div>
                <div class="card-body p-2">
                    <canvas id="chartEvolucao" height="160"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Projecao Futura -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card chart-card" style="cursor: pointer;" onclick="ampliarGrafico('projecao', 'Projecao 6 Meses')">
                <div class="card-header bg-light py-2">
                    <small><i class="bi bi-graph-up-arrow"></i> Projecao 6 Meses (recorrentes + parcelas) <i class="bi bi-arrows-fullscreen float-end"></i></small>
                </div>
                <div class="card-body p-2">
                    <canvas id="chartProjecao" height="70"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Graficos Adicionais -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card chart-card" style="cursor: pointer;" onclick="ampliarGrafico('comparativo', 'Comparativo Mensal')">
                <div class="card-header bg-light py-2">
                    <small><i class="bi bi-bar-chart-line"></i> Comparativo Mensal <i class="bi bi-arrows-fullscreen float-end"></i></small>
                </div>
                <div class="card-body p-2">
                    <canvas id="chartComparativo" height="160"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card chart-card" style="cursor: pointer;" onclick="ampliarGrafico('tendencia', 'Tendencia Anual')">
                <div class="card-header bg-light py-2">
                    <small><i class="bi bi-graph-up"></i> Tendencia 12 Meses <i class="bi bi-arrows-fullscreen float-end"></i></small>
                </div>
                <div class="card-body p-2">
                    <canvas id="chartTendencia" height="160"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card chart-card" style="cursor: pointer;" onclick="ampliarGrafico('diasSemana', 'Gastos por Dia da Semana')">
                <div class="card-header bg-light py-2">
                    <small><i class="bi bi-calendar-week"></i> Gastos/Dia Semana <i class="bi bi-arrows-fullscreen float-end"></i></small>
                </div>
                <div class="card-body p-2">
                    <canvas id="chartDiasSemana" height="160"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Despesas Parceladas -->
    @if($despesasParceladas->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-warning d-flex justify-content-between align-items-center py-2">
                    <span><i class="bi bi-credit-card"></i> Despesas Parceladas</span>
                    <span class="badge bg-dark">{{ $despesasParceladas->count() }} compras</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Descricao</th>
                                    <th>Valor Total</th>
                                    <th>Progresso</th>
                                    <th>Parcela</th>
                                    <th>Proxima</th>
                                    <th>Restante</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($despesasParceladas as $grupoId => $parcelas)
                                    @php
                                        $primeira = $parcelas->first();
                                        $parcelasPagas = $parcelas->filter(fn($p) => $p->data <= now())->count();
                                        $proximaParcela = $parcelas->where('data', '>', now())->first();
                                        $restante = $parcelas->where('data', '>', now())->sum('valor');
                                        $percent = ($parcelasPagas / $primeira->total_parcelas) * 100;
                                    @endphp
                                    <tr>
                                        <td>
                                            {{ $primeira->descricao }}
                                            @if($primeira->categoria)
                                                <br><small class="text-muted">{{ $primeira->categoria }}</small>
                                            @endif
                                        </td>
                                        <td class="valor-negativo">R$ {{ number_format($primeira->valor_total, 2, ',', '.') }}</td>
                                        <td style="min-width: 120px;">
                                            <div class="progress" style="height: 18px;">
                                                <div class="progress-bar bg-success" style="width: {{ $percent }}%">
                                                    <small>{{ $parcelasPagas }}/{{ $primeira->total_parcelas }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="valor-negativo">R$ {{ number_format($primeira->valor, 2, ',', '.') }}</td>
                                        <td>
                                            @if($proximaParcela)
                                                {{ $proximaParcela->data->format('d/m/Y') }}
                                            @else
                                                <span class="badge bg-success"><i class="bi bi-check"></i> Quitado</span>
                                            @endif
                                        </td>
                                        <td class="valor-negativo">R$ {{ number_format($restante, 2, ',', '.') }}</td>
                                        <td class="text-end">
                                            @if($proximaParcela)
                                                @php
                                                    $parcelasRestantes = $parcelas->where('data', '>', now())->count();
                                                @endphp
                                                <form action="{{ route('despesas.avancarParcela', $proximaParcela->_id) }}" method="POST" class="d-inline" title="Pagar 1 parcela">
                                                    @csrf @method('PATCH')
                                                    <button type="submit" class="btn btn-outline-success btn-sm"><i class="bi bi-check-lg"></i></button>
                                                </form>
                                                @if($parcelasRestantes > 1)
                                                    <button type="button" class="btn btn-outline-info btn-sm" onclick="abrirAdiantar('{{ $grupoId }}', {{ $parcelasRestantes }}, {{ $primeira->valor }})" title="Adiantar parcelas">
                                                        <i class="bi bi-fast-forward"></i>
                                                    </button>
                                                @endif
                                            @endif
                                            <form action="{{ route('despesas.destroyGrupo', $grupoId) }}" method="POST" class="d-inline" onsubmit="return confirm('Excluir TODAS as parcelas?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Itens Recorrentes -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success d-flex justify-content-between align-items-center py-2">
                    <span><i class="bi bi-arrow-repeat"></i> Receitas Recorrentes</span>
                    <span class="badge bg-light text-success">{{ $receitasRecorrentes->count() }}</span>
                </div>
                <div class="card-body" style="max-height: 200px; overflow-y: auto;">
                    @if($receitasRecorrentes->count() > 0)
                        <table class="table table-sm table-hover mb-0">
                            <tbody>
                                @foreach($receitasRecorrentes as $receita)
                                    <tr class="{{ !$receita->ativo ? 'opacity-50' : '' }}">
                                        <td>{{ $receita->descricao }}</td>
                                        <td class="valor-positivo">R$ {{ number_format($receita->valor, 2, ',', '.') }}</td>
                                        <td>Dia {{ $receita->dia_vencimento ?? '-' }}</td>
                                        <td class="text-end">
                                            <form action="{{ route('receitas.toggle', $receita->_id) }}" method="POST" class="d-inline">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="btn btn-outline-{{ $receita->ativo ? 'warning' : 'success' }} btn-sm py-0">
                                                    <i class="bi bi-{{ $receita->ativo ? 'pause' : 'play' }}"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('receitas.destroy', $receita->_id) }}" method="POST" class="d-inline" onsubmit="return confirm('Excluir?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm py-0"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-muted text-center mb-0 py-3"><i class="bi bi-inbox"></i> Nenhuma receita recorrente</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-danger d-flex justify-content-between align-items-center py-2">
                    <span><i class="bi bi-arrow-repeat"></i> Despesas Recorrentes</span>
                    <span class="badge bg-light text-danger">{{ $despesasRecorrentes->count() }}</span>
                </div>
                <div class="card-body" style="max-height: 200px; overflow-y: auto;">
                    @if($despesasRecorrentes->count() > 0)
                        <table class="table table-sm table-hover mb-0">
                            <tbody>
                                @foreach($despesasRecorrentes as $despesa)
                                    <tr class="{{ !$despesa->ativo ? 'opacity-50' : '' }}">
                                        <td>{{ $despesa->descricao }}</td>
                                        <td class="valor-negativo">R$ {{ number_format($despesa->valor, 2, ',', '.') }}</td>
                                        <td>Dia {{ $despesa->dia_vencimento ?? '-' }}</td>
                                        <td class="text-end">
                                            <form action="{{ route('despesas.toggle', $despesa->_id) }}" method="POST" class="d-inline">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="btn btn-outline-{{ $despesa->ativo ? 'warning' : 'success' }} btn-sm py-0">
                                                    <i class="bi bi-{{ $despesa->ativo ? 'pause' : 'play' }}"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('despesas.destroy', $despesa->_id) }}" method="POST" class="d-inline" onsubmit="return confirm('Excluir?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm py-0"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-muted text-center mb-0 py-3"><i class="bi bi-inbox"></i> Nenhuma despesa recorrente</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Lancamentos Recentes -->
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-success d-flex justify-content-between align-items-center py-2">
                    <span><i class="bi bi-arrow-up-circle"></i> Receitas Recentes</span>
                </div>
                <div class="card-body" style="max-height: 220px; overflow-y: auto;">
                    @php $receitasSimples = $receitas->filter(fn($r) => !$r->recorrente)->take(10); @endphp
                    @if($receitasSimples->count() > 0)
                        <table class="table table-sm table-hover mb-0">
                            <tbody>
                                @foreach($receitasSimples as $receita)
                                    <tr>
                                        <td>{{ $receita->descricao }}</td>
                                        <td class="valor-positivo">R$ {{ number_format($receita->valor, 2, ',', '.') }}</td>
                                        <td><small class="text-muted">{{ $receita->data ? $receita->data->format('d/m') : '-' }}</small></td>
                                        <td class="text-end">
                                            <form action="{{ route('receitas.destroy', $receita->_id) }}" method="POST" class="d-inline" onsubmit="return confirm('Excluir?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm py-0 px-1"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-muted text-center mb-0 py-4"><i class="bi bi-inbox" style="font-size: 2rem;"></i><br>Nenhuma receita</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-danger d-flex justify-content-between align-items-center py-2">
                    <span><i class="bi bi-arrow-down-circle"></i> Despesas Recentes</span>
                </div>
                <div class="card-body" style="max-height: 220px; overflow-y: auto;">
                    @php $despesasSimples = $despesas->filter(fn($d) => !$d->recorrente && !$d->parcelado)->take(10); @endphp
                    @if($despesasSimples->count() > 0)
                        <table class="table table-sm table-hover mb-0">
                            <tbody>
                                @foreach($despesasSimples as $despesa)
                                    <tr>
                                        <td>{{ $despesa->descricao }}</td>
                                        <td class="valor-negativo">R$ {{ number_format($despesa->valor, 2, ',', '.') }}</td>
                                        <td><small class="text-muted">{{ $despesa->data ? $despesa->data->format('d/m') : '-' }}</small></td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-outline-warning btn-sm py-0 px-1" onclick="editarDespesa('{{ $despesa->_id }}', '{{ $despesa->descricao }}', '{{ $despesa->valor }}', '{{ $despesa->data ? $despesa->data->format('Y-m-d') : '' }}', '{{ $despesa->categoria }}')">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form action="{{ route('despesas.destroy', $despesa->_id) }}" method="POST" class="d-inline" onsubmit="return confirm('Excluir?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm py-0 px-1"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-muted text-center mb-0 py-4"><i class="bi bi-inbox" style="font-size: 2rem;"></i><br>Nenhuma despesa</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Despesas Totais por Mes -->
    <div class="modal fade" id="modalDespesasTotais" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-danger py-2">
                    <h6 class="modal-title text-white"><i class="bi bi-calendar3"></i> Despesas Totais por Mes</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @if(!empty($despesasPorMes))
                        <div class="row">
                            @foreach($despesasPorMes as $mes)
                                <div class="col-md-4 col-sm-6 mb-3">
                                    <div class="card bg-dark border-secondary h-100">
                                        <div class="card-body py-2 text-center">
                                            <small class="text-muted d-block">{{ $mes['mes'] }}</small>
                                            <h5 class="valor-negativo mb-0">R$ {{ number_format($mes['total'], 2, ',', '.') }}</h5>
                                            <small class="text-muted">{{ $mes['quantidade'] }} despesa(s)</small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <hr class="my-3 border-secondary">
                        <div class="card bg-danger bg-opacity-25 border-danger">
                            <div class="card-body py-3 text-center">
                                <span class="text-muted">TOTAL GERAL DE DESPESAS</span>
                                <h2 class="valor-negativo mb-0 fw-bold">R$ {{ number_format($totalDespesas, 2, ',', '.') }}</h2>
                                <small class="text-muted">{{ $despesas->count() }} despesa(s) no total</small>
                            </div>
                        </div>
                    @else
                        <p class="text-muted text-center mb-0 py-3"><i class="bi bi-inbox"></i> Nenhuma despesa registrada</p>
                    @endif
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Receita -->
    <div class="modal fade" id="modalReceita" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success py-2">
                    <h6 class="modal-title text-white"><i class="bi bi-arrow-up-circle"></i> Nova Receita</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('receitas.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small">Descricao *</label>
                            <input type="text" name="descricao" class="form-control" placeholder="Ex: Salario" required>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label small">Valor (R$) *</label>
                                <input type="number" name="valor" class="form-control" step="0.01" min="0.01" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label small">Data *</label>
                                <input type="date" name="data" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Categoria</label>
                            <input type="text" name="categoria" class="form-control" list="cat-rec" placeholder="Opcional">
                            <datalist id="cat-rec">
                                <option value="Salario"><option value="Freelance"><option value="Investimentos"><option value="Bonus">
                            </datalist>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="recorrente" id="recRecorrente" onchange="document.getElementById('recRecFields').style.display=this.checked?'block':'none'">
                            <label class="form-check-label small" for="recRecorrente"><i class="bi bi-arrow-repeat"></i> Receita Recorrente</label>
                        </div>
                        <div id="recRecFields" style="display:none" class="mt-2 p-2 rounded" style="background: rgba(255,255,255,0.05);">
                            <div class="row">
                                <div class="col-6">
                                    <select name="frequencia" class="form-select form-select-sm">
                                        <option value="mensal">Mensal</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <input type="number" name="dia_vencimento" class="form-control form-control-sm" placeholder="Dia" min="1" max="31">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer py-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-check-lg"></i> Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Despesa -->
    <div class="modal fade" id="modalDespesa" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger py-2">
                    <h6 class="modal-title text-white"><i class="bi bi-arrow-down-circle"></i> Nova Despesa</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('despesas.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small">Descricao *</label>
                            <input type="text" name="descricao" class="form-control" placeholder="Ex: Aluguel" required>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label small">Valor (R$) *</label>
                                <input type="number" name="valor" id="despValor" class="form-control" step="0.01" min="0.01" required oninput="calcularParcela()">
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label small">Data *</label>
                                <input type="date" name="data" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Categoria</label>
                            <input type="text" name="categoria" class="form-control" list="cat-desp" placeholder="Opcional">
                            <datalist id="cat-desp">
                                <option value="Moradia"><option value="Alimentacao"><option value="Transporte"><option value="Saude"><option value="Lazer">
                            </datalist>
                        </div>
                        
                        <div class="btn-group w-100 mb-3" role="group">
                            <input type="radio" class="btn-check" name="tipoDespesa" id="tipoUnica" value="unica" checked onclick="mostrarTipo('unica')">
                            <label class="btn btn-outline-secondary btn-sm" for="tipoUnica"><i class="bi bi-1-circle"></i> Unica</label>
                            <input type="radio" class="btn-check" name="tipoDespesa" id="tipoRecorrente" value="recorrente" onclick="mostrarTipo('recorrente')">
                            <label class="btn btn-outline-secondary btn-sm" for="tipoRecorrente"><i class="bi bi-arrow-repeat"></i> Fixa</label>
                            <input type="radio" class="btn-check" name="tipoDespesa" id="tipoParcelada" value="parcelada" onclick="mostrarTipo('parcelada')">
                            <label class="btn btn-outline-secondary btn-sm" for="tipoParcelada"><i class="bi bi-credit-card"></i> Parcelada</label>
                        </div>

                        <input type="hidden" name="recorrente" id="inputRecorrente" value="0">
                        <input type="hidden" name="parcelado" id="inputParcelado" value="0">

                        <div id="camposRecorrente" style="display:none" class="p-2 rounded mb-2" style="background: rgba(255,255,255,0.05);">
                            <div class="row">
                                <div class="col-6">
                                    <label class="form-label small">Frequencia</label>
                                    <select name="frequencia" class="form-select form-select-sm">
                                        <option value="mensal">Mensal</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small">Dia</label>
                                    <input type="number" name="dia_vencimento" class="form-control form-control-sm" min="1" max="31">
                                </div>
                            </div>
                        </div>

                        <div id="camposParcelada" style="display:none" class="p-2 rounded mb-2" style="background: rgba(255,255,255,0.05);">
                            <div class="row align-items-center">
                                <div class="col-5">
                                    <label class="form-label small">Parcelas</label>
                                    <select name="total_parcelas" id="totalParcelas" class="form-select form-select-sm" onchange="calcularParcela()">
                                        @for($i = 2; $i <= 12; $i++)
                                            <option value="{{ $i }}">{{ $i }}x</option>
                                        @endfor
                                        <option value="18">18x</option>
                                        <option value="24">24x</option>
                                        <option value="36">36x</option>
                                        <option value="48">48x</option>
                                    </select>
                                </div>
                                <div class="col-7">
                                    <div class="alert alert-info py-1 px-2 mb-0 small">
                                        <i class="bi bi-calculator"></i> <strong id="valorParcela">R$ 0,00</strong>/mes
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer py-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-check-lg"></i> Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Multiplas Despesas -->
    <div class="modal fade" id="modalMultiplasDespesas" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-danger py-2">
                    <h6 class="modal-title text-white"><i class="bi bi-list-ul"></i> Adicionar Multiplas Despesas</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('despesas.storeMultiplas') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small">Data para todas as despesas</label>
                            <input type="date" name="data" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" style="max-width: 200px;">
                        </div>
                        <div id="listaDespesas">
                            <div class="row mb-2 despesa-item">
                                <div class="col-5">
                                    <input type="text" name="despesas[0][descricao]" class="form-control form-control-sm" placeholder="Descricao" required>
                                </div>
                                <div class="col-3">
                                    <input type="number" name="despesas[0][valor]" class="form-control form-control-sm" placeholder="Valor" step="0.01" min="0.01" required>
                                </div>
                                <div class="col-3">
                                    <input type="text" name="despesas[0][categoria]" class="form-control form-control-sm" placeholder="Categoria">
                                </div>
                                <div class="col-1">
                                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="removerLinha(this)" disabled><i class="bi bi-x"></i></button>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-success btn-sm mt-2" onclick="adicionarLinha()">
                            <i class="bi bi-plus-lg"></i> Adicionar Linha
                        </button>
                    </div>
                    <div class="modal-footer py-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-check-lg"></i> Salvar Todas</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Grafico Ampliado -->
    <div class="modal fade" id="modalGrafico" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h5 class="modal-title" id="tituloGrafico"><i class="bi bi-bar-chart"></i> Grafico</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3" id="detalhesGrafico"></div>
                    <canvas id="chartAmpliado" height="350"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Adiantar Parcelas -->
    <div class="modal fade" id="modalAdiantar" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header bg-info py-2">
                    <h6 class="modal-title text-white"><i class="bi bi-fast-forward"></i> Adiantar Parcelas</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formAdiantar" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small">Quantas parcelas adiantar?</label>
                            <input type="number" name="quantidade" id="qtdAdiantar" class="form-control" min="1" value="1" onchange="calcularAdiantamento()">
                            <small class="text-muted">Máximo: <span id="maxParcelas">0</span> parcelas</small>
                        </div>
                        <div class="alert alert-info py-2 mb-0">
                            <small>Valor total: <strong id="valorAdiantamento">R$ 0,00</strong></small>
                        </div>
                    </div>
                    <div class="modal-footer py-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-info btn-sm"><i class="bi bi-check-lg"></i> Adiantar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Despesa -->
    <div class="modal fade" id="modalEditarDespesa" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning py-2">
                    <h6 class="modal-title"><i class="bi bi-pencil"></i> Editar Despesa</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formEditarDespesa" method="POST">
                    @csrf @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small">Descricao *</label>
                            <input type="text" name="descricao" id="editDescricao" class="form-control" required>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label small">Valor (R$) *</label>
                                <input type="number" name="valor" id="editValor" class="form-control" step="0.01" min="0.01" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label small">Data *</label>
                                <input type="date" name="data" id="editData" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Categoria</label>
                            <input type="text" name="categoria" id="editCategoria" class="form-control" placeholder="Opcional">
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
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    function mostrarTipo(tipo) {
        document.getElementById('camposRecorrente').style.display = tipo === 'recorrente' ? 'block' : 'none';
        document.getElementById('camposParcelada').style.display = tipo === 'parcelada' ? 'block' : 'none';
        document.getElementById('inputRecorrente').value = tipo === 'recorrente' ? '1' : '0';
        document.getElementById('inputParcelado').value = tipo === 'parcelada' ? '1' : '0';
        
        // Animar campos
        if (typeof gsap !== 'undefined') {
            const el = document.getElementById(tipo === 'recorrente' ? 'camposRecorrente' : 'camposParcelada');
            if (tipo !== 'unica') {
                gsap.fromTo(el, { opacity: 0, y: -10 }, { opacity: 1, y: 0, duration: 0.3 });
            }
        }
        calcularParcela();
    }
    
    function calcularParcela() {
        const valor = parseFloat(document.getElementById('despValor').value) || 0;
        const parcelas = parseInt(document.getElementById('totalParcelas').value) || 1;
        const valorParcela = valor / parcelas;
        document.getElementById('valorParcela').textContent = 'R$ ' + valorParcela.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    // Funcoes para multiplas despesas
    let linhaCount = 1;
    function adicionarLinha() {
        const container = document.getElementById('listaDespesas');
        const novaLinha = document.createElement('div');
        novaLinha.className = 'row mb-2 despesa-item';
        novaLinha.innerHTML = `
            <div class="col-5">
                <input type="text" name="despesas[${linhaCount}][descricao]" class="form-control form-control-sm" placeholder="Descricao" required>
            </div>
            <div class="col-3">
                <input type="number" name="despesas[${linhaCount}][valor]" class="form-control form-control-sm" placeholder="Valor" step="0.01" min="0.01" required>
            </div>
            <div class="col-3">
                <input type="text" name="despesas[${linhaCount}][categoria]" class="form-control form-control-sm" placeholder="Categoria">
            </div>
            <div class="col-1">
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="removerLinha(this)"><i class="bi bi-x"></i></button>
            </div>
        `;
        container.appendChild(novaLinha);
        linhaCount++;
        atualizarBotoes();
    }

    function removerLinha(btn) {
        btn.closest('.despesa-item').remove();
        atualizarBotoes();
    }

    function atualizarBotoes() {
        const itens = document.querySelectorAll('.despesa-item');
        itens.forEach((item, index) => {
            const btn = item.querySelector('button');
            btn.disabled = itens.length === 1;
        });
    }

    // Funcao para editar despesa
    function editarDespesa(id, descricao, valor, data, categoria) {
        document.getElementById('formEditarDespesa').action = '/despesas/' + id;
        document.getElementById('editDescricao').value = descricao;
        document.getElementById('editValor').value = valor;
        document.getElementById('editData').value = data;
        document.getElementById('editCategoria').value = categoria || '';
        new bootstrap.Modal(document.getElementById('modalEditarDespesa')).show();
    }

    // Funcoes para adiantar parcelas
    let valorParcelaAtual = 0;
    let maxParcelasAtual = 0;

    function abrirAdiantar(grupoId, maxParcelas, valorParcela) {
        document.getElementById('formAdiantar').action = '/despesas/grupo/' + grupoId + '/adiantar';
        document.getElementById('maxParcelas').textContent = maxParcelas;
        document.getElementById('qtdAdiantar').max = maxParcelas;
        document.getElementById('qtdAdiantar').value = 1;
        valorParcelaAtual = valorParcela;
        maxParcelasAtual = maxParcelas;
        calcularAdiantamento();
        new bootstrap.Modal(document.getElementById('modalAdiantar')).show();
    }

    function calcularAdiantamento() {
        let qtd = parseInt(document.getElementById('qtdAdiantar').value) || 1;
        if (qtd > maxParcelasAtual) qtd = maxParcelasAtual;
        if (qtd < 1) qtd = 1;
        const total = qtd * valorParcelaAtual;
        document.getElementById('valorAdiantamento').textContent = 'R$ ' + total.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    // Funcao para ampliar graficos
    let chartAmpliado = null;

    function ampliarGrafico(tipo, titulo) {
        document.getElementById('tituloGrafico').innerHTML = '<i class="bi bi-bar-chart"></i> ' + titulo;

        // Destruir grafico anterior se existir
        if (chartAmpliado) {
            chartAmpliado.destroy();
        }

        const ctx = document.getElementById('chartAmpliado').getContext('2d');
        let config = {};
        let detalhes = '';

        switch(tipo) {
            case 'pizza':
                const totalGeral = totalReceitas + totalDespesas;
                const percentReceitas = totalGeral > 0 ? ((totalReceitas / totalGeral) * 100).toFixed(1) : 0;
                const percentDespesas = totalGeral > 0 ? ((totalDespesas / totalGeral) * 100).toFixed(1) : 0;
                detalhes = `
                    <div class="col-md-4">
                        <div class="card bg-success bg-opacity-10 border-success">
                            <div class="card-body py-2 text-center">
                                <small class="text-muted">Total Receitas</small>
                                <h4 class="valor-positivo mb-0">R$ ${totalReceitas.toLocaleString('pt-BR', {minimumFractionDigits: 2})}</h4>
                                <small class="text-success">${percentReceitas}% do total</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-danger bg-opacity-10 border-danger">
                            <div class="card-body py-2 text-center">
                                <small class="text-muted">Total Despesas</small>
                                <h4 class="valor-negativo mb-0">R$ ${totalDespesas.toLocaleString('pt-BR', {minimumFractionDigits: 2})}</h4>
                                <small class="text-danger">${percentDespesas}% do total</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-info bg-opacity-10 border-info">
                            <div class="card-body py-2 text-center">
                                <small class="text-muted">Saldo</small>
                                <h4 class="${(totalReceitas - totalDespesas) >= 0 ? 'valor-positivo' : 'valor-negativo'} mb-0">R$ ${(totalReceitas - totalDespesas).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</h4>
                            </div>
                        </div>
                    </div>`;
                config = {
                    type: 'doughnut',
                    data: {
                        labels: ['Receitas', 'Despesas'],
                        datasets: [{
                            data: [totalReceitas, totalDespesas],
                            backgroundColor: ['#00ff88', '#ff4757'],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { position: 'bottom' },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.label + ': R$ ' + context.raw.toLocaleString('pt-BR', {minimumFractionDigits: 2});
                                    }
                                }
                            }
                        }
                    }
                };
                break;

            case 'despesasCategoria':
                const categDespesas = Object.entries(despesasPorCategoria);
                detalhes = '<div class="col-12"><div class="table-responsive"><table class="table table-sm"><thead><tr><th>Categoria</th><th class="text-end">Valor</th><th class="text-end">%</th></tr></thead><tbody>';
                const totalDesp = Object.values(despesasPorCategoria).reduce((a, b) => a + b, 0);
                categDespesas.sort((a, b) => b[1] - a[1]).forEach(([cat, val]) => {
                    detalhes += `<tr><td>${cat}</td><td class="text-end valor-negativo">R$ ${val.toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td><td class="text-end">${((val/totalDesp)*100).toFixed(1)}%</td></tr>`;
                });
                detalhes += '</tbody></table></div></div>';
                config = {
                    type: 'bar',
                    data: {
                        labels: Object.keys(despesasPorCategoria),
                        datasets: [{
                            label: 'Valor',
                            data: Object.values(despesasPorCategoria),
                            backgroundColor: coresNeon,
                            borderRadius: 5
                        }]
                    },
                    options: {
                        responsive: true,
                        indexAxis: 'y',
                        plugins: { legend: { display: false } },
                        scales: { x: { beginAtZero: true } }
                    }
                };
                break;

            case 'receitasCategoria':
                const categReceitas = Object.entries(receitasPorCategoria);
                detalhes = '<div class="col-12"><div class="table-responsive"><table class="table table-sm"><thead><tr><th>Categoria</th><th class="text-end">Valor</th><th class="text-end">%</th></tr></thead><tbody>';
                const totalRec = Object.values(receitasPorCategoria).reduce((a, b) => a + b, 0);
                categReceitas.sort((a, b) => b[1] - a[1]).forEach(([cat, val]) => {
                    detalhes += `<tr><td>${cat}</td><td class="text-end valor-positivo">R$ ${val.toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td><td class="text-end">${((val/totalRec)*100).toFixed(1)}%</td></tr>`;
                });
                detalhes += '</tbody></table></div></div>';
                config = {
                    type: 'bar',
                    data: {
                        labels: Object.keys(receitasPorCategoria),
                        datasets: [{
                            label: 'Valor',
                            data: Object.values(receitasPorCategoria),
                            backgroundColor: coresNeon,
                            borderRadius: 5
                        }]
                    },
                    options: {
                        responsive: true,
                        indexAxis: 'y',
                        plugins: { legend: { display: false } },
                        scales: { x: { beginAtZero: true } }
                    }
                };
                break;

            case 'evolucao':
                detalhes = '<div class="col-12"><div class="table-responsive"><table class="table table-sm"><thead><tr><th>Dia</th><th class="text-end">Receitas</th><th class="text-end">Despesas</th><th class="text-end">Saldo</th></tr></thead><tbody>';
                evolucaoDias.forEach((dia, i) => {
                    const saldo = evolucaoReceitas[i] - evolucaoDespesas[i];
                    detalhes += `<tr><td>${dia}</td><td class="text-end valor-positivo">R$ ${evolucaoReceitas[i].toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td><td class="text-end valor-negativo">R$ ${evolucaoDespesas[i].toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td><td class="text-end ${saldo >= 0 ? 'valor-positivo' : 'valor-negativo'}">R$ ${saldo.toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td></tr>`;
                });
                detalhes += '</tbody></table></div></div>';
                config = {
                    type: 'line',
                    data: {
                        labels: evolucaoDias,
                        datasets: [
                            { label: 'Receitas', data: evolucaoReceitas, borderColor: '#00ff88', backgroundColor: 'rgba(0,255,136,0.2)', fill: true, tension: 0.4 },
                            { label: 'Despesas', data: evolucaoDespesas, borderColor: '#ff4757', backgroundColor: 'rgba(255,71,87,0.2)', fill: true, tension: 0.4 }
                        ]
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { position: 'bottom' } },
                        scales: { y: { beginAtZero: true } }
                    }
                };
                break;

            case 'projecao':
                detalhes = '<div class="col-12"><div class="table-responsive"><table class="table table-sm"><thead><tr><th>Mes</th><th class="text-end">Receitas</th><th class="text-end">Despesas</th><th class="text-end">Saldo</th></tr></thead><tbody>';
                projecaoMeses.forEach((mes, i) => {
                    detalhes += `<tr><td>${mes}</td><td class="text-end valor-positivo">R$ ${projecaoReceitasMensal[i].toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td><td class="text-end valor-negativo">R$ ${projecaoDespesasMensal[i].toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td><td class="text-end ${projecaoSaldoMensal[i] >= 0 ? 'valor-positivo' : 'valor-negativo'}">R$ ${projecaoSaldoMensal[i].toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td></tr>`;
                });
                detalhes += '</tbody></table></div></div>';
                config = {
                    type: 'bar',
                    data: {
                        labels: projecaoMeses,
                        datasets: [
                            { label: 'Receitas', data: projecaoReceitasMensal, backgroundColor: 'rgba(0,255,136,0.7)', borderRadius: 5 },
                            { label: 'Despesas', data: projecaoDespesasMensal, backgroundColor: 'rgba(255,71,87,0.7)', borderRadius: 5 },
                            { label: 'Saldo', data: projecaoSaldoMensal, type: 'line', borderColor: '#3742fa', borderWidth: 3, fill: false, tension: 0.4 }
                        ]
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { position: 'bottom' } },
                        scales: { y: { beginAtZero: true } }
                    }
                };
                break;

            case 'comparativo':
                const compLabels = @json($comparativoLabels);
                const compAnterior = @json($comparativoMesAnterior);
                const compAtual = @json($comparativoMesAtual);
                const diffReceitas = compAtual.receitas - compAnterior.receitas;
                const diffDespesas = compAtual.despesas - compAnterior.despesas;
                detalhes = `
                    <div class="col-md-6">
                        <div class="card bg-dark border-secondary">
                            <div class="card-body py-2 text-center">
                                <small class="text-muted">${compLabels[0]}</small>
                                <div class="d-flex justify-content-around mt-2">
                                    <div><small class="text-muted">Receitas</small><br><span class="valor-positivo">R$ ${compAnterior.receitas.toLocaleString('pt-BR', {minimumFractionDigits: 2})}</span></div>
                                    <div><small class="text-muted">Despesas</small><br><span class="valor-negativo">R$ ${compAnterior.despesas.toLocaleString('pt-BR', {minimumFractionDigits: 2})}</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-dark border-secondary">
                            <div class="card-body py-2 text-center">
                                <small class="text-muted">${compLabels[1]} (atual)</small>
                                <div class="d-flex justify-content-around mt-2">
                                    <div><small class="text-muted">Receitas</small><br><span class="valor-positivo">R$ ${compAtual.receitas.toLocaleString('pt-BR', {minimumFractionDigits: 2})}</span><br><small class="${diffReceitas >= 0 ? 'text-success' : 'text-danger'}">${diffReceitas >= 0 ? '+' : ''}${diffReceitas.toLocaleString('pt-BR', {minimumFractionDigits: 2})}</small></div>
                                    <div><small class="text-muted">Despesas</small><br><span class="valor-negativo">R$ ${compAtual.despesas.toLocaleString('pt-BR', {minimumFractionDigits: 2})}</span><br><small class="${diffDespesas <= 0 ? 'text-success' : 'text-danger'}">${diffDespesas >= 0 ? '+' : ''}${diffDespesas.toLocaleString('pt-BR', {minimumFractionDigits: 2})}</small></div>
                                </div>
                            </div>
                        </div>
                    </div>`;
                config = {
                    type: 'bar',
                    data: {
                        labels: compLabels,
                        datasets: [
                            { label: 'Receitas', data: [compAnterior.receitas, compAtual.receitas], backgroundColor: ['rgba(0,255,136,0.5)', 'rgba(0,255,136,0.9)'], borderRadius: 5 },
                            { label: 'Despesas', data: [compAnterior.despesas, compAtual.despesas], backgroundColor: ['rgba(255,71,87,0.5)', 'rgba(255,71,87,0.9)'], borderRadius: 5 }
                        ]
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { position: 'bottom' } },
                        scales: { y: { beginAtZero: true } }
                    }
                };
                break;

            case 'tendencia':
                const tendMeses = @json($tendenciaMeses);
                const tendReceitas = @json($tendenciaReceitas);
                const tendDespesas = @json($tendenciaDespesas);
                const tendSaldo = @json($tendenciaSaldo);
                detalhes = '<div class="col-12"><div class="table-responsive" style="max-height: 200px; overflow-y: auto;"><table class="table table-sm"><thead><tr><th>Mes</th><th class="text-end">Receitas</th><th class="text-end">Despesas</th><th class="text-end">Saldo</th></tr></thead><tbody>';
                tendMeses.forEach((mes, i) => {
                    detalhes += `<tr><td>${mes}</td><td class="text-end valor-positivo">R$ ${tendReceitas[i].toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td><td class="text-end valor-negativo">R$ ${tendDespesas[i].toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td><td class="text-end ${tendSaldo[i] >= 0 ? 'valor-positivo' : 'valor-negativo'}">R$ ${tendSaldo[i].toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td></tr>`;
                });
                detalhes += '</tbody></table></div></div>';
                config = {
                    type: 'line',
                    data: {
                        labels: tendMeses,
                        datasets: [
                            { label: 'Receitas', data: tendReceitas, borderColor: '#00ff88', backgroundColor: 'rgba(0,255,136,0.2)', fill: true, tension: 0.4 },
                            { label: 'Despesas', data: tendDespesas, borderColor: '#ff4757', backgroundColor: 'rgba(255,71,87,0.2)', fill: true, tension: 0.4 },
                            { label: 'Saldo', data: tendSaldo, borderColor: '#3742fa', borderWidth: 2, fill: false, tension: 0.4, borderDash: [5, 5] }
                        ]
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { position: 'bottom' } },
                        scales: { y: { grid: { color: 'rgba(255,255,255,0.05)' } } }
                    }
                };
                break;

            case 'diasSemana':
                const dias = @json($diasSemana);
                const gastosDia = @json($gastosPorDiaSemana);
                const totalGastos = gastosDia.reduce((a, b) => a + b, 0);
                detalhes = '<div class="col-12"><div class="table-responsive"><table class="table table-sm"><thead><tr><th>Dia</th><th class="text-end">Total Gasto</th><th class="text-end">%</th></tr></thead><tbody>';
                dias.forEach((dia, i) => {
                    const percent = totalGastos > 0 ? ((gastosDia[i] / totalGastos) * 100).toFixed(1) : 0;
                    detalhes += `<tr><td>${dia}</td><td class="text-end valor-negativo">R$ ${gastosDia[i].toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td><td class="text-end">${percent}%</td></tr>`;
                });
                detalhes += '</tbody></table></div></div>';
                config = {
                    type: 'bar',
                    data: {
                        labels: dias,
                        datasets: [{
                            label: 'Gastos',
                            data: gastosDia,
                            backgroundColor: ['rgba(255,71,87,0.7)', 'rgba(55,66,250,0.7)', 'rgba(55,66,250,0.7)', 'rgba(55,66,250,0.7)', 'rgba(55,66,250,0.7)', 'rgba(55,66,250,0.7)', 'rgba(255,71,87,0.7)'],
                            borderRadius: 5
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true } }
                    }
                };
                break;
        }

        document.getElementById('detalhesGrafico').innerHTML = detalhes;
        chartAmpliado = new Chart(ctx, config);
        new bootstrap.Modal(document.getElementById('modalGrafico')).show();
    }

    // Animar barras de progresso
    setTimeout(() => {
        document.querySelectorAll('.progress-animated').forEach(bar => {
            const width = bar.dataset.width;
            if (typeof gsap !== 'undefined') {
                gsap.to(bar, { width: width + '%', duration: 1, ease: 'power2.out' });
            } else {
                bar.style.width = width + '%';
            }
        });
    }, 800);

    // Config Chart.js para tema escuro
    Chart.defaults.color = '#888';
    Chart.defaults.borderColor = 'rgba(255,255,255,0.1)';

    const totalReceitas = {{ $totalReceitas }};
    const totalDespesas = {{ $totalDespesas }};
    const receitasPorCategoria = @json($receitasPorCategoria ?? []);
    const despesasPorCategoria = @json($despesasPorCategoria ?? []);
    const evolucaoDias = @json($evolucaoDias ?? []);
    const evolucaoReceitas = @json($evolucaoReceitas ?? []);
    const evolucaoDespesas = @json($evolucaoDespesas ?? []);
    const projecaoMeses = @json($projecaoMeses ?? []);
    const projecaoReceitasMensal = @json($projecaoReceitasMensal ?? []);
    const projecaoDespesasMensal = @json($projecaoDespesasMensal ?? []);
    const projecaoSaldoMensal = @json($projecaoSaldoMensal ?? []);

    const coresNeon = ['#00ff88', '#ff4757', '#3742fa', '#ffa502', '#a55eea', '#1dd1a1', '#ff6b81', '#5f27cd'];

    // Inicializar graficos com delay para animacao
    setTimeout(() => {
        new Chart(document.getElementById('chartPizza'), {
            type: 'doughnut',
            data: { 
                labels: ['Receitas', 'Despesas'], 
                datasets: [{ 
                    data: [totalReceitas, totalDespesas], 
                    backgroundColor: ['#00ff88', '#ff4757'],
                    borderWidth: 0,
                    hoverOffset: 10
                }] 
            },
            options: { 
                responsive: true,
                animation: { animateScale: true, animateRotate: true },
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 10 } } }
            }
        });

        new Chart(document.getElementById('chartDespesasCategoria'), {
            type: 'bar',
            data: { 
                labels: Object.keys(despesasPorCategoria).length ? Object.keys(despesasPorCategoria) : ['--'], 
                datasets: [{ 
                    data: Object.values(despesasPorCategoria).length ? Object.values(despesasPorCategoria) : [0], 
                    backgroundColor: coresNeon,
                    borderRadius: 5,
                    borderSkipped: false
                }] 
            },
            options: { 
                responsive: true,
                animation: { duration: 1000 },
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' } }, x: { grid: { display: false } } }
            }
        });

        new Chart(document.getElementById('chartReceitasCategoria'), {
            type: 'bar',
            data: { 
                labels: Object.keys(receitasPorCategoria).length ? Object.keys(receitasPorCategoria) : ['--'], 
                datasets: [{ 
                    data: Object.values(receitasPorCategoria).length ? Object.values(receitasPorCategoria) : [0], 
                    backgroundColor: coresNeon,
                    borderRadius: 5
                }] 
            },
            options: { 
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' } }, x: { grid: { display: false } } }
            }
        });

        new Chart(document.getElementById('chartEvolucao'), {
            type: 'line',
            data: { 
                labels: evolucaoDias,
                datasets: [
                    { label: 'Receitas', data: evolucaoReceitas, borderColor: '#00ff88', backgroundColor: 'rgba(0,255,136,0.1)', fill: true, tension: 0.4, borderWidth: 2, pointRadius: 3, pointBackgroundColor: '#00ff88' },
                    { label: 'Despesas', data: evolucaoDespesas, borderColor: '#ff4757', backgroundColor: 'rgba(255,71,87,0.1)', fill: true, tension: 0.4, borderWidth: 2, pointRadius: 3, pointBackgroundColor: '#ff4757' }
                ]
            },
            options: { 
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' } }, x: { grid: { display: false } } }
            }
        });

        new Chart(document.getElementById('chartProjecao'), {
            type: 'bar',
            data: {
                labels: projecaoMeses,
                datasets: [
                    { label: 'Receitas', data: projecaoReceitasMensal, backgroundColor: 'rgba(0,255,136,0.6)', borderRadius: 5 },
                    { label: 'Despesas', data: projecaoDespesasMensal, backgroundColor: 'rgba(255,71,87,0.6)', borderRadius: 5 },
                    { label: 'Saldo', data: projecaoSaldoMensal, type: 'line', borderColor: '#3742fa', borderWidth: 3, tension: 0.4, fill: false, pointRadius: 4, pointBackgroundColor: '#3742fa' }
                ]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 12 } } },
                scales: { y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' } }, x: { grid: { display: false } } }
            }
        });

        // Grafico Comparativo Mensal
        const comparativoLabels = @json($comparativoLabels);
        const comparativoMesAnterior = @json($comparativoMesAnterior);
        const comparativoMesAtual = @json($comparativoMesAtual);

        new Chart(document.getElementById('chartComparativo'), {
            type: 'bar',
            data: {
                labels: comparativoLabels,
                datasets: [
                    { label: 'Receitas', data: [comparativoMesAnterior.receitas, comparativoMesAtual.receitas], backgroundColor: ['rgba(0,255,136,0.4)', 'rgba(0,255,136,0.8)'], borderRadius: 5 },
                    { label: 'Despesas', data: [comparativoMesAnterior.despesas, comparativoMesAtual.despesas], backgroundColor: ['rgba(255,71,87,0.4)', 'rgba(255,71,87,0.8)'], borderRadius: 5 }
                ]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' } }, x: { grid: { display: false } } }
            }
        });

        // Grafico Tendencia Anual
        const tendenciaMeses = @json($tendenciaMeses);
        const tendenciaReceitas = @json($tendenciaReceitas);
        const tendenciaDespesas = @json($tendenciaDespesas);
        const tendenciaSaldo = @json($tendenciaSaldo);

        new Chart(document.getElementById('chartTendencia'), {
            type: 'line',
            data: {
                labels: tendenciaMeses,
                datasets: [
                    { label: 'Receitas', data: tendenciaReceitas, borderColor: '#00ff88', backgroundColor: 'rgba(0,255,136,0.1)', fill: true, tension: 0.4, borderWidth: 2, pointRadius: 2 },
                    { label: 'Despesas', data: tendenciaDespesas, borderColor: '#ff4757', backgroundColor: 'rgba(255,71,87,0.1)', fill: true, tension: 0.4, borderWidth: 2, pointRadius: 2 },
                    { label: 'Saldo', data: tendenciaSaldo, borderColor: '#3742fa', borderWidth: 2, tension: 0.4, fill: false, pointRadius: 2, borderDash: [5, 5] }
                ]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { grid: { color: 'rgba(255,255,255,0.05)' } }, x: { grid: { display: false } } }
            }
        });

        // Grafico Gastos por Dia da Semana
        const diasSemana = @json($diasSemana);
        const gastosPorDiaSemana = @json($gastosPorDiaSemana);

        new Chart(document.getElementById('chartDiasSemana'), {
            type: 'bar',
            data: {
                labels: diasSemana,
                datasets: [{
                    label: 'Gastos',
                    data: gastosPorDiaSemana,
                    backgroundColor: [
                        'rgba(255,71,87,0.6)',
                        'rgba(55,66,250,0.6)',
                        'rgba(55,66,250,0.6)',
                        'rgba(55,66,250,0.6)',
                        'rgba(55,66,250,0.6)',
                        'rgba(55,66,250,0.6)',
                        'rgba(255,71,87,0.6)'
                    ],
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' } }, x: { grid: { display: false } } }
            }
        });
    }, 600);
</script>
@endsection
