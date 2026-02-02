<?php

use App\Http\Controllers\FinancaController;
use App\Http\Controllers\ConfiguracaoController;
use App\Http\Controllers\ExportController;
use Illuminate\Support\Facades\Route;

Route::get('/', [FinancaController::class, 'index'])->name('financas.index');
Route::get('/transacoes', [FinancaController::class, 'transacoes'])->name('financas.transacoes');

// Categorias
Route::get('/categorias', [ConfiguracaoController::class, 'categorias'])->name('categorias.index');
Route::post('/categorias', [ConfiguracaoController::class, 'storeCategoria'])->name('categorias.store');
Route::put('/categorias/{id}', [ConfiguracaoController::class, 'updateCategoria'])->name('categorias.update');
Route::delete('/categorias/{id}', [ConfiguracaoController::class, 'destroyCategoria'])->name('categorias.destroy');
Route::patch('/categorias/{id}/toggle', [ConfiguracaoController::class, 'toggleCategoria'])->name('categorias.toggle');

// Metas
Route::get('/metas', [ConfiguracaoController::class, 'metas'])->name('metas.index');
Route::post('/metas', [ConfiguracaoController::class, 'storeMeta'])->name('metas.store');
Route::put('/metas/{id}', [ConfiguracaoController::class, 'updateMeta'])->name('metas.update');
Route::delete('/metas/{id}', [ConfiguracaoController::class, 'destroyMeta'])->name('metas.destroy');

// Alertas
Route::get('/alertas', [ConfiguracaoController::class, 'alertas'])->name('alertas.index');
Route::post('/alertas', [ConfiguracaoController::class, 'storeAlerta'])->name('alertas.store');
Route::patch('/alertas/{id}/lido', [ConfiguracaoController::class, 'marcarAlertaLido'])->name('alertas.lido');
Route::post('/alertas/marcar-todos-lidos', [ConfiguracaoController::class, 'marcarTodosAlertasLidos'])->name('alertas.marcarTodosLidos');
Route::delete('/alertas/{id}', [ConfiguracaoController::class, 'destroyAlerta'])->name('alertas.destroy');

// Logs de Auditoria
Route::get('/logs', [ConfiguracaoController::class, 'logs'])->name('logs.index');
Route::get('/logs/{id}', [ConfiguracaoController::class, 'showLog'])->name('logs.show');
Route::post('/logs/limpar', [ConfiguracaoController::class, 'limparLogs'])->name('logs.limpar');

// API
Route::get('/api/alertas', [ConfiguracaoController::class, 'getAlertasNaoLidos'])->name('api.alertas');
Route::get('/api/categorias', [ConfiguracaoController::class, 'getCategoriasJson'])->name('api.categorias');

Route::post('/receitas', [FinancaController::class, 'storeReceita'])->name('receitas.store');
Route::put('/receitas/{id}', [FinancaController::class, 'updateReceita'])->name('receitas.update');
Route::delete('/receitas/{id}', [FinancaController::class, 'destroyReceita'])->name('receitas.destroy');
Route::patch('/receitas/{id}/toggle', [FinancaController::class, 'toggleRecorrenteReceita'])->name('receitas.toggle');

Route::post('/despesas', [FinancaController::class, 'storeDespesa'])->name('despesas.store');
Route::post('/despesas/multiplas', [FinancaController::class, 'storeMultiplasDespesas'])->name('despesas.storeMultiplas');
Route::put('/despesas/{id}', [FinancaController::class, 'updateDespesa'])->name('despesas.update');
Route::patch('/despesas/{id}/avancar-parcela', [FinancaController::class, 'avancarParcela'])->name('despesas.avancarParcela');
Route::post('/despesas/grupo/{grupoId}/adiantar', [FinancaController::class, 'adiantarParcelas'])->name('despesas.adiantarParcelas');
Route::delete('/despesas/{id}', [FinancaController::class, 'destroyDespesa'])->name('despesas.destroy');
Route::delete('/despesas/grupo/{grupoId}', [FinancaController::class, 'destroyDespesaGrupo'])->name('despesas.destroyGrupo');
Route::patch('/despesas/{id}/toggle', [FinancaController::class, 'toggleRecorrenteDespesa'])->name('despesas.toggle');

// Exportacao PDF
Route::get('/exportar/pdf/relatorio', [ExportController::class, 'relatorioPdf'])->name('exportar.pdf.relatorio');
Route::get('/exportar/pdf/extrato', [ExportController::class, 'extratoPdf'])->name('exportar.pdf.extrato');

// Exportacao CSV
Route::get('/exportar/csv/receitas', [ExportController::class, 'receitasCsv'])->name('exportar.csv.receitas');
Route::get('/exportar/csv/despesas', [ExportController::class, 'despesasCsv'])->name('exportar.csv.despesas');
Route::get('/exportar/csv/transacoes', [ExportController::class, 'transacoesCsv'])->name('exportar.csv.transacoes');

// Backup e Restauracao
Route::get('/backup', [ExportController::class, 'backupIndex'])->name('backup.index');
Route::get('/backup/exportar', [ExportController::class, 'exportarBackup'])->name('backup.exportar');
Route::post('/backup/importar', [ExportController::class, 'importarBackup'])->name('backup.importar');
