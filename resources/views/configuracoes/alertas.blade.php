@extends('layouts.app')

@section('page-title')
    Alertas @if($alertasNaoLidos > 0)<span class="badge bg-danger">{{ $alertasNaoLidos }}</span>@endif
@endsection

@section('page-actions')
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
                </ul>
            </div>
        </div>
    </div>
@endsection
