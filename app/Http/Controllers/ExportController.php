<?php

namespace App\Http\Controllers;

use App\Models\Receita;
use App\Models\Despesa;
use App\Models\Categoria;
use App\Models\Meta;
use App\Models\Alerta;
use App\Services\BackupService;
use App\Services\CacheService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Carbon\Carbon;

class ExportController extends Controller
{
    /**
     * Relatorio PDF do mes
     */
    public function relatorioPdf(Request $request)
    {
        $mes = $request->input('mes', now()->format('Y-m'));
        $dados = $this->getDadosPeriodo($mes);

        $pdf = Pdf::loadView('exports.relatorio-pdf', $dados);
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download("relatorio-{$mes}.pdf");
    }

    /**
     * Extrato PDF de todas as transacoes
     */
    public function extratoPdf(Request $request)
    {
        $dataInicio = $request->input('inicio', now()->startOfMonth()->format('Y-m-d'));
        $dataFim = $request->input('fim', now()->endOfMonth()->format('Y-m-d'));

        $receitas = Receita::whereBetween('data', [$dataInicio, $dataFim])
            ->orderBy('data', 'desc')
            ->get();

        $despesas = Despesa::whereBetween('data', [$dataInicio, $dataFim])
            ->orderBy('data', 'desc')
            ->get();

        $transacoes = collect();

        foreach ($receitas as $receita) {
            $transacoes->push((object) [
                'tipo' => 'Receita',
                'descricao' => $receita->descricao,
                'valor' => $receita->valor,
                'data' => $receita->data,
                'categoria' => $receita->categoria,
            ]);
        }

        foreach ($despesas as $despesa) {
            $transacoes->push((object) [
                'tipo' => 'Despesa',
                'descricao' => $despesa->descricao_completa ?? $despesa->descricao,
                'valor' => $despesa->valor,
                'data' => $despesa->data,
                'categoria' => $despesa->categoria,
            ]);
        }

        $transacoes = $transacoes->sortByDesc('data')->values();

        $pdf = Pdf::loadView('exports.extrato-pdf', [
            'transacoes' => $transacoes,
            'dataInicio' => Carbon::parse($dataInicio),
            'dataFim' => Carbon::parse($dataFim),
            'totalReceitas' => $receitas->sum('valor'),
            'totalDespesas' => $despesas->sum('valor'),
        ]);
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download("extrato-{$dataInicio}-{$dataFim}.pdf");
    }

    /**
     * CSV de receitas
     */
    public function receitasCsv()
    {
        $receitas = Receita::orderBy('data', 'desc')->get();

        $csv = "Data;Descricao;Valor;Categoria;Recorrente\n";

        foreach ($receitas as $r) {
            $data = $r->data ? $r->data->format('d/m/Y') : '-';
            $valor = number_format($r->valor, 2, ',', '.');
            $recorrente = $r->recorrente ? 'Sim' : 'Nao';
            $csv .= "{$data};\"{$r->descricao}\";{$valor};\"{$r->categoria}\";{$recorrente}\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="receitas.csv"');
    }

    /**
     * CSV de despesas
     */
    public function despesasCsv()
    {
        $despesas = Despesa::orderBy('data', 'desc')->get();

        $csv = "Data;Descricao;Valor;Categoria;Recorrente;Parcelado;Parcela\n";

        foreach ($despesas as $d) {
            $data = $d->data ? $d->data->format('d/m/Y') : '-';
            $valor = number_format($d->valor, 2, ',', '.');
            $recorrente = $d->recorrente ? 'Sim' : 'Nao';
            $parcelado = $d->parcelado ? 'Sim' : 'Nao';
            $parcela = $d->parcelado ? "{$d->parcela_atual}/{$d->total_parcelas}" : '-';
            $csv .= "{$data};\"{$d->descricao}\";{$valor};\"{$d->categoria}\";{$recorrente};{$parcelado};{$parcela}\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="despesas.csv"');
    }

    /**
     * CSV de todas as transacoes
     */
    public function transacoesCsv()
    {
        $receitas = Receita::orderBy('data', 'desc')->get();
        $despesas = Despesa::orderBy('data', 'desc')->get();

        $csv = "Data;Tipo;Descricao;Valor;Categoria\n";

        $transacoes = collect();

        foreach ($receitas as $r) {
            $transacoes->push([
                'data' => $r->data,
                'tipo' => 'Receita',
                'descricao' => $r->descricao,
                'valor' => $r->valor,
                'categoria' => $r->categoria,
            ]);
        }

        foreach ($despesas as $d) {
            $transacoes->push([
                'data' => $d->data,
                'tipo' => 'Despesa',
                'descricao' => $d->descricao_completa ?? $d->descricao,
                'valor' => $d->valor,
                'categoria' => $d->categoria,
            ]);
        }

        $transacoes = $transacoes->sortByDesc('data');

        foreach ($transacoes as $t) {
            $data = $t['data'] ? $t['data']->format('d/m/Y') : '-';
            $valor = number_format($t['valor'], 2, ',', '.');
            $csv .= "{$data};{$t['tipo']};\"{$t['descricao']}\";{$valor};\"{$t['categoria']}\"\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="transacoes.csv"');
    }

    /**
     * Pagina de backup
     */
    public function backupIndex()
    {
        $estatisticas = [
            'receitas' => Receita::count(),
            'despesas' => Despesa::count(),
            'categorias' => Categoria::count(),
            'metas' => Meta::count(),
            'alertas' => Alerta::count(),
        ];

        $totalRegistros = array_sum($estatisticas);

        return view('exports.backup', compact('estatisticas', 'totalRegistros'));
    }

    /**
     * Exportar backup JSON
     */
    public function exportarBackup()
    {
        $backup = BackupService::export();
        $json = json_encode($backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $filename = 'backup-financas-' . now()->format('Y-m-d-His') . '.json';

        return response($json)
            ->header('Content-Type', 'application/json')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Importar backup
     */
    public function importarBackup(Request $request)
    {
        $request->validate([
            'backup' => 'required|file|max:10240', // Max 10MB
            'modo' => 'required|in:substituir,mesclar',
        ]);

        $file = $request->file('backup');
        $modo = $request->input('modo');

        // Validar arquivo primeiro
        $validacao = BackupService::validarBackup($file);
        if (!$validacao['valido']) {
            return redirect()->back()
                ->with('error', $validacao['erro']);
        }

        // Importar
        $resultado = BackupService::import($file, $modo);

        if ($resultado['success']) {
            return redirect()->back()
                ->with('success', $resultado['message']);
        }

        return redirect()->back()
            ->with('error', $resultado['message']);
    }

    /**
     * Obter dados do periodo para relatorio
     */
    private function getDadosPeriodo(string $mes): array
    {
        $inicioMes = Carbon::parse($mes . '-01')->startOfMonth();
        $fimMes = Carbon::parse($mes . '-01')->endOfMonth();

        $receitas = Receita::whereBetween('data', [$inicioMes, $fimMes])->get();
        $despesas = Despesa::whereBetween('data', [$inicioMes, $fimMes])->get();

        $totalReceitas = $receitas->sum('valor');
        $totalDespesas = $despesas->sum('valor');
        $saldo = $totalReceitas - $totalDespesas;

        // Agrupar por categoria
        $receitasPorCategoria = $receitas->groupBy('categoria')
            ->map(fn($g) => $g->sum('valor'))
            ->sortDesc();

        $despesasPorCategoria = $despesas->groupBy('categoria')
            ->map(fn($g) => $g->sum('valor'))
            ->sortDesc();

        // Transacoes ordenadas
        $transacoes = collect();

        foreach ($receitas as $r) {
            $transacoes->push((object) [
                'tipo' => 'Receita',
                'descricao' => $r->descricao,
                'valor' => $r->valor,
                'data' => $r->data,
                'categoria' => $r->categoria,
            ]);
        }

        foreach ($despesas as $d) {
            $transacoes->push((object) [
                'tipo' => 'Despesa',
                'descricao' => $d->descricao_completa ?? $d->descricao,
                'valor' => $d->valor,
                'data' => $d->data,
                'categoria' => $d->categoria,
            ]);
        }

        $transacoes = $transacoes->sortByDesc('data')->values();

        return [
            'periodo' => $inicioMes->translatedFormat('F \d\e Y'),
            'mes' => $mes,
            'totalReceitas' => $totalReceitas,
            'totalDespesas' => $totalDespesas,
            'saldo' => $saldo,
            'receitasPorCategoria' => $receitasPorCategoria,
            'despesasPorCategoria' => $despesasPorCategoria,
            'transacoes' => $transacoes,
            'qtdReceitas' => $receitas->count(),
            'qtdDespesas' => $despesas->count(),
        ];
    }
}
