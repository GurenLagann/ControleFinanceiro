@extends('layouts.app')

@section('page-title')
    Alertas @if($alertasNaoLidos > 0)<span class="badge bg-danger">{{ $alertasNaoLidos }}</span>@endif
@endsection

@section('page-actions')
    <div class="dropdown d-inline">
        <button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
            <i class="bi bi-plus-lg"></i> <span class="d-none d-sm-inline">Novo Alerta</span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#modalLembrete"><i class="bi bi-sticky text-secondary"></i> Lembrete</a></li>
            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#modalVencimento"><i class="bi bi-calendar-event text-danger"></i> Vencimento</a></li>
            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#modalLimite"><i class="bi bi-shield-exclamation text-warning"></i> Limite</a></li>
            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#modalMeta"><i class="bi bi-bullseye text-primary"></i> Meta</a></li>
        </ul>
    </div>
    @if($alertasNaoLidos > 0)
        <form action="{{ route('alertas.marcarTodosLidos') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-outline-success btn-sm">
                <i class="bi bi-check-all"></i> <span class="d-none d-sm-inline">Marcar Lidos</span>
            </button>
        </form>
    @endif
@endsection

@section('content')
    <!-- Resumo -->
    @php
        $alertasAtivos = $alertas->where('ativo', true);
        $vencimentos = $alertasAtivos->where('tipo', 'vencimento')->count();
        $limites = $alertasAtivos->where('tipo', 'limite')->count();
        $metasAlertas = $alertasAtivos->where('tipo', 'meta')->count();
        $conquistas = $alertasAtivos->where('tipo', 'conquista')->count();
    @endphp
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body d-flex flex-wrap gap-4 justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon r"><i class="bi bi-bell" aria-hidden="true"></i></div>
                        <div>
                            <div class="stat-label">Não lidos</div>
                            <div class="stat-value {{ $alertasNaoLidos > 0 ? 'valor-negativo' : '' }}">{{ $alertasNaoLidos }}</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon p"><i class="bi bi-calendar-event" aria-hidden="true"></i></div>
                        <div>
                            <div class="stat-label">Vencimentos</div>
                            <div class="stat-value">{{ $vencimentos }}</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon w"><i class="bi bi-shield-exclamation" aria-hidden="true"></i></div>
                        <div>
                            <div class="stat-label">Limites</div>
                            <div class="stat-value">{{ $limites }}</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon b"><i class="bi bi-bullseye" aria-hidden="true"></i></div>
                        <div>
                            <div class="stat-label">Metas</div>
                            <div class="stat-value">{{ $metasAlertas }}</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon g"><i class="bi bi-trophy" aria-hidden="true"></i></div>
                        <div>
                            <div class="stat-label">Conquistas</div>
                            <div class="stat-value">{{ $conquistas }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Alertas -->
    <div class="row">
        <div class="col-12">
            <div class="card" style="opacity: 1 !important;">
                <div class="card-header bg-light py-2">
                    <span><i class="bi bi-list"></i> Todos os Alertas</span>
                    <span class="badge bg-secondary float-end">{{ $alertas->count() }} alertas</span>
                </div>
                <div class="card-body p-0">
                    @forelse($alertas as $alerta)
                        @php
                            $iconClass = match($alerta->tipo) {
                                'vencimento' => 'r', 'limite' => 'w', 'meta' => 'b',
                                'lembrete' => 'n', 'conquista' => 'g', default => 'p',
                            };
                            $iconBi = match($alerta->tipo) {
                                'vencimento' => 'calendar-event', 'limite' => 'shield-exclamation', 'meta' => 'bullseye',
                                'lembrete' => 'sticky', 'conquista' => 'trophy', default => 'info-circle',
                            };
                        @endphp
                        <div class="feed-row px-3" style="{{ $alerta->lido ? 'opacity:.6;' : '' }}">
                            <div class="feed-icon {{ $iconClass }}"><i class="bi bi-{{ $iconBi }}" aria-hidden="true"></i></div>
                            <div class="feed-body">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <div>
                                        <strong>{{ $alerta->titulo }}</strong>
                                        @if(!$alerta->lido)
                                            <span class="badge bg-danger ms-1">Novo</span>
                                        @endif
                                    </div>
                                    <small class="text-muted text-nowrap">
                                        {{ $alerta->data_alerta ? $alerta->data_alerta->format('d/m/Y') : '-' }}
                                    </small>
                                </div>
                                <div class="feed-cat">{{ $alerta->mensagem }}</div>
                            </div>
                            <div class="d-flex gap-1">
                                @if(!$alerta->lido)
                                    <form action="{{ route('alertas.lido', $alerta->_id) }}" method="POST">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="btn btn-outline-success btn-sm btn-icon" title="Marcar como lido" aria-label="Marcar como lido">
                                            <i class="bi bi-check" aria-hidden="true"></i>
                                        </button>
                                    </form>
                                @endif
                                <form action="{{ route('alertas.destroy', $alerta->_id) }}" method="POST" onsubmit="return confirm('Excluir este alerta?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm btn-icon" title="Excluir" aria-label="Excluir alerta">
                                        <i class="bi bi-trash" aria-hidden="true"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">
                            <i class="bi bi-bell-slash" aria-hidden="true"></i>
                            <p class="mb-1">Nenhum alerta no momento</p>
                            <small>Alertas serão gerados automaticamente quando houver vencimentos próximos ou limites ultrapassados.</small>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Info -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="alert alert-info" style="opacity: 1 !important;">
                <h6><i class="bi bi-info-circle"></i> Como funcionam os alertas?</h6>
                <ul class="mb-0 small">
                    <li><strong>Vencimentos:</strong> Alertas automaticos para despesas recorrentes com vencimento nos proximos 7 dias</li>
                    <li><strong>Limites:</strong> Alertas quando uma meta de limite de gasto ou o orçamento mensal de uma categoria e ultrapassado</li>
                    <li><strong>Metas:</strong> Alertas quando uma meta esta proxima do prazo (menos de 7 dias)</li>
                    <li><strong>Lembretes:</strong> Alertas personalizados criados por voce</li>
                    <li><strong>Conquistas:</strong> Gerados automaticamente ao concluir uma meta ou quitar todas as parcelas de uma despesa parcelada</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Modal Lembrete -->
    <div class="modal fade" id="modalLembrete" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header py-2" style="background: #6c757d;">
                    <h6 class="modal-title text-white"><i class="bi bi-sticky-fill"></i> Novo Lembrete</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('alertas.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="tipo" value="lembrete">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small">Titulo *</label>
                            <input type="text" name="titulo" class="form-control" required placeholder="Ex: Verificar extrato bancario">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Descricao *</label>
                            <textarea name="mensagem" class="form-control" rows="3" required placeholder="Detalhes do lembrete..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Data do Lembrete</label>
                            <input type="date" name="data_alerta" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="modal-footer py-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-secondary btn-sm"><i class="bi bi-check-lg"></i> Criar Lembrete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Vencimento -->
    <div class="modal fade" id="modalVencimento" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header py-2" style="background: #dc3545;">
                    <h6 class="modal-title text-white"><i class="bi bi-calendar-event-fill"></i> Alerta de Vencimento</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('alertas.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="tipo" value="vencimento">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small">Conta/Despesa *</label>
                            <input type="text" name="titulo" class="form-control" required placeholder="Ex: Conta de Luz, Aluguel, IPTU">
                        </div>
                        <div class="row">
                            <div class="col-12 col-sm-6 mb-3">
                                <label class="form-label small">Valor (R$)</label>
                                <input type="number" name="valor" class="form-control" step="0.01" placeholder="0,00">
                            </div>
                            <div class="col-12 col-sm-6 mb-3">
                                <label class="form-label small">Data de Vencimento *</label>
                                <input type="date" name="data_alerta" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Observacoes</label>
                            <textarea name="mensagem" class="form-control" rows="2" placeholder="Informacoes adicionais..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer py-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-check-lg"></i> Criar Alerta</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Limite -->
    <div class="modal fade" id="modalLimite" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header py-2" style="background: #ffc107;">
                    <h6 class="modal-title text-dark"><i class="bi bi-shield-exclamation"></i> Alerta de Limite</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('alertas.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="tipo" value="limite">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small">Categoria/Area *</label>
                            <input type="text" name="titulo" class="form-control" required placeholder="Ex: Alimentacao, Transporte, Lazer">
                        </div>
                        <div class="row">
                            <div class="col-12 col-sm-6 mb-3">
                                <label class="form-label small">Limite (R$) *</label>
                                <input type="number" name="valor_limite" class="form-control" step="0.01" required placeholder="0,00">
                            </div>
                            <div class="col-12 col-sm-6 mb-3">
                                <label class="form-label small">Valor Atual (R$)</label>
                                <input type="number" name="valor_atual" class="form-control" step="0.01" placeholder="0,00">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Mensagem de Alerta</label>
                            <textarea name="mensagem" class="form-control" rows="2" placeholder="Ex: Atencao! Limite de gastos atingido"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer py-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning btn-sm text-dark"><i class="bi bi-check-lg"></i> Criar Alerta</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Meta -->
    <div class="modal fade" id="modalMeta" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header py-2" style="background: #0d6efd;">
                    <h6 class="modal-title text-white"><i class="bi bi-bullseye"></i> Alerta de Meta</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('alertas.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="tipo" value="meta">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small">Nome da Meta *</label>
                            <input type="text" name="titulo" class="form-control" required placeholder="Ex: Reserva de Emergencia, Viagem">
                        </div>
                        <div class="row">
                            <div class="col-12 col-sm-6 mb-3">
                                <label class="form-label small">Valor Alvo (R$)</label>
                                <input type="number" name="valor_alvo" class="form-control" step="0.01" placeholder="0,00">
                            </div>
                            <div class="col-12 col-sm-6 mb-3">
                                <label class="form-label small">Data Limite</label>
                                <input type="date" name="data_alerta" class="form-control">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Descricao</label>
                            <textarea name="mensagem" class="form-control" rows="2" placeholder="Detalhes sobre a meta..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer py-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-check-lg"></i> Criar Alerta</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
