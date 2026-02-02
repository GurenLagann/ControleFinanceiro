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
    <!-- Cards de Resumo -->
    <div class="row mb-4">
        @php
            $alertasAtivos = $alertas->where('ativo', true);
            $vencimentos = $alertasAtivos->where('tipo', 'vencimento')->count();
            $limites = $alertasAtivos->where('tipo', 'limite')->count();
            $metasAlertas = $alertasAtivos->where('tipo', 'meta')->count();
        @endphp
        <div class="col-md-3">
            <div class="card glow-red" style="opacity: 1 !important; border-left: 4px solid #ff4757;">
                <div class="card-body text-center py-3">
                    <h6 class="text-muted mb-1"><i class="bi bi-bell"></i> Nao Lidos</h6>
                    <h3 class="mb-0 valor-negativo">{{ $alertasNaoLidos }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card glow-purple" style="opacity: 1 !important; border-left: 4px solid #6f42c1;">
                <div class="card-body text-center py-3">
                    <h6 class="text-muted mb-1"><i class="bi bi-calendar-event"></i> Vencimentos</h6>
                    <h3 class="mb-0" style="color: #a5b4fc;">{{ $vencimentos }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card" style="opacity: 1 !important; border-left: 4px solid #ffc107;">
                <div class="card-body text-center py-3">
                    <h6 class="text-muted mb-1"><i class="bi bi-shield-exclamation"></i> Limites</h6>
                    <h3 class="mb-0" style="color: #ffc107;">{{ $limites }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card glow-blue" style="opacity: 1 !important; border-left: 4px solid #3742fa;">
                <div class="card-body text-center py-3">
                    <h6 class="text-muted mb-1"><i class="bi bi-bullseye"></i> Metas</h6>
                    <h3 class="mb-0" style="color: #3742fa;">{{ $metasAlertas }}</h3>
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
                        <div class="border-bottom p-3 d-flex align-items-center {{ $alerta->lido ? 'bg-dark bg-opacity-25' : '' }}">
                            <div class="me-3">
                                @if($alerta->tipo === 'vencimento')
                                    <span class="badge bg-danger p-2"><i class="bi bi-calendar-event"></i></span>
                                @elseif($alerta->tipo === 'limite')
                                    <span class="badge bg-warning text-dark p-2"><i class="bi bi-shield-exclamation"></i></span>
                                @elseif($alerta->tipo === 'meta')
                                    <span class="badge bg-primary p-2"><i class="bi bi-bullseye"></i></span>
                                @elseif($alerta->tipo === 'lembrete')
                                    <span class="badge bg-secondary p-2"><i class="bi bi-sticky"></i></span>
                                @else
                                    <span class="badge bg-info p-2"><i class="bi bi-info-circle"></i></span>
                                @endif
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong class="{{ $alerta->lido ? 'text-muted' : '' }}">{{ $alerta->titulo }}</strong>
                                        @if(!$alerta->lido)
                                            <span class="badge bg-danger ms-2">Novo</span>
                                        @endif
                                    </div>
                                    <small class="text-muted">
                                        {{ $alerta->data_alerta ? $alerta->data_alerta->format('d/m/Y') : '-' }}
                                    </small>
                                </div>
                                <p class="mb-0 text-muted small">{{ $alerta->mensagem }}</p>
                            </div>
                            <div class="ms-3 d-flex gap-1">
                                @if(!$alerta->lido)
                                    <form action="{{ route('alertas.lido', $alerta->_id) }}" method="POST">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="btn btn-outline-success btn-sm" title="Marcar como lido">
                                            <i class="bi bi-check"></i>
                                        </button>
                                    </form>
                                @endif
                                <form action="{{ route('alertas.destroy', $alerta->_id) }}" method="POST" onsubmit="return confirm('Excluir este alerta?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm" title="Excluir">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-bell-slash" style="font-size: 3rem;"></i>
                            <p class="mt-2">Nenhum alerta no momento</p>
                            <small>Alertas serao gerados automaticamente quando houver vencimentos proximos ou limites ultrapassados.</small>
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
                    <li><strong>Limites:</strong> Alertas quando uma meta de limite de gasto e ultrapassada</li>
                    <li><strong>Metas:</strong> Alertas quando uma meta esta proxima do prazo (menos de 7 dias)</li>
                    <li><strong>Lembretes:</strong> Alertas personalizados criados por voce</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Modal Lembrete -->
    <div class="modal fade" id="modalLembrete" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
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
        <div class="modal-dialog modal-dialog-centered">
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
                            <div class="col-6 mb-3">
                                <label class="form-label small">Valor (R$)</label>
                                <input type="number" name="valor" class="form-control" step="0.01" placeholder="0,00">
                            </div>
                            <div class="col-6 mb-3">
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
        <div class="modal-dialog modal-dialog-centered">
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
                            <div class="col-6 mb-3">
                                <label class="form-label small">Limite (R$) *</label>
                                <input type="number" name="valor_limite" class="form-control" step="0.01" required placeholder="0,00">
                            </div>
                            <div class="col-6 mb-3">
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
        <div class="modal-dialog modal-dialog-centered">
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
                            <div class="col-6 mb-3">
                                <label class="form-label small">Valor Alvo (R$)</label>
                                <input type="number" name="valor_alvo" class="form-control" step="0.01" placeholder="0,00">
                            </div>
                            <div class="col-6 mb-3">
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
