<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AcceptOnlyJsonRequest
{
    /**
     * @param Request $request
     * @param Closure(Request): (Response) $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->header('Content-Type') != 'application/json') {
            return response()->json(["error" => "Unsupported media type"], 415);
        }

        return $next($request);
    }
}
