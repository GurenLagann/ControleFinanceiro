@extends('layouts.app')

@section('page-title', 'Backup e Restauracao')

@section('page-actions')
    <a href="{{ route('backup.exportar') }}" class="btn btn-success btn-sm">
        <i class="bi bi-download"></i> <span class="d-none d-sm-inline">Baixar Backup</span>
    </a>
@endsection

@section('content')
    <!-- Cards de Estatisticas -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card" style="opacity: 1 !important; border-left: 4px solid #00ff88;">
                <div class="card-body text-center py-3">
                    <h6 class="text-muted mb-1"><i class="bi bi-arrow-up-circle"></i> Receitas</h6>
                    <h3 class="mb-0 valor-positivo">{{ number_format($estatisticas['receitas']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card" style="opacity: 1 !important; border-left: 4px solid #ff4757;">
                <div class="card-body text-center py-3">
                    <h6 class="text-muted mb-1"><i class="bi bi-arrow-down-circle"></i> Despesas</h6>
                    <h3 class="mb-0 valor-negativo">{{ number_format($estatisticas['despesas']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card" style="opacity: 1 !important; border-left: 4px solid #6f42c1;">
                <div class="card-body text-center py-3">
                    <h6 class="text-muted mb-1"><i class="bi bi-palette"></i> Categorias</h6>
                    <h3 class="mb-0">{{ number_format($estatisticas['categorias']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card" style="opacity: 1 !important; border-left: 4px solid #3742fa;">
                <div class="card-body text-center py-3">
                    <h6 class="text-muted mb-1"><i class="bi bi-bullseye"></i> Metas</h6>
                    <h3 class="mb-0">{{ number_format($estatisticas['metas']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card" style="opacity: 1 !important; border-left: 4px solid #ffc107;">
                <div class="card-body text-center py-3">
                    <h6 class="text-muted mb-1"><i class="bi bi-bell"></i> Alertas</h6>
                    <h3 class="mb-0">{{ number_format($estatisticas['alertas']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card" style="opacity: 1 !important; border-left: 4px solid #17a2b8;">
                <div class="card-body text-center py-3">
                    <h6 class="text-muted mb-1"><i class="bi bi-database"></i> Total</h6>
                    <h3 class="mb-0">{{ number_format($totalRegistros) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Exportar Backup -->
        <div class="col-md-6 mb-4">
            <div class="card h-100" style="opacity: 1 !important;">
                <div class="card-header bg-success py-2">
                    <span class="text-white"><i class="bi bi-cloud-download"></i> Exportar Backup</span>
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        Exporte todos os seus dados financeiros em um arquivo JSON.
                        Este arquivo pode ser usado para restaurar seus dados posteriormente.
                    </p>

                    <div class="alert alert-info mb-3">
                        <h6 class="mb-1"><i class="bi bi-info-circle"></i> O backup inclui:</h6>
                        <ul class="mb-0 small">
                            <li>Todas as receitas e despesas</li>
                            <li>Categorias personalizadas</li>
                            <li>Metas financeiras</li>
                            <li>Alertas configurados</li>
                        </ul>
                    </div>

                    <a href="{{ route('backup.exportar') }}" class="btn btn-success w-100">
                        <i class="bi bi-download"></i> Baixar Backup Completo
                    </a>
                </div>
            </div>
        </div>

        <!-- Importar Backup -->
        <div class="col-md-6 mb-4">
            <div class="card h-100" style="opacity: 1 !important;">
                <div class="card-header bg-warning py-2">
                    <span class="text-dark"><i class="bi bi-cloud-upload"></i> Restaurar Backup</span>
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        Restaure seus dados a partir de um arquivo de backup JSON exportado anteriormente.
                    </p>

                    <form action="{{ route('backup.importar') }}" method="POST" enctype="multipart/form-data" id="formRestore">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small">Arquivo de Backup</label>
                            <input type="file" name="backup" class="form-control" accept=".json" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small">Modo de Importacao</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="modo" id="modoSubstituir" value="substituir" checked>
                                <label class="form-check-label" for="modoSubstituir">
                                    <strong>Substituir tudo</strong>
                                    <small class="text-muted d-block">Apaga todos os dados atuais antes de importar</small>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="modo" id="modoMesclar" value="mesclar">
                                <label class="form-check-label" for="modoMesclar">
                                    <strong>Mesclar dados</strong>
                                    <small class="text-muted d-block">Adiciona os registros sem apagar os existentes</small>
                                </label>
                            </div>
                        </div>

                        <button type="button" class="btn btn-warning w-100" data-bs-toggle="modal" data-bs-target="#modalConfirmar">
                            <i class="bi bi-upload"></i> Restaurar Backup
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Exportar Dados -->
    <div class="row">
        <div class="col-12">
            <div class="card" style="opacity: 1 !important;">
                <div class="card-header bg-info py-2">
                    <span class="text-white"><i class="bi bi-file-earmark-spreadsheet"></i> Exportar Dados (CSV)</span>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">
                        Exporte seus dados em formato CSV para abrir em planilhas como Excel, Google Sheets ou LibreOffice.
                    </p>
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <a href="{{ route('exportar.csv.receitas') }}" class="btn btn-outline-success w-100">
                                <i class="bi bi-file-earmark-text"></i> Receitas (CSV)
                            </a>
                        </div>
                        <div class="col-md-4 mb-2">
                            <a href="{{ route('exportar.csv.despesas') }}" class="btn btn-outline-danger w-100">
                                <i class="bi bi-file-earmark-text"></i> Despesas (CSV)
                            </a>
                        </div>
                        <div class="col-md-4 mb-2">
                            <a href="{{ route('exportar.csv.transacoes') }}" class="btn btn-outline-primary w-100">
                                <i class="bi bi-file-earmark-text"></i> Todas Transacoes (CSV)
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Info -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="alert alert-secondary" style="opacity: 1 !important;">
                <h6><i class="bi bi-shield-check"></i> Dicas de Seguranca</h6>
                <ul class="mb-0 small">
                    <li>Faca backups regulares dos seus dados</li>
                    <li>Guarde os arquivos de backup em local seguro (nuvem, HD externo)</li>
                    <li>Antes de restaurar, verifique se o arquivo e de uma fonte confiavel</li>
                    <li>Ao usar "Substituir tudo", todos os dados atuais serao perdidos</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Modal Confirmar Restauracao -->
    <div class="modal fade" id="modalConfirmar" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning py-2">
                    <h6 class="modal-title text-dark"><i class="bi bi-exclamation-triangle"></i> Confirmar Restauracao</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="msgConfirmacao">Tem certeza que deseja restaurar o backup?</p>
                    <p class="text-danger small" id="avisoSubstituir">
                        <i class="bi bi-exclamation-circle"></i>
                        <strong>Atencao:</strong> Ao escolher "Substituir tudo", todos os dados atuais serao permanentemente apagados!
                    </p>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-warning btn-sm" onclick="document.getElementById('formRestore').submit()">
                        <i class="bi bi-upload"></i> Confirmar Restauracao
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    // Atualizar aviso baseado no modo selecionado
    document.querySelectorAll('input[name="modo"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const aviso = document.getElementById('avisoSubstituir');
            if (this.value === 'substituir') {
                aviso.style.display = 'block';
            } else {
                aviso.style.display = 'none';
            }
        });
    });
</script>
@endsection
