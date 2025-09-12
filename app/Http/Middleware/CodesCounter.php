<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;


class CodesCounter
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {


        if (!auth()->user()->code_packet()->exists())
        return response()->json(['codes_error'=> true,'status' => false, 'msg' => "Не осталось доступного пакета кодов"], 200);

        if (auth()->user()->code_packet->end < now() ) 
        return response()->json(['codes_error'=> true,'status' => false, 'msg' => "Срок действия пакета кодов закончился"], 200);

        if (auth()->user()->code_packet->count < 1 && auth()->user()->code_packet->count != -100)
        return response()->json(['codes_error'=> true,'status' => false, 'msg' => "Не осталось доступного пакета кодов"], 200);


        return $next($request);
    }
}
