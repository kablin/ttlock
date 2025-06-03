<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;


class CodesDec
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {



        if (!auth()->user()->code_packet()->exists())
        return response()->json(['codes_error'=> true,'status' => false, 'msg' => "no codes"], 200);

        if (auth()->user()->code_packet->end < now() ) 
        return response()->json(['codes_error'=> true,'status' => false, 'msg' => "codes expired"], 200);

        if (auth()->user()->code_packet->count < 1)
        return response()->json(['codes_error'=> true,'status' => false, 'msg' => "no codes"], 200);

        auth()->user()->code_packet->count = auth()->user()->code_packet->count - 1;
        auth()->user()->code_packet->save();


        return $next($request);
    }
}
