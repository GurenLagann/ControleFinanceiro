<?php

namespace App\Http\Controllers;

use App\Services\CacheService;
use App\Services\ImportacaoCsvService;
use Illuminate\Http\Request;

class ImportacaoController extends Controller
{
    public function index()
    {
        return view('importacao.index');
    }

    public function preview(Request $request)
    {
        $request->validate([
            'arquivo' => 'required|file|max:5120',
        ]);

        $conteudo = file_get_contents($request->file('arquivo')->getRealPath());
        $resultado = ImportacaoCsvService::parsear($conteudo);

        $request->session()->put('importacao.linhas', $resultado['linhas']);

        return view('importacao.preview', [
            'linhas' => $resultado['linhas'],
            'erros' => $resultado['erros'],
        ]);
    }

    public function confirmar(Request $request)
    {
        $linhas = $request->session()->get('importacao.linhas', []);

        if (empty($linhas)) {
            return redirect()->route('importacao.index')
                ->with('error', 'Nenhuma linha para importar. Envie o arquivo novamente.');
        }

        $criadas = (new ImportacaoCsvService())->importar($linhas);

        $request->session()->forget('importacao.linhas');
        CacheService::clearReceitas();
        CacheService::clearDespesas();

        return redirect()->route('financas.index')
            ->with('success', "{$criadas} lançamento(s) importado(s) com sucesso!");
    }

    public function cancelar(Request $request)
    {
        $request->session()->forget('importacao.linhas');

        return redirect()->route('importacao.index');
    }
}
