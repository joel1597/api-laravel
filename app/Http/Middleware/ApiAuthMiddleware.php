<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {   //comprobar si el usuario esta autentificado
        $token = $request->header('Authorization');
        $jwt = new \JwtAuth();
        $checktoken = $jwt->checkToken($token);

        if($checktoken){
            return $next($request);    
        }else{
            $data = [
                'status' => 'false',
                'code' => 404,
                'message' => 'el usuario no esta identificado'                
            ];

            return response()->json($data);
            
        }
    
    }
}
