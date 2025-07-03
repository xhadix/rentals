<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\Response;

final class GzipResponse
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $response = $next($request);        
        Log::info('GzipResponse middleware called');
        
        if (! $this->shouldGzipResponse()) {
            Log::info('Gzip disabled in config');
            return $response;
        }

        if ($this->gzipDebugEnabled()) {
            return $response;
        }

        if ($response instanceof BinaryFileResponse || $response instanceof StreamedResponse) {
            return $response;
        }

        if (! $response instanceof Response) {
            return $response;
        }

        if (! $this->hasMinimumContentLength($response)) {
            Log::info('Content too small for gzip: ' . strlen((string)$response->getContent()));
            return $response;
        }

        Log::info('Content size: ' . strlen((string)$response->getContent()));
        Log::info('Accept encodings: ' . implode(', ', $request->getEncodings()));
        Log::info('gzencode function exists: ' . (function_exists('gzencode') ? 'yes' : 'no'));

        if (in_array('gzip', $request->getEncodings()) && function_exists('gzencode')) {
            $content = $response->getContent();
            Log::info('Content for compression: ' . (! empty($content) ? 'not empty' : 'empty'));
            if (! empty($content)) {
                $compressed = gzencode($content, $this->gzipLevel());
                Log::info('Compression result: ' . ($compressed ? 'success' : 'failed'));

                if ($compressed) {
                    Log::info('Applying gzip compression');
                    $response->setContent($compressed);

                    $response->headers->add([
                        'Content-Encoding' => 'gzip',
                        'Vary' => 'Accept-Encoding',
                        'Content-Length' => strlen($compressed),
                    ]);
                }
            }
        }

        return $response;
    }

    private function shouldGzipResponse(): bool
    {
        return filter_var(
            config('laravel-gzip.enabled', true),
            FILTER_VALIDATE_BOOLEAN,
        );
    }

    protected function minimumContentLength(): int
    {
        return intval(config('laravel-gzip.minimum_content_length', 100));
    }

    private function gzipLevel(): int
    {
        return intval(config('laravel-gzip.level', 5));
    }

    private function gzipDebugEnabled(): bool
    {
        return filter_var(
            config('laravel-gzip.debug', false),
            FILTER_VALIDATE_BOOLEAN,
        );
    }

    protected function hasMinimumContentLength(Response $response): bool
    {
        return filter_var(
            strlen((string)$response->getContent()) >= $this->minimumContentLength(),
            FILTER_VALIDATE_BOOLEAN,
        );
    }
} 