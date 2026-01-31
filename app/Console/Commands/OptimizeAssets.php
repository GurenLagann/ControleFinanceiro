<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class OptimizeAssets extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'assets:optimize {--clear : Limpar cache de assets}';

    /**
     * The console command description.
     */
    protected $description = 'Otimizar e gerenciar assets da aplicacao';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando otimizacao de assets...');

        if ($this->option('clear')) {
            $this->clearAssetCache();
        }

        // Limpar caches do Laravel
        $this->call('config:cache');
        $this->call('route:cache');
        $this->call('view:cache');

        // Verificar se existe build do Vite
        $buildPath = public_path('build');
        if (File::isDirectory($buildPath)) {
            $manifestPath = $buildPath . '/manifest.json';
            if (File::exists($manifestPath)) {
                $this->info('Build do Vite encontrado com manifest.');
                $this->displayBuildStats($buildPath);
            } else {
                $this->warn('Build do Vite encontrado mas sem manifest. Execute: npm run build');
            }
        } else {
            $this->warn('Nenhum build do Vite encontrado. Execute: npm run build');
        }

        // Listar arquivos JS customizados
        $jsPath = public_path('js');
        if (File::isDirectory($jsPath)) {
            $jsFiles = File::files($jsPath);
            $this->info("\nArquivos JS customizados:");
            foreach ($jsFiles as $file) {
                $size = $this->formatBytes($file->getSize());
                $this->line("  - {$file->getFilename()} ({$size})");
            }
        }

        $this->newLine();
        $this->info('Otimizacao concluida!');
        $this->displayRecommendations();

        return Command::SUCCESS;
    }

    /**
     * Limpar cache de assets
     */
    protected function clearAssetCache()
    {
        $this->info('Limpando cache de assets...');

        // Limpar cache do Laravel
        $this->call('cache:clear');

        // Limpar views compiladas
        $this->call('view:clear');

        // Limpar arquivos de build antigos (manter apenas o mais recente)
        $buildPath = public_path('build');
        if (File::isDirectory($buildPath)) {
            // Manter o manifest e arquivos atuais
            $this->info('Cache de build preservado.');
        }

        $this->info('Cache de assets limpo!');
    }

    /**
     * Exibir estatisticas do build
     */
    protected function displayBuildStats(string $buildPath)
    {
        $totalSize = 0;
        $fileCount = 0;

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($buildPath, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        $stats = [
            'js' => ['count' => 0, 'size' => 0],
            'css' => ['count' => 0, 'size' => 0],
            'other' => ['count' => 0, 'size' => 0],
        ];

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $size = $file->getSize();
                $totalSize += $size;
                $fileCount++;

                $ext = strtolower($file->getExtension());
                if ($ext === 'js') {
                    $stats['js']['count']++;
                    $stats['js']['size'] += $size;
                } elseif ($ext === 'css') {
                    $stats['css']['count']++;
                    $stats['css']['size'] += $size;
                } else {
                    $stats['other']['count']++;
                    $stats['other']['size'] += $size;
                }
            }
        }

        $this->newLine();
        $this->info('Estatisticas do Build:');
        $this->table(
            ['Tipo', 'Arquivos', 'Tamanho'],
            [
                ['JavaScript', $stats['js']['count'], $this->formatBytes($stats['js']['size'])],
                ['CSS', $stats['css']['count'], $this->formatBytes($stats['css']['size'])],
                ['Outros', $stats['other']['count'], $this->formatBytes($stats['other']['size'])],
                ['Total', $fileCount, $this->formatBytes($totalSize)],
            ]
        );
    }

    /**
     * Exibir recomendacoes
     */
    protected function displayRecommendations()
    {
        $this->newLine();
        $this->info('Recomendacoes de Performance:');
        $this->line('  1. Execute "npm run build" para minificar assets');
        $this->line('  2. Configure CDN para assets estaticos');
        $this->line('  3. Habilite mod_deflate e mod_expires no Apache');
        $this->line('  4. Use "php artisan assets:optimize --clear" periodicamente');
    }

    /**
     * Formatar bytes para leitura humana
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
