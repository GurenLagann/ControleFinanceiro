<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CompressResponse
{
    /**
     * Content types que devem ser comprimidos
     */
    protected array $compressibleTypes = [
        'text/html',
        'text/plain',
        'text/css',
        'text/javascript',
        'application/javascript',
        'application/json',
        'application/xml',
        'text/xml',
        'image/svg+xml',
    ];

    /**
     * Tamanho minimo para compressao (em bytes)
     */
    protected int $minSize = 1024; // 1KB

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Verificar se o cliente aceita gzip
        if (!$this->clientAcceptsGzip($request)) {
            return $response;
        }

        // Verificar se o content-type e comprimivel
        if (!$this->isCompressible($response)) {
            return $response;
        }

        // Verificar tamanho minimo
        $content = $response->getContent();
        if (strlen($content) < $this->minSize) {
            return $response;
        }

        // Verificar se ja esta comprimido
        if ($response->headers->has('Content-Encoding')) {
            return $response;
        }

        // Comprimir com gzip
        $compressed = gzencode($content, 6);
        if ($compressed === false) {
            return $response;
        }

        // Atualizar resposta
        $response->setContent($compressed);
        $response->headers->set('Content-Encoding', 'gzip');
        $response->headers->set('Vary', 'Accept-Encoding');
        $response->headers->remove('Content-Length');

        return $response;
    }

    /**
     * Verificar se o cliente aceita gzip
     */
    protected function clientAcceptsGzip(Request $request): bool
    {
        $acceptEncoding = $request->header('Accept-Encoding', '');
        return str_contains($acceptEncoding, 'gzip');
    }

    /**
     * Verificar se o content-type e comprimivel
     */
    protected function isCompressible(Response $response): bool
    {
        $contentType = $response->headers->get('Content-Type', '');

        foreach ($this->compressibleTypes as $type) {
            if (str_contains($contentType, $type)) {
                return true;
            }
        }

        return false;
    }
}
