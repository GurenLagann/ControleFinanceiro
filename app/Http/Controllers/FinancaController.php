<?php

namespace App\Http\Controllers;

use App\Models\Receita;
use App\Models\Despesa;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;
use Illuminate\Support\Str;

class FinancaController extends Controller
{
    public function transacoes(Request $request)
    {
        $receitas = CacheService::getReceitas();
        $despesas = CacheService::getDespesas();

        $transacoes = collect();

        foreach ($receitas as $receita) {
            $transacoes->push((object) [
                '_id' => $receita->_id,
                'tipo' => 'receita',
                'descricao' => $receita->descricao,
                'valor' => $receita->valor,
                'data' => $receita->data,
                'categoria' => $receita->categoria,
                'recorrente' => $receita->recorrente ?? false,
                'parcelado' => false,
                'parcela_atual' => null,
                'total_parcelas' => null,
            ]);
        }

        foreach ($despesas as $despesa) {
            $transacoes->push((object) [
                '_id' => $despesa->_id,
                'tipo' => 'despesa',
                'descricao' => $despesa->descricao,
                'valor' => $despesa->valor,
                'data' => $despesa->data,
                'categoria' => $despesa->categoria,
                'recorrente' => $despesa->recorrente ?? false,
                'parcelado' => $despesa->parcelado ?? false,
                'parcela_atual' => $despesa->parcela_atual,
                'total_parcelas' => $despesa->total_parcelas,
            ]);
        }

        $transacoes = $transacoes->sortByDesc(function($item) {
            if (!$item->data) return 0;
            return $item->data instanceof \Carbon\Carbon ? $item->data->timestamp : strtotime($item->data);
        })->values();

        // Paginacao
        $perPage = 20;
        $page = $request->input('page', 1);
        $total = $transacoes->count();
        $transacoesPaginadas = new LengthAwarePaginator(
            $transacoes->forPage($page, $perPage),
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('financas.transacoes', [
            'transacoes' => $transacoesPaginadas,
            'totalTransacoes' => $total,
        ]);
    }

    public function updateReceita(Request $request, string $id)
    {
        $receita = Receita::findOrFail($id);

        $validated = $request->validate([
            'descricao' => 'required|string|max:255',
            'valor' => 'required|numeric|min:0.01',
            'data' => 'required|date',
            'categoria' => 'nullable|string|max:100',
        ]);

        $receita->update($validated);
        CacheService::clearReceitas();

        return redirect()->back()
            ->with('success', 'Receita atualizada com sucesso!');
    }

    public function index()
    {
        // Usando cache para melhor performance
        $receitas = CacheService::getReceitas();
        $despesas = CacheService::getDespesas();
        $receitasRecorrentes = CacheService::getReceitasRecorrentes();
        $despesasRecorrentes = CacheService::getDespesasRecorrentes();
        $despesasParceladas = CacheService::getDespesasParceladas();

        $totalReceitas = $receitas->sum('valor');
        $totalDespesas = $despesas->sum('valor');
        $saldo = $totalReceitas - $totalDespesas;

        // Totais do mês atual
        $mesAtual = Carbon::now()->format('Y-m');
        $totalDespesasMesAtual = $despesas->filter(function($item) use ($mesAtual) {
            return $item->data && $item->data->format('Y-m') === $mesAtual;
        })->sum('valor');
        $totalReceitasMesAtual = $receitas->filter(function($item) use ($mesAtual) {
            return $item->data && $item->data->format('Y-m') === $mesAtual;
        })->sum('valor');
        $saldoMesAtual = $totalReceitasMesAtual - $totalDespesasMesAtual;

        // Calcular valores futuros (proximo mes)
        $previsaoReceitas = $receitasRecorrentes->sum('valor');
        $previsaoDespesas = $despesasRecorrentes->sum('valor');
        
        // Adicionar parcelas futuras na previsao
        $proximoMes = Carbon::now()->addMonth();
        $parcelasFuturas = $despesas->filter(function($d) use ($proximoMes) {
            return $d->parcelado && $d->data && $d->data->format('Y-m') === $proximoMes->format('Y-m');
        })->sum('valor');
        $previsaoDespesas += $parcelasFuturas;
        
        $previsaoSaldo = $previsaoReceitas - $previsaoDespesas;

        // Dados para graficos por categoria
        $receitasPorCategoria = $receitas->groupBy(function($item) {
            return $item->categoria ?: 'Sem categoria';
        })->map(function($group) {
            return $group->sum('valor');
        })->toArray();

        $despesasPorCategoria = $despesas->groupBy(function($item) {
            return $item->categoria ?: 'Sem categoria';
        })->map(function($group) {
            return $group->sum('valor');
        })->toArray();

        // Dados para grafico de evolucao dos ultimos 7 dias
        $evolucaoDias = [];
        $evolucaoReceitas = [];
        $evolucaoDespesas = [];

        for ($i = 6; $i >= 0; $i--) {
            $data = Carbon::now()->subDays($i);
            $evolucaoDias[] = $data->format('d/m');
            
            $evolucaoReceitas[] = $receitas->filter(function($item) use ($data) {
                return $item->data && $item->data->format('Y-m-d') === $data->format('Y-m-d');
            })->sum('valor');
            
            $evolucaoDespesas[] = $despesas->filter(function($item) use ($data) {
                return $item->data && $item->data->format('Y-m-d') === $data->format('Y-m-d');
            })->sum('valor');
        }

        // Despesas agrupadas por mês (para exibição de totais)
        $despesasPorMes = $despesas->groupBy(function($item) {
            return $item->data ? $item->data->format('Y-m') : 'sem-data';
        })->map(function($group, $key) {
            $mesAno = $key !== 'sem-data' ? Carbon::parse($key . '-01') : null;
            return [
                'mes' => $mesAno ? $mesAno->translatedFormat('F/Y') : 'Sem data',
                'total' => $group->sum('valor'),
                'quantidade' => $group->count(),
            ];
        })->sortKeysDesc()->values()->toArray();

        // Projecao para os proximos 6 meses
        $projecaoMeses = [];
        $projecaoReceitasMensal = [];
        $projecaoDespesasMensal = [];
        $projecaoSaldoMensal = [];

        for ($i = 0; $i < 6; $i++) {
            $mes = Carbon::now()->startOfMonth()->addMonths($i);
            $projecaoMeses[] = $mes->translatedFormat('M/Y');
            
            // Receitas do mes (reais + recorrentes para projeção)
            $receitasReaisMes = $receitas->filter(function($item) use ($mes) {
                return $item->data && $item->data->format('Y-m') === $mes->format('Y-m') && !$item->recorrente;
            })->sum('valor');

            // Sempre incluir receitas recorrentes na projeção
            $receitasMes = $receitasReaisMes + $receitasRecorrentes->sum('valor');
            
            // Despesas do mes (reais não recorrentes + parcelas + recorrentes para projeção)
            $despesasReaisMes = $despesas->filter(function($item) use ($mes) {
                return $item->data && $item->data->format('Y-m') === $mes->format('Y-m') && !$item->recorrente;
            })->sum('valor');

            // Sempre incluir despesas recorrentes na projeção
            $despesasMes = $despesasReaisMes + $despesasRecorrentes->sum('valor');
            
            $projecaoReceitasMensal[] = $receitasMes;
            $projecaoDespesasMensal[] = $despesasMes;
            $projecaoSaldoMensal[] = $receitasMes - $despesasMes;
        }

        // Comparativo Mensal (mes atual vs mes anterior)
        $mesAtualCarbon = Carbon::now()->startOfMonth();
        $mesAnteriorCarbon = Carbon::now()->subMonth()->startOfMonth();

        $comparativoMesAtual = [
            'receitas' => $receitas->filter(fn($r) => $r->data && $r->data->format('Y-m') === $mesAtualCarbon->format('Y-m'))->sum('valor'),
            'despesas' => $despesas->filter(fn($d) => $d->data && $d->data->format('Y-m') === $mesAtualCarbon->format('Y-m'))->sum('valor'),
        ];
        $comparativoMesAnterior = [
            'receitas' => $receitas->filter(fn($r) => $r->data && $r->data->format('Y-m') === $mesAnteriorCarbon->format('Y-m'))->sum('valor'),
            'despesas' => $despesas->filter(fn($d) => $d->data && $d->data->format('Y-m') === $mesAnteriorCarbon->format('Y-m'))->sum('valor'),
        ];
        $comparativoLabels = [$mesAnteriorCarbon->translatedFormat('M/Y'), $mesAtualCarbon->translatedFormat('M/Y')];

        // Tendencia Anual (ultimos 12 meses)
        $tendenciaMeses = [];
        $tendenciaReceitas = [];
        $tendenciaDespesas = [];
        $tendenciaSaldo = [];

        for ($i = 11; $i >= 0; $i--) {
            $mes = Carbon::now()->startOfMonth()->subMonths($i);
            $tendenciaMeses[] = $mes->translatedFormat('M/y');

            $recMes = $receitas->filter(fn($r) => $r->data && $r->data->format('Y-m') === $mes->format('Y-m'))->sum('valor');
            $desMes = $despesas->filter(fn($d) => $d->data && $d->data->format('Y-m') === $mes->format('Y-m'))->sum('valor');

            $tendenciaReceitas[] = $recMes;
            $tendenciaDespesas[] = $desMes;
            $tendenciaSaldo[] = $recMes - $desMes;
        }

        // Distribuicao de Gastos por Dia da Semana
        $diasSemana = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab'];
        $gastosPorDiaSemana = array_fill(0, 7, 0);

        foreach ($despesas as $despesa) {
            if ($despesa->data) {
                $diaSemana = $despesa->data->dayOfWeek; // 0 = domingo, 6 = sabado
                $gastosPorDiaSemana[$diaSemana] += $despesa->valor;
            }
        }

        // Categorias para autocomplete
        $categoriasReceita = Receita::whereNotNull('categoria')
            ->where('categoria', '!=', '')
            ->pluck('categoria')
            ->unique()
            ->filter()
            ->values()
            ->toArray();

        $categoriasDespesa = Despesa::whereNotNull('categoria')
            ->where('categoria', '!=', '')
            ->pluck('categoria')
            ->unique()
            ->filter()
            ->values()
            ->toArray();

        return view('financas.index', compact(
            'receitas',
            'despesas',
            'receitasRecorrentes',
            'despesasRecorrentes',
            'despesasParceladas',
            'totalReceitas',
            'totalDespesas',
            'totalDespesasMesAtual',
            'totalReceitasMesAtual',
            'saldoMesAtual',
            'saldo',
            'previsaoReceitas',
            'previsaoDespesas',
            'previsaoSaldo',
            'receitasPorCategoria',
            'despesasPorCategoria',
            'evolucaoDias',
            'evolucaoReceitas',
            'evolucaoDespesas',
            'projecaoMeses',
            'projecaoReceitasMensal',
            'projecaoDespesasMensal',
            'projecaoSaldoMensal',
            'despesasPorMes',
            'comparativoMesAtual',
            'comparativoMesAnterior',
            'comparativoLabels',
            'tendenciaMeses',
            'tendenciaReceitas',
            'tendenciaDespesas',
            'tendenciaSaldo',
            'diasSemana',
            'gastosPorDiaSemana',
            'categoriasReceita',
            'categoriasDespesa'
        ));
    }

    public function storeReceita(Request $request)
    {
        $validated = $request->validate([
            'descricao' => 'required|string|max:255',
            'valor' => 'required|numeric|min:0.01',
            'data' => 'required|date',
            'categoria' => 'nullable|string|max:100',
            'recorrente' => 'nullable|boolean',
            'frequencia' => 'nullable|string|in:mensal,semanal,quinzenal,anual',
            'dia_vencimento' => 'nullable|integer|min:1|max:31',
        ]);

        $validated['recorrente'] = $request->has('recorrente');
        $validated['ativo'] = true;

        if ($validated['recorrente'] && empty($validated['dia_vencimento'])) {
            $validated['dia_vencimento'] = Carbon::parse($validated['data'])->day;
        }

        Receita::create($validated);
        CacheService::clearReceitas();

        return redirect()->route('financas.index')
            ->with('success', 'Receita adicionada com sucesso!');
    }

    public function storeDespesa(Request $request)
    {
        $validated = $request->validate([
            'descricao' => 'required|string|max:255',
            'valor' => 'required|numeric|min:0.01',
            'data' => 'required|date',
            'categoria' => 'nullable|string|max:100',
            'recorrente' => 'nullable|boolean',
            'frequencia' => 'nullable|string|in:mensal,semanal,quinzenal,anual',
            'dia_vencimento' => 'nullable|integer|min:1|max:31',
            'parcelado' => 'nullable|boolean',
            'total_parcelas' => 'nullable|integer|min:2|max:48',
        ]);

        $validated['recorrente'] = $request->input('recorrente') == '1';
        $validated['parcelado'] = $request->input('parcelado') == '1';
        $validated['ativo'] = true;

        // Se for parcelado, criar multiplas despesas
        if ($validated['parcelado'] && !empty($validated['total_parcelas'])) {
            $valorTotal = $validated['valor'];
            $totalParcelas = $validated['total_parcelas'];
            $valorParcela = round($valorTotal / $totalParcelas, 2);
            $dataInicial = Carbon::parse($validated['data']);
            $grupoParcela = Str::uuid()->toString();

            for ($i = 1; $i <= $totalParcelas; $i++) {
                $dataParcela = $dataInicial->copy()->addMonths($i - 1);
                
                // Ajustar valor da ultima parcela para compensar arredondamento
                $valorAtual = $valorParcela;
                if ($i === $totalParcelas) {
                    $valorAtual = $valorTotal - ($valorParcela * ($totalParcelas - 1));
                }

                Despesa::create([
                    'descricao' => $validated['descricao'],
                    'valor' => $valorAtual,
                    'valor_total' => $valorTotal,
                    'data' => $dataParcela,
                    'categoria' => $validated['categoria'] ?? null,
                    'parcelado' => true,
                    'parcela_atual' => $i,
                    'total_parcelas' => $totalParcelas,
                    'grupo_parcela_id' => $grupoParcela,
                    'recorrente' => false,
                    'ativo' => true,
                ]);
            }

            CacheService::clearDespesas();
            return redirect()->route('financas.index')
                ->with('success', "Despesa parcelada em {$totalParcelas}x de R$ " . number_format($valorParcela, 2, ',', '.') . " criada com sucesso!");
        }

        // Despesa normal ou recorrente
        if ($validated['recorrente'] && empty($validated['dia_vencimento'])) {
            $validated['dia_vencimento'] = Carbon::parse($validated['data'])->day;
        }

        Despesa::create($validated);
        CacheService::clearDespesas();

        return redirect()->route('financas.index')
            ->with('success', 'Despesa adicionada com sucesso!');
    }

    public function storeMultiplasDespesas(Request $request)
    {
        $request->validate([
            'despesas' => 'required|array|min:1',
            'despesas.*.descricao' => 'required|string|max:255',
            'despesas.*.valor' => 'required|numeric|min:0.01',
            'despesas.*.categoria' => 'nullable|string|max:100',
        ]);

        $data = $request->input('data') ?: now()->format('Y-m-d');
        $count = 0;

        foreach ($request->input('despesas') as $despesa) {
            if (!empty($despesa['descricao']) && !empty($despesa['valor'])) {
                Despesa::create([
                    'descricao' => $despesa['descricao'],
                    'valor' => $despesa['valor'],
                    'data' => $data,
                    'categoria' => $despesa['categoria'] ?? null,
                    'recorrente' => false,
                    'parcelado' => false,
                    'ativo' => true,
                ]);
                $count++;
            }
        }

        CacheService::clearDespesas();
        return redirect()->route('financas.index')
            ->with('success', "{$count} despesas adicionadas com sucesso!");
    }

    public function destroyReceita(string $id)
    {
        $receita = Receita::findOrFail($id);
        $receita->delete();
        CacheService::clearReceitas();

        return redirect()->route('financas.index')
            ->with('success', 'Receita removida com sucesso!');
    }

    public function updateDespesa(Request $request, string $id)
    {
        $despesa = Despesa::findOrFail($id);

        $validated = $request->validate([
            'descricao' => 'required|string|max:255',
            'valor' => 'required|numeric|min:0.01',
            'data' => 'required|date',
            'categoria' => 'nullable|string|max:100',
        ]);

        $despesa->update($validated);
        CacheService::clearDespesas();

        return redirect()->back()
            ->with('success', 'Despesa atualizada com sucesso!');
    }

    public function avancarParcela(string $id)
    {
        $despesa = Despesa::findOrFail($id);

        if (!$despesa->parcelado) {
            return redirect()->route('financas.index')
                ->with('error', 'Esta despesa não é parcelada.');
        }

        // Encontrar a próxima parcela não paga do mesmo grupo
        $proximaParcela = Despesa::where('grupo_parcela_id', $despesa->grupo_parcela_id)
            ->where('data', '>', now())
            ->orderBy('data', 'asc')
            ->first();

        if ($proximaParcela) {
            // Marcar como paga alterando a data para hoje
            $proximaParcela->data = now();
            $proximaParcela->save();
            CacheService::clearDespesas();

            return redirect()->route('financas.index')
                ->with('success', "Parcela {$proximaParcela->parcela_atual}/{$proximaParcela->total_parcelas} marcada como paga!");
        }

        return redirect()->route('financas.index')
            ->with('success', 'Todas as parcelas já foram pagas!');
    }

    public function adiantarParcelas(Request $request, string $grupoId)
    {
        $quantidade = (int) $request->input('quantidade', 1);

        // Buscar parcelas futuras
        $parcelasFuturas = Despesa::where('grupo_parcela_id', $grupoId)
            ->where('data', '>', now())
            ->orderBy('data', 'asc')
            ->take($quantidade)
            ->get();

        if ($parcelasFuturas->isEmpty()) {
            return redirect()->route('financas.index')
                ->with('success', 'Não há parcelas futuras para adiantar.');
        }

        $valorTotal = 0;
        foreach ($parcelasFuturas as $parcela) {
            $parcela->data = now();
            $parcela->save();
            $valorTotal += $parcela->valor;
        }
        CacheService::clearDespesas();

        $count = $parcelasFuturas->count();
        return redirect()->route('financas.index')
            ->with('success', "{$count} parcela(s) adiantada(s) - Total: R$ " . number_format($valorTotal, 2, ',', '.'));
    }

    public function destroyDespesa(string $id)
    {
        $despesa = Despesa::findOrFail($id);
        $despesa->delete();
        CacheService::clearDespesas();

        return redirect()->route('financas.index')
            ->with('success', 'Despesa removida com sucesso!');
    }

    public function destroyDespesaGrupo(string $grupoId)
    {
        $count = Despesa::where('grupo_parcela_id', $grupoId)->delete();
        CacheService::clearDespesas();

        return redirect()->route('financas.index')
            ->with('success', "{$count} parcelas removidas com sucesso!");
    }

    public function toggleRecorrenteReceita(string $id)
    {
        $receita = Receita::findOrFail($id);
        $receita->ativo = !$receita->ativo;
        $receita->save();
        CacheService::clearReceitas();

        $status = $receita->ativo ? 'ativada' : 'desativada';
        return redirect()->route('financas.index')
            ->with('success', "Receita recorrente {$status} com sucesso!");
    }

    public function toggleRecorrenteDespesa(string $id)
    {
        $despesa = Despesa::findOrFail($id);
        $despesa->ativo = !$despesa->ativo;
        $despesa->save();
        CacheService::clearDespesas();

        $status = $despesa->ativo ? 'ativada' : 'desativada';
        return redirect()->route('financas.index')
            ->with('success', "Despesa recorrente {$status} com sucesso!");
    }
}
