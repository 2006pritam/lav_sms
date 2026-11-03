<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\URL;

/**
 * When the Cloudflare edge fronts the app it tells us the address visitors really use
 * (X-Public-Host), so links, redirects and asset URLs point at Cloudflare, not at Voroa.
 * If EDGE_SECRET is set, the header is honoured only when the matching secret is sent.
 */
class EdgeHost
{
    public function handle($request, Closure $next)
    {
        $host = $request->headers->get('X-Public-Host');

        if ($host && preg_match('/^[A-Za-z0-9.-]+(:\d+)?$/', $host)) {
            $secret = env('EDGE_SECRET');
            $sent = (string) $request->headers->get('X-Edge-Secret', '');

            if (!$secret || hash_equals((string) $secret, $sent)) {
                URL::forceRootUrl('https://'.$host);
            }
        }

        return $next($request);
    }
}
