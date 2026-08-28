@extends('layouts.app')

@section('page-title', 'Logs de Auditoria')

@section('page-actions')
    <button class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalLimpar">
        <i class="bi bi-trash"></i> <span class="d-none d-sm-inline">Limpar</span>
    </button>
@endsection

@section('content')
    <!-- Resumo -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body d-flex flex-wrap gap-4 justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon p"><i class="bi bi-database" aria-hidden="true"></i></div>
                        <div>
                            <div class="stat-label">Total</div>
                            <div class="stat-value">{{ number_format($estatisticas['total']) }}</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon b"><i class="bi bi-calendar-day" aria-hidden="true"></i></div>
                        <div>
                            <div class="stat-label">Hoje</div>
                            <div class="stat-value">{{ number_format($estatisticas['hoje']) }}</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon g"><i class="bi bi-plus-circle" aria-hidden="true"></i></div>
                        <div>
                            <div class="stat-label">Criação</div>
                            <div class="stat-value valor-positivo">{{ number_format($estatisticas['creates']) }}</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon w"><i class="bi bi-pencil" aria-hidden="true"></i></div>
                        <div>
                            <div class="stat-label">Edição</div>
                            <div class="stat-value">{{ number_format($estatisticas['updates']) }}</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon r"><i class="bi bi-trash" aria-hidden="true"></i></div>
                        <div>
                            <div class="stat-label">Exclusão</div>
                            <div class="stat-value valor-negativo">{{ number_format($estatisticas['deletes']) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card" style="opacity: 1 !important;">
                <div class="card-body py-2">
                    <form method="GET" class="row g-2 align-items-center">
                        <div class="col-4 col-md-2">
                            <select name="model" class="form-select form-select-sm">
                                <option value="">Todos os Modelos</option>
                                <option value="Receita" {{ request('model') == 'Receita' ? 'selected' : '' }}>Receita</option>
                                <option value="Despesa" {{ request('model') == 'Despesa' ? 'selected' : '' }}>Despesa</option>
                                <option value="Meta" {{ request('model') == 'Meta' ? 'selected' : '' }}>Meta</option>
                                <option value="Categoria" {{ request('model') == 'Categoria' ? 'selected' : '' }}>Categoria</option>
                            </select>
                        </div>
                        <div class="col-4 col-md-2">
                            <select name="action" class="form-select form-select-sm">
                                <option value="">Todas as Acoes</option>
                                <option value="create" {{ request('action') == 'create' ? 'selected' : '' }}>Criacao</option>
                                <option value="update" {{ request('action') == 'update' ? 'selected' : '' }}>Edicao</option>
                                <option value="delete" {{ request('action') == 'delete' ? 'selected' : '' }}>Exclusao</option>
                            </select>
                        </div>
                        <div class="col-4 col-md-2">
                            <input type="date" name="data_inicio" class="form-control form-control-sm" placeholder="Data Inicio" value="{{ request('data_inicio') }}">
                        </div>
                        <div class="col-4 col-md-2">
                            <input type="date" name="data_fim" class="form-control form-control-sm" placeholder="Data Fim" value="{{ request('data_fim') }}">
                        </div>
                        <div class="col-4 col-md-2">
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <i class="bi bi-search"></i> Filtrar
                            </button>
                        </div>
                        <div class="col-4 col-md-2">
                            <a href="{{ route('logs.index') }}" class="btn btn-outline-secondary btn-sm w-100">
                                <i class="bi bi-x"></i> Limpar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabela de Logs -->
    <div class="row">
        <div class="col-12">
            <div class="card" style="opacity: 1 !important;">
                <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-list-ul"></i> Registros</span>
                    <span class="badge bg-secondary">{{ $logs->total() }} logs</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Data/Hora</th>
                                    <th>Acao</th>
                                    <th>Modelo</th>
                                    <th>ID</th>
                                    <th>IP</th>
                                    <th class="text-center">Detalhes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $log)
                                    <tr>
                                        <td class="small">
                                            {{ $log->created_at->format('d/m/Y H:i:s') }}
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $log->badge_color }}">
                                                @if($log->action === 'create')
                                                    <i class="bi bi-plus"></i>
                                                @elseif($log->action === 'update')
                                                    <i class="bi bi-pencil"></i>
                                                @else
                                                    <i class="bi bi-trash"></i>
                                                @endif
                                                {{ $log->action_label }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $log->model_name }}</span>
                                        </td>
                                        <td class="small text-muted" style="max-width: 100px; overflow: hidden; text-overflow: ellipsis;">
                                            {{ Str::limit($log->model_id, 12) }}
                                        </td>
                                        <td class="small text-muted">
                                            {{ $log->user_ip }}
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-outline-info btn-sm" onclick="verDetalhes('{{ $log->_id }}')">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6">
                                            <div class="empty-state">
                                                <i class="bi bi-inbox" aria-hidden="true"></i>
                                                Nenhum log encontrado
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($logs->hasPages())
                <div class="card-footer py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            Mostrando {{ $logs->firstItem() }} - {{ $logs->lastItem() }} de {{ $logs->total() }}
                        </small>
                        <nav>
                            {{ $logs->links('pagination::bootstrap-5') }}
                        </nav>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal Detalhes -->
    <div class="modal fade" id="modalDetalhes" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-fullscreen-md-down modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-info py-2">
                    <h6 class="modal-title text-white"><i class="bi bi-info-circle"></i> Detalhes do Log</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-12 col-sm-6 mb-2 mb-sm-0">
                            <label class="form-label small text-muted">Modelo</label>
                            <p id="detalheModelo" class="mb-0"></p>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label small text-muted">Acao</label>
                            <p id="detalheAcao" class="mb-0"></p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12 col-sm-6 mb-2 mb-sm-0">
                            <label class="form-label small text-muted">Data/Hora</label>
                            <p id="detalheData" class="mb-0"></p>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label small text-muted">IP</label>
                            <p id="detalheIP" class="mb-0"></p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 col-md-6 mb-3 mb-md-0">
                            <label class="form-label small text-muted">Valores Antigos</label>
                            <pre id="detalheOld" class="bg-dark text-light p-2 rounded small" style="max-height: 300px; overflow: auto;"></pre>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small text-muted">Valores Novos</label>
                            <pre id="detalheNew" class="bg-dark text-light p-2 rounded small" style="max-height: 300px; overflow: auto;"></pre>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Limpar Logs -->
    <div class="modal fade" id="modalLimpar" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-danger py-2">
                    <h6 class="modal-title text-white"><i class="bi bi-exclamation-triangle"></i> Limpar Logs Antigos</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('logs.limpar') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p class="text-muted">Selecione quantos dias de logs deseja manter:</p>
                        <div class="mb-3">
                            <select name="dias" class="form-select">
                                <option value="7">Manter ultimos 7 dias</option>
                                <option value="15">Manter ultimos 15 dias</option>
                                <option value="30" selected>Manter ultimos 30 dias</option>
                                <option value="60">Manter ultimos 60 dias</option>
                                <option value="90">Manter ultimos 90 dias</option>
                            </select>
                        </div>
                        <p class="small text-danger"><i class="bi bi-exclamation-circle"></i> Esta acao nao pode ser desfeita!</p>
                    </div>
                    <div class="modal-footer py-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i> Limpar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    function verDetalhes(id) {
        fetch('/logs/' + id)
            .then(response => response.json())
            .then(data => {
                document.getElementById('detalheModelo').textContent = data.model_type;
                document.getElementById('detalheAcao').innerHTML = '<span class="badge bg-' +
                    (data.action === 'create' ? 'success' : (data.action === 'update' ? 'warning' : 'danger')) + '">' +
                    (data.action === 'create' ? 'Criacao' : (data.action === 'update' ? 'Edicao' : 'Exclusao')) + '</span>';
                document.getElementById('detalheData').textContent = new Date(data.created_at).toLocaleString('pt-BR');
                document.getElementById('detalheIP').textContent = data.user_ip;
                document.getElementById('detalheOld').textContent = JSON.stringify(data.old_values, null, 2) || '-';
                document.getElementById('detalheNew').textContent = JSON.stringify(data.new_values, null, 2) || '-';

                new bootstrap.Modal(document.getElementById('modalDetalhes')).show();
            })
            .catch(error => {
                console.error('Erro ao carregar detalhes:', error);
                alert('Erro ao carregar detalhes do log');
            });
    }
</script>
@endsection
