<?php

namespace App\Http\Controllers;

use App\Models\Cartao;
use App\Services\FaturaService;
use Illuminate\Http\Request;

class CartaoController extends Controller
{
    public function index()
    {
        $cartoes = Cartao::orderBy('nome')->get();
        $faturaService = new FaturaService();

        $faturas = $cartoes->map(function (Cartao $cartao) use ($faturaService) {
            $fatura = $faturaService->faturaAtual($cartao);

            return [
                'cartao' => [
                    'id' => (string) $cartao->_id,
                    'nome' => $cartao->nome,
                    'dia_fechamento' => $cartao->dia_fechamento,
                    'dia_vencimento' => $cartao->dia_vencimento,
                    'limite' => $cartao->limite,
                    'ativo' => $cartao->ativo,
                ],
                'ciclo' => $fatura['ciclo'],
                'total' => $fatura['total'],
            ];
        });

        return view('cartoes.index', compact('cartoes', 'faturas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:100',
            'dia_fechamento' => 'required|integer|min:1|max:31',
            'dia_vencimento' => 'required|integer|min:1|max:31',
            'limite' => 'nullable|numeric|min:0',
        ]);

        $validated['ativo'] = true;

        Cartao::create($validated);

        return redirect()->route('cartoes.index')
            ->with('success', 'Cartão cadastrado com sucesso!');
    }

    public function update(Request $request, string $id)
    {
        $cartao = Cartao::findOrFail($id);

        $validated = $request->validate([
            'nome' => 'required|string|max:100',
            'dia_fechamento' => 'required|integer|min:1|max:31',
            'dia_vencimento' => 'required|integer|min:1|max:31',
            'limite' => 'nullable|numeric|min:0',
        ]);

        $cartao->update($validated);

        return redirect()->route('cartoes.index')
            ->with('success', 'Cartão atualizado com sucesso!');
    }

    public function toggle(string $id)
    {
        $cartao = Cartao::findOrFail($id);
        $cartao->update(['ativo' => ! $cartao->ativo]);

        return redirect()->route('cartoes.index')
            ->with('success', 'Cartão atualizado com sucesso!');
    }

    public function destroy(string $id)
    {
        $cartao = Cartao::findOrFail($id);
        $cartao->delete();

        return redirect()->route('cartoes.index')
            ->with('success', 'Cartão removido com sucesso!');
    }
}
