<?php

namespace Andruby\Login\Middleware;

use Closure;

class ImgCode
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        $response->header('Cache-Control', 'private, max-age=0, no-store, no-cache, must-revalidate');
        $response->header('Cache-Control', 'post-check=0, pre-check=0', false);
        $response->header('Pragma', 'no-cache');
        $response->header('Content-type', 'image/png');
        return $response;
    }
}
