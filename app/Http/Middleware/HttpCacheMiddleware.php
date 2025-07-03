<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HttpCacheMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next,  int $maxAge = 300): Response
    {
        $response = $next($request);

        // Only cache GET requests
        if ($request->isMethod('GET')) {
            $response->headers->set('Cache-Control', "public, max-age={$maxAge}");
            $response->headers->set('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + $maxAge));
            
            // Add ETag for better caching
            $etag = md5($response->getContent());
            $response->headers->set('ETag', $etag);
            
            // Check if client has a valid cached version
            if ($request->headers->get('If-None-Match') === $etag) {
                return response('', 304);
            }
        }

        return $response;
    }
}
