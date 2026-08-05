<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class VerifySyncToken
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        $secret = config('services.sync.secret');

        if (! is_string($secret) || $secret === '' || ! is_string($token) || ! hash_equals($secret, $token)) {
            abort(401, 'Invalid sync token.');
        }

        return $next($request);
    }
}
