<?php

namespace App\Services;

use App\Models\Despesa;
use App\Models\Receita;
use Carbon\Carbon;

class ImportacaoCsvService
{
    /**
     * Formato esperado: Data;Tipo;Descricao;Valor;Categoria
     * (o mesmo formato gerado por ExportController::transacoesCsv)
     *
     * @return array{data: Carbon, tipo: string, descricao: string, valor: float, categoria: ?string}|null
     */
    public static function parsearLinha(string $linha): ?array
    {
        $campos = str_getcsv(trim($linha), ';');

        if (count($campos) < 5) {
            return null;
        }

        [$dataStr, $tipoStr, $descricao, $valorStr, $categoria] = $campos;

        try {
            $data = Carbon::createFromFormat('d/m/Y', trim($dataStr))->startOfDay();
        } catch (\Throwable $e) {
            return null;
        }

        if (! $data || $data->format('d/m/Y') !== trim($dataStr)) {
            return null;
        }

        $tipo = strtolower(trim($tipoStr));
        if (! in_array($tipo, ['receita', 'despesa'], true)) {
            return null;
        }

        $descricao = trim($descricao);
        if ($descricao === '') {
            return null;
        }

        $valorNormalizado = str_replace(['.', ','], ['', '.'], trim($valorStr));
        if (! is_numeric($valorNormalizado)) {
            return null;
        }

        $categoria = trim($categoria);

        return [
            'data' => $data,
            'tipo' => $tipo,
            'descricao' => $descricao,
            'valor' => (float) $valorNormalizado,
            'categoria' => $categoria !== '' ? $categoria : null,
        ];
    }

    /**
     * @return array{linhas: array<int, array>, erros: int}
     */
    public static function parsear(string $conteudo): array
    {
        $todasLinhas = preg_split('/\r\n|\r|\n/', trim($conteudo));
        $linhasDados = array_slice($todasLinhas, 1); // ignora cabecalho

        $linhas = [];
        $erros = 0;

        foreach ($linhasDados as $linha) {
            if (trim($linha) === '') {
                continue;
            }

            $parsed = self::parsearLinha($linha);

            if ($parsed) {
                $linhas[] = $parsed;
            } else {
                $erros++;
            }
        }

        return ['linhas' => $linhas, 'erros' => $erros];
    }

    /**
     * Cria as receitas/despesas a partir das linhas ja validadas.
     *
     * @param  array<int, array>  $linhas
     */
    public function importar(array $linhas): int
    {
        $criadas = 0;

        foreach ($linhas as $linha) {
            $dados = [
                'descricao' => $linha['descricao'],
                'valor' => $linha['valor'],
                'data' => $linha['data'],
                'categoria' => $linha['categoria'],
                'ativo' => true,
            ];

            if ($linha['tipo'] === 'receita') {
                $dados['recorrente'] = false;
                Receita::create($dados);
            } else {
                $dados['recorrente'] = false;
                $dados['parcelado'] = false;
                Despesa::create($dados);
            }

            $criadas++;
        }

        return $criadas;
    }
}
