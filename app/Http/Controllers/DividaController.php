<?php

namespace App\Http\Controllers;

use App\Models\Divida;
use App\Models\Despesa;
use App\Models\Categoria;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Str;

class DividaController extends Controller
{
    public function index()
    {
        $dividas = Divida::orderBy('created_at', 'desc')->get();

        $dividas->each(function ($divida) {
            $pagamentos = $divida->pagamentos ?? [];
            $divida->valor_pago_calc = collect($pagamentos)->sum('valor');
            $divida->valor_restante_calc = max(0, $divida->valor_total - $divida->valor_pago_calc);
            $divida->percentual_calc = $divida->valor_total > 0
                ? min(100, round(($divida->valor_pago_calc / $divida->valor_total) * 100, 1))
                : 0;
        });

        $totalDividas = $dividas->count();
        $totalDevido = $dividas->sum('valor_total');
        $totalPago = $dividas->sum('valor_pago_calc');
        $totalEmAtraso = $dividas->where('status', 'em_atraso')->sum('valor_total');

        $categorias = Categoria::ativas()->paraDespesas()->get();

        return view('dividas.index', compact(
            'dividas',
            'categorias',
            'totalDividas',
            'totalDevido',
            'totalPago',
            'totalEmAtraso'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'descricao'       => 'required|string|max:255',
            'credor'          => 'nullable|string|max:255',
            'valor_total'     => 'required|numeric|min:0.01',
            'data_inicio'     => 'required|date',
            'data_vencimento' => 'nullable|date',
            'categoria'       => 'nullable|string|max:100',
            'observacoes'     => 'nullable|string|max:500',
        ]);

        $validated['status'] = 'ativa';
        $validated['pagamentos'] = [];

        Divida::create($validated);

        return redirect()->route('dividas.index')
            ->with('success', 'Dívida cadastrada com sucesso!');
    }

    public function update(Request $request, string $id)
    {
        $divida = Divida::findOrFail($id);

        $validated = $request->validate([
            'descricao'       => 'required|string|max:255',
            'credor'          => 'nullable|string|max:255',
            'valor_total'     => 'required|numeric|min:0.01',
            'data_inicio'     => 'required|date',
            'data_vencimento' => 'nullable|date',
            'categoria'       => 'nullable|string|max:100',
            'observacoes'     => 'nullable|string|max:500',
        ]);

        // Recalculate status after editing
        $valorPago = collect($divida->pagamentos ?? [])->sum('valor');
        if ($valorPago >= (float) $validated['valor_total']) {
            $validated['status'] = 'quitada';
        } elseif (!empty($validated['data_vencimento']) && Carbon::parse($validated['data_vencimento'])->isPast()) {
            $validated['status'] = 'em_atraso';
        } else {
            $validated['status'] = 'ativa';
        }

        $divida->update($validated);

        return redirect()->route('dividas.index')
            ->with('success', 'Dívida atualizada com sucesso!');
    }

    public function destroy(string $id)
    {
        $divida = Divida::findOrFail($id);

        // Delete all related despesas
        foreach ($divida->pagamentos ?? [] as $pagamento) {
            if (!empty($pagamento['despesa_id'])) {
                $despesa = Despesa::find($pagamento['despesa_id']);
                if ($despesa) {
                    $despesa->delete();
                }
            }
        }

        CacheService::clearDespesas();
        $divida->delete();

        return redirect()->route('dividas.index')
            ->with('success', 'Dívida excluída com sucesso!');
    }

    public function storePagamento(Request $request, string $id)
    {
        $divida = Divida::findOrFail($id);

        $validated = $request->validate([
            'valor'    => 'required|numeric|min:0.01',
            'data'     => 'required|date',
            'descricao' => 'nullable|string|max:255',
        ]);

        $descricaoPagamento = 'Pagamento: ' . $divida->descricao;
        if (!empty($validated['descricao'])) {
            $descricaoPagamento .= ' - ' . $validated['descricao'];
        }

        // Auto-create a despesa
        $despesa = Despesa::create([
            'descricao'   => $descricaoPagamento,
            'valor'       => (float) $validated['valor'],
            'data'        => $validated['data'],
            'categoria'   => $divida->categoria,
            'recorrente'  => false,
            'parcelado'   => false,
            'ativo'       => true,
        ]);

        CacheService::clearDespesas();

        // Add payment to divida
        $pagamentos = $divida->pagamentos ?? [];
        $pagamentos[] = [
            'id'         => Str::uuid()->toString(),
            'valor'      => (float) $validated['valor'],
            'data'       => $validated['data'],
            'descricao'  => $validated['descricao'] ?? '',
            'despesa_id' => (string) $despesa->_id,
        ];

        $totalPago = collect($pagamentos)->sum('valor');

        if ($totalPago >= $divida->valor_total) {
            $status = 'quitada';
        } elseif ($divida->data_vencimento && Carbon::parse($divida->data_vencimento)->isPast()) {
            $status = 'em_atraso';
        } else {
            $status = 'ativa';
        }

        $divida->update([
            'pagamentos' => $pagamentos,
            'status'     => $status,
        ]);

        return redirect()->route('dividas.index')
            ->with('success', 'Pagamento registrado! Uma despesa foi criada automaticamente.');
    }

    public function destroyPagamento(string $id, string $pagamentoId)
    {
        $divida = Divida::findOrFail($id);

        $pagamentos = $divida->pagamentos ?? [];
        $pagamento = collect($pagamentos)->firstWhere('id', $pagamentoId);

        if ($pagamento && !empty($pagamento['despesa_id'])) {
            $despesa = Despesa::find($pagamento['despesa_id']);
            if ($despesa) {
                $despesa->delete();
                CacheService::clearDespesas();
            }
        }

        $novosPagamentos = collect($pagamentos)
            ->filter(fn($p) => ($p['id'] ?? '') !== $pagamentoId)
            ->values()
            ->toArray();

        $totalPago = collect($novosPagamentos)->sum('valor');

        if ($totalPago >= $divida->valor_total) {
            $status = 'quitada';
        } elseif ($divida->data_vencimento && Carbon::parse($divida->data_vencimento)->isPast()) {
            $status = 'em_atraso';
        } else {
            $status = 'ativa';
        }

        $divida->update([
            'pagamentos' => $novosPagamentos,
            'status'     => $status,
        ]);

        return redirect()->route('dividas.index')
            ->with('success', 'Pagamento removido com sucesso!');
    }
}
