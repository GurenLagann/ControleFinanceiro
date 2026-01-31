<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Meta;
use App\Models\Alerta;
use App\Models\AuditLog;
use App\Models\Despesa;
use App\Models\Receita;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class ConfiguracaoController extends Controller
{
    // ==================== CATEGORIAS ====================

    public function categorias()
    {
        $categorias = Categoria::orderBy('nome')->get();
        return view('configuracoes.categorias', compact('categorias'));
    }

    public function storeCategoria(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:100',
            'cor' => 'required|string|max:7',
            'icone' => 'nullable|string|max:50',
            'tipo' => 'required|in:receita,despesa,ambos',
        ]);

        $validated['ativo'] = true;

        Categoria::create($validated);

        return redirect()->route('categorias.index')
            ->with('success', 'Categoria criada com sucesso!');
    }

    public function updateCategoria(Request $request, string $id)
    {
        $categoria = Categoria::findOrFail($id);

        $validated = $request->validate([
            'nome' => 'required|string|max:100',
            'cor' => 'required|string|max:7',
            'icone' => 'nullable|string|max:50',
            'tipo' => 'required|in:receita,despesa,ambos',
        ]);

        $categoria->update($validated);

        return redirect()->route('categorias.index')
            ->with('success', 'Categoria atualizada com sucesso!');
    }

    public function destroyCategoria(string $id)
    {
        $categoria = Categoria::findOrFail($id);
        $categoria->delete();

        return redirect()->route('categorias.index')
            ->with('success', 'Categoria removida com sucesso!');
    }

    public function toggleCategoria(string $id)
    {
        $categoria = Categoria::findOrFail($id);
        $categoria->ativo = !$categoria->ativo;
        $categoria->save();

        $status = $categoria->ativo ? 'ativada' : 'desativada';
        return redirect()->route('categorias.index')
            ->with('success', "Categoria {$status} com sucesso!");
    }

    // ==================== METAS ====================

    public function metas()
    {
        $metas = Meta::orderBy('data_fim')->get();

        // Atualizar valores atuais das metas baseado nas transacoes
        foreach ($metas as $meta) {
            $this->atualizarValorMeta($meta);
        }

        return view('configuracoes.metas', compact('metas'));
    }

    private function atualizarValorMeta(Meta $meta)
    {
        $dataInicio = $meta->data_inicio ?? Carbon::now()->startOfMonth();
        $dataFim = $meta->data_fim ?? Carbon::now()->endOfMonth();

        if ($meta->tipo === 'economia') {
            // Meta de economia: receitas - despesas no periodo
            $receitas = Receita::whereBetween('data', [$dataInicio, $dataFim])->sum('valor');
            $despesas = Despesa::whereBetween('data', [$dataInicio, $dataFim])->sum('valor');
            $meta->valor_atual = max(0, $receitas - $despesas);
        } elseif ($meta->tipo === 'limite_gasto') {
            // Meta de limite: total de despesas no periodo (quanto menor, melhor)
            $despesas = Despesa::whereBetween('data', [$dataInicio, $dataFim]);
            if ($meta->categoria) {
                $despesas = $despesas->where('categoria', $meta->categoria);
            }
            $meta->valor_atual = $despesas->sum('valor');
        } elseif ($meta->tipo === 'receita') {
            // Meta de receita: total de receitas no periodo
            $receitas = Receita::whereBetween('data', [$dataInicio, $dataFim]);
            if ($meta->categoria) {
                $receitas = $receitas->where('categoria', $meta->categoria);
            }
            $meta->valor_atual = $receitas->sum('valor');
        }

        $meta->save();
    }

    public function storeMeta(Request $request)
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'descricao' => 'nullable|string|max:500',
            'valor_alvo' => 'required|numeric|min:0.01',
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',
            'categoria' => 'nullable|string|max:100',
            'tipo' => 'required|in:economia,limite_gasto,receita',
        ]);

        $validated['ativo'] = true;
        $validated['valor_atual'] = 0;

        $meta = Meta::create($validated);
        $this->atualizarValorMeta($meta);

        return redirect()->route('metas.index')
            ->with('success', 'Meta criada com sucesso!');
    }

    public function updateMeta(Request $request, string $id)
    {
        $meta = Meta::findOrFail($id);

        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'descricao' => 'nullable|string|max:500',
            'valor_alvo' => 'required|numeric|min:0.01',
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',
            'categoria' => 'nullable|string|max:100',
            'tipo' => 'required|in:economia,limite_gasto,receita',
        ]);

        $meta->update($validated);
        $this->atualizarValorMeta($meta);

        return redirect()->route('metas.index')
            ->with('success', 'Meta atualizada com sucesso!');
    }

    public function destroyMeta(string $id)
    {
        $meta = Meta::findOrFail($id);
        $meta->delete();

        return redirect()->route('metas.index')
            ->with('success', 'Meta removida com sucesso!');
    }

    // ==================== ALERTAS ====================

    public function alertas()
    {
        // Gerar alertas automaticos
        $this->gerarAlertasAutomaticos();

        $alertas = Alerta::orderBy('data_alerta', 'desc')->get();
        $alertasNaoLidos = Alerta::naoLidos()->count();

        return view('configuracoes.alertas', compact('alertas', 'alertasNaoLidos'));
    }

    private function gerarAlertasAutomaticos()
    {
        $hoje = Carbon::now();
        $proximosDias = Carbon::now()->addDays(7);

        // Alertas de despesas recorrentes proximas do vencimento
        $despesasRecorrentes = Despesa::where('recorrente', true)
            ->where('ativo', true)
            ->whereNotNull('dia_vencimento')
            ->get();

        foreach ($despesasRecorrentes as $despesa) {
            $diaVencimento = $despesa->dia_vencimento;
            $dataVencimento = Carbon::now()->day($diaVencimento);

            if ($dataVencimento->isPast()) {
                $dataVencimento->addMonth();
            }

            if ($dataVencimento->between($hoje, $proximosDias)) {
                // Verificar se ja existe alerta para esta despesa neste mes
                $alertaExistente = Alerta::where('referencia_tipo', 'despesa')
                    ->where('referencia_id', $despesa->_id)
                    ->where('data_alerta', '>=', Carbon::now()->startOfMonth())
                    ->first();

                if (!$alertaExistente) {
                    Alerta::create([
                        'titulo' => 'Vencimento Proximo',
                        'mensagem' => "A despesa '{$despesa->descricao}' vence em " . $dataVencimento->format('d/m'),
                        'tipo' => 'vencimento',
                        'data_alerta' => $dataVencimento,
                        'referencia_tipo' => 'despesa',
                        'referencia_id' => $despesa->_id,
                    ]);
                }
            }
        }

        // Alertas de metas proximas do vencimento ou ultrapassadas
        $metas = Meta::where('ativo', true)->get();

        foreach ($metas as $meta) {
            if ($meta->tipo === 'limite_gasto' && $meta->valor_atual > $meta->valor_alvo) {
                $alertaExistente = Alerta::where('referencia_tipo', 'meta')
                    ->where('referencia_id', $meta->_id)
                    ->where('tipo', 'limite')
                    ->where('data_alerta', '>=', Carbon::now()->startOfMonth())
                    ->first();

                if (!$alertaExistente) {
                    Alerta::create([
                        'titulo' => 'Limite Ultrapassado',
                        'mensagem' => "A meta '{$meta->titulo}' ultrapassou o limite! Atual: R$ " . number_format($meta->valor_atual, 2, ',', '.'),
                        'tipo' => 'limite',
                        'data_alerta' => Carbon::now(),
                        'referencia_tipo' => 'meta',
                        'referencia_id' => $meta->_id,
                    ]);
                }
            }

            if ($meta->dias_restantes !== null && $meta->dias_restantes <= 7 && $meta->dias_restantes > 0) {
                $alertaExistente = Alerta::where('referencia_tipo', 'meta')
                    ->where('referencia_id', $meta->_id)
                    ->where('tipo', 'meta')
                    ->where('data_alerta', '>=', Carbon::now()->subDays(7))
                    ->first();

                if (!$alertaExistente) {
                    Alerta::create([
                        'titulo' => 'Meta Proxima do Prazo',
                        'mensagem' => "A meta '{$meta->titulo}' vence em {$meta->dias_restantes} dias!",
                        'tipo' => 'meta',
                        'data_alerta' => Carbon::now(),
                        'referencia_tipo' => 'meta',
                        'referencia_id' => $meta->_id,
                    ]);
                }
            }
        }
    }

    public function marcarAlertaLido(string $id)
    {
        $alerta = Alerta::findOrFail($id);
        $alerta->lido = true;
        $alerta->save();

        return redirect()->back()
            ->with('success', 'Alerta marcado como lido!');
    }

    public function marcarTodosAlertasLidos()
    {
        Alerta::where('lido', false)->update(['lido' => true]);

        return redirect()->back()
            ->with('success', 'Todos os alertas foram marcados como lidos!');
    }

    public function destroyAlerta(string $id)
    {
        $alerta = Alerta::findOrFail($id);
        $alerta->delete();

        return redirect()->back()
            ->with('success', 'Alerta removido com sucesso!');
    }

    // ==================== API PARA DASHBOARD ====================

    public function getAlertasNaoLidos()
    {
        $alertas = Alerta::naoLidos()->orderBy('data_alerta', 'desc')->take(5)->get();
        $count = Alerta::naoLidos()->count();

        return response()->json([
            'alertas' => $alertas,
            'count' => $count,
        ]);
    }

    public function getCategoriasJson()
    {
        $categorias = Categoria::ativas()->orderBy('nome')->get();
        return response()->json($categorias);
    }

    // ==================== LOGS DE AUDITORIA ====================

    public function logs(Request $request)
    {
        $query = AuditLog::orderBy('created_at', 'desc');

        // Filtros
        if ($request->filled('model')) {
            $query->where('model_type', 'like', '%' . $request->input('model') . '%');
        }
        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }
        if ($request->filled('data_inicio')) {
            $query->where('created_at', '>=', Carbon::parse($request->input('data_inicio'))->startOfDay());
        }
        if ($request->filled('data_fim')) {
            $query->where('created_at', '<=', Carbon::parse($request->input('data_fim'))->endOfDay());
        }

        // Paginacao manual para MongoDB
        $perPage = 25;
        $page = $request->input('page', 1);
        $allLogs = $query->get();
        $total = $allLogs->count();
        $logs = new LengthAwarePaginator(
            $allLogs->forPage($page, $perPage),
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Estatisticas
        $estatisticas = [
            'total' => AuditLog::count(),
            'hoje' => AuditLog::where('created_at', '>=', Carbon::today())->count(),
            'creates' => AuditLog::where('action', 'create')->count(),
            'updates' => AuditLog::where('action', 'update')->count(),
            'deletes' => AuditLog::where('action', 'delete')->count(),
        ];

        return view('configuracoes.logs', compact('logs', 'estatisticas'));
    }

    public function showLog(string $id)
    {
        $log = AuditLog::findOrFail($id);
        return response()->json($log);
    }

    public function limparLogs(Request $request)
    {
        $diasManter = $request->input('dias', 30);
        $dataLimite = Carbon::now()->subDays($diasManter);

        $count = AuditLog::where('created_at', '<', $dataLimite)->delete();

        return redirect()->route('logs.index')
            ->with('success', "{$count} logs antigos foram removidos!");
    }
}
