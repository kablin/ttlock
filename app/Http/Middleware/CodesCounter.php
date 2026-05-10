<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\LockApiLog;

class CodesCounter
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {




        if (!auth()->user()->code_packet()->exists()) {

            LockApiLog::create([
                'is_ttlock_result' => false,
                'api_method' => 'codes_error_1',
                'user_id' => auth()->user()->id,
                'ip' => json_encode($request->ip()),
                'params' => json_encode($request->all()),
            ]);
            return response()->json(['codes_error' => true, 'status' => false, 'error' => "Не осталось доступного пакета ключей", 'msg' => "Не осталось доступного пакета ключей"], 200);
        }

        if (auth()->user()->code_packet->end < now()) {
            LockApiLog::create([
                'is_ttlock_result' => false,
                'api_method' => 'codes_error_2',
                'user_id' => auth()->user()->id,
                'ip' => json_encode($request->ip()),
                'params' => json_encode($request->all()),
            ]);
            return response()->json(['codes_error' => true, 'status' => false, 'error' => "Срок действия пакета ключей закончился", 'msg' => "Срок действия пакета ключей закончился"], 200);
        }
        if (auth()->user()->code_packet->count < 1 && auth()->user()->code_packet->count != -100) {

            LockApiLog::create([
                'is_ttlock_result' => false,
                'api_method' => 'codes_error_3',
                'user_id' => auth()->user()->id,
                'ip' => json_encode($request->ip()),
                'params' => json_encode($request->all()),
            ]);
            return response()->json(['codes_error' => true, 'status' => false, 'error' => "Срок действия пакета ключей закончился", 'msg' => "Не осталось доступного пакета ключей"], 200);
        }

        return $next($request);
    }
}
