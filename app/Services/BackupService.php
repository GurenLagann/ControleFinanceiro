<?php

namespace App\Services;

use App\Models\Receita;
use App\Models\Despesa;
use App\Models\Categoria;
use App\Models\Meta;
use App\Models\Alerta;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class BackupService
{
    const VERSAO_BACKUP = '1.0';

    /**
     * Exportar todos os dados para backup
     */
    public static function export(): array
    {
        return [
            'versao' => self::VERSAO_BACKUP,
            'data_exportacao' => now()->toISOString(),
            'estatisticas' => [
                'receitas' => Receita::count(),
                'despesas' => Despesa::count(),
                'categorias' => Categoria::count(),
                'metas' => Meta::count(),
                'alertas' => Alerta::count(),
            ],
            'dados' => [
                'receitas' => Receita::all()->map(fn($r) => self::prepararRegistro($r))->toArray(),
                'despesas' => Despesa::all()->map(fn($d) => self::prepararRegistro($d))->toArray(),
                'categorias' => Categoria::all()->map(fn($c) => self::prepararRegistro($c))->toArray(),
                'metas' => Meta::all()->map(fn($m) => self::prepararRegistro($m))->toArray(),
                'alertas' => Alerta::all()->map(fn($a) => self::prepararRegistro($a))->toArray(),
            ],
        ];
    }

    /**
     * Preparar registro para exportacao (remover campos internos)
     */
    private static function prepararRegistro($model): array
    {
        $dados = $model->toArray();

        // Converter datas para string ISO
        foreach (['data', 'data_inicio', 'data_fim', 'data_alerta', 'created_at', 'updated_at'] as $campo) {
            if (isset($dados[$campo]) && $dados[$campo] instanceof \Carbon\Carbon) {
                $dados[$campo] = $dados[$campo]->toISOString();
            }
        }

        return $dados;
    }

    /**
     * Importar dados de backup
     */
    public static function import(UploadedFile $file, string $modo = 'substituir'): array
    {
        $conteudo = file_get_contents($file->getRealPath());
        $backup = json_decode($conteudo, true);

        if (!$backup || !isset($backup['dados'])) {
            return [
                'success' => false,
                'message' => 'Arquivo de backup invalido.',
            ];
        }

        // Verificar versao
        $versao = $backup['versao'] ?? '0.0';
        if (version_compare($versao, '1.0', '<')) {
            return [
                'success' => false,
                'message' => 'Versao do backup incompativel.',
            ];
        }

        $resultado = [
            'success' => true,
            'message' => '',
            'importados' => [],
        ];

        try {
            // Se modo substituir, limpar dados existentes
            if ($modo === 'substituir') {
                Receita::truncate();
                Despesa::truncate();
                Categoria::truncate();
                Meta::truncate();
                Alerta::truncate();
            }

            // Importar categorias primeiro (podem ser referenciadas por outros)
            if (!empty($backup['dados']['categorias'])) {
                $count = self::importarColecao(Categoria::class, $backup['dados']['categorias'], $modo);
                $resultado['importados']['categorias'] = $count;
            }

            // Importar receitas
            if (!empty($backup['dados']['receitas'])) {
                $count = self::importarColecao(Receita::class, $backup['dados']['receitas'], $modo);
                $resultado['importados']['receitas'] = $count;
            }

            // Importar despesas
            if (!empty($backup['dados']['despesas'])) {
                $count = self::importarColecao(Despesa::class, $backup['dados']['despesas'], $modo);
                $resultado['importados']['despesas'] = $count;
            }

            // Importar metas
            if (!empty($backup['dados']['metas'])) {
                $count = self::importarColecao(Meta::class, $backup['dados']['metas'], $modo);
                $resultado['importados']['metas'] = $count;
            }

            // Importar alertas
            if (!empty($backup['dados']['alertas'])) {
                $count = self::importarColecao(Alerta::class, $backup['dados']['alertas'], $modo);
                $resultado['importados']['alertas'] = $count;
            }

            // Limpar cache
            CacheService::clearAll();

            $total = array_sum($resultado['importados']);
            $resultado['message'] = "Backup importado com sucesso! {$total} registros processados.";

        } catch (\Exception $e) {
            $resultado['success'] = false;
            $resultado['message'] = 'Erro ao importar: ' . $e->getMessage();
        }

        return $resultado;
    }

    /**
     * Importar colecao de registros
     */
    private static function importarColecao(string $modelClass, array $registros, string $modo): int
    {
        $count = 0;

        foreach ($registros as $dados) {
            // Remover _id para criar novo registro
            $originalId = $dados['_id'] ?? null;
            unset($dados['_id']);
            unset($dados['updated_at']);

            // Converter strings de data de volta para Carbon
            foreach (['data', 'data_inicio', 'data_fim', 'data_alerta', 'created_at'] as $campo) {
                if (isset($dados[$campo]) && is_string($dados[$campo])) {
                    try {
                        $dados[$campo] = \Carbon\Carbon::parse($dados[$campo]);
                    } catch (\Exception $e) {
                        // Manter como string se falhar
                    }
                }
            }

            try {
                $modelClass::create($dados);
                $count++;
            } catch (\Exception $e) {
                // Ignorar registros que falharem (ex: duplicados em modo mesclar)
            }
        }

        return $count;
    }

    /**
     * Validar arquivo de backup
     */
    public static function validarBackup(UploadedFile $file): array
    {
        $conteudo = file_get_contents($file->getRealPath());
        $backup = json_decode($conteudo, true);

        if (!$backup) {
            return [
                'valido' => false,
                'erro' => 'Arquivo JSON invalido.',
            ];
        }

        if (!isset($backup['dados'])) {
            return [
                'valido' => false,
                'erro' => 'Estrutura de backup invalida.',
            ];
        }

        return [
            'valido' => true,
            'versao' => $backup['versao'] ?? 'desconhecida',
            'data' => $backup['data_exportacao'] ?? null,
            'estatisticas' => $backup['estatisticas'] ?? [],
        ];
    }
}
