<?php

namespace Modules\Auth\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use JWTAuth;
use Symfony\Component\HttpKernel\Exception\HttpException;

class JwtMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $user=JWTAuth::parseToken()->authenticate();
        } catch (Exception $e) {
            if($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
                return response()->json(['error' => 'nonAuth'], 401);
            } elseif ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
                return response()->json(['error' => 'nonAuth'], 401);
            } elseif ($e instanceof HttpException) {
                return response()->json(['error' => $e->getMessage()], $e->getStatusCode());
            } else {
                return response()->json(['error' => 'nonAuth']);
            }
        }
        return $next($request);
    }
}
