<?php


namespace App\Services;




use App\Models\Lock;
use App\Models\LockJob;
use App\Models\User;
use App\Models\CodePacket;
use App\Models\LocksToken;
use Carbon\Carbon;
use App\Jobs\CreateLockJob;
use App\Jobs\GetLockListJob;
use App\Jobs\GetCodesListJob;
use App\Jobs\CreateCredentialJob;
use App\Jobs\SetStatusJob;
use App\Jobs\ChatPushJob;
use App\Jobs\ChangeCodeJob;
use App\Jobs\SetPassageModeOffJob;
use App\Jobs\SetPassageModeOnJob;
use App\Jobs\RefreshLockTokenJob;
use App\Jobs\AddKeyToLockJob;
use App\Jobs\DeleteKeyJob;
use App\Models\Rent;
use App\Jobs\OpenLockJob;
use App\Models\LockEvent;
use App\Models\LockPinCode;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Bus;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Log;
use Throwable;


class JobsService
{
    public function __construct(private int $user_id = 0) {}



    private function startLockJob(string $task,  $tag = null, $parent = null)
    {
        return LockJob::create(['user_id' => $this->user_id, 'parent_job' => $parent, 'task' => $task, 'tag' => $tag ? json_encode($tag) : ""]);
    }


    public function getDelay()
    {
        if ($this->user_id) {
            $user = User::find($this->user_id);
            if ($user) {
                $t1 = new Carbon($user->last_query);
                $now = now();
                if ($t1->getTimestamp() >= $now->getTimestamp()) {
                    $user->last_query = $t1->addSeconds(2);
                    $user->save();
                    return  new Carbon($user->last_query);
                } else {
                    $user->last_query = $now;
                    $user->save();
                }
            }
        }

        return now();
    }



    public function createLock($tag)
    {
        $uuid = $this->startLockJob('createLock', $tag);
        CreateLockJob::dispatch()->onQueue('default')->chain([
            new SetStatusJob($uuid->id, true)
        ]);

        return response()->json(['job_id' => $uuid->job_id, 'status' => true,], 200);
    }


    public function getLockList($tag)
    {
        $uuid = $this->startLockJob('getLockList', $tag);
        GetLockListJob::dispatch($uuid->id)->onQueue('default')->chain([
            new SetStatusJob($uuid->id, true)
        ])->delay($this->getDelay());

        return response()->json(['job_id' => $uuid->job_id, 'status' => true,], 200);
    }



    public function refreshLockTocken(int $id, $tag)
    {
        $uuid = $this->startLockJob('refreshLockTocken', $tag);

        RefreshLockTokenJob::dispatch($id)->onQueue('default')->chain([
            new SetStatusJob($uuid->id, true)
        ]);
    }


    public function addKeyToLock($lock_id, $code, $code_name, $begin, $end, $tag, $utc)
    {
        $uuid = $this->startLockJob('addKeyToLock', $tag);
        $lock = auth()->user()->locks->where('lock_id', $lock_id)->first();

        AddKeyToLockJob::dispatch($uuid->id, $lock ? $lock?->id : 0, $code, $code_name, $begin, $end, $utc)->onQueue('default')->chain([
            new SetStatusJob($uuid->id,  $lock ? true : false)
        ])->delay($this->getDelay());

        return response()->json(['job_id' => $uuid->job_id, 'status' => true,], 200);
    }



    public function cancelBooking($params)
    {

        $global_uuid  = $this->startLockJob('createBooking', $params['tag'] ?? null);

        $pins = LockPinCode::where('rent_id', $params['rent_id'])->get();

        if (!$pins) {
            $data['status'] = false;
            $data['error'] = "Ключи не найдены";
            return response()->json($data, 200);
        }


        // Запускаем первый батч (попытка #0)
        $this->dispatchDelBatch($params, $pins, $global_uuid,  auth()->user()->realty_key, 0);


        return response()->json(['job_id' => $global_uuid->job_id, 'status' => true, 'pin_count' => count($pins)], 200);
    }


    //******************************************************************************************************** */
    public function createBooking($params)
    {

        /* $jobs = [];
        $uuids = [];
        $token = 'token';*/
        $global_uuid  = $this->startLockJob('createBooking', $params['tag'] ?? null);

        $rent = Rent::where('internal_id', $params['realty_id'])->first();

        if (!$rent) {
            $data['status'] = false;
            $data['error'] = "Объект не найден";
            return response()->json($data, 200);
        }

        $locks = auth()->user()->locks()->where('rent_id',  $rent->id)->get();

        if (!$locks->count()) {
            $data['status'] = false;
            $data['error'] = "К объекту не привязан ни один замок";
            return response()->json($data, 200);

            //Http::withToken($token)->withBody(json_encode($data), 'application/json')->post($job->user->callback);
        }


        // Запускаем первый батч (попытка #0)
        $this->dispatchBatch($params, $params['code'], $global_uuid, $locks,  auth()->user()->realty_key, 0);


        return response()->json(['job_id' => $global_uuid->job_id, 'status' => true, 'locks_count' => count($locks)], 200);
    }



    public function changeBooking($params)
    {

        $global_uuid  = $this->startLockJob('changeBooking', $params['tag'] ?? null);

        $pins = LockPinCode::where('rent_id', $params['rent_id'])->get();

        if (!$pins) {
            $data['status'] = false;
            $data['error'] = "Ключи не найдены";
            return response()->json($data, 200);
        }



        // Запускаем первый батч (попытка #0)
        $this->dispatchChangeBatch($params, $params['code'], $global_uuid, $pins,  auth()->user()->realty_key, 0);


        return response()->json(['job_id' => $global_uuid->job_id, 'status' => true, 'locks_count' => count($pins)], 200);
    }





    private function dispatchBatch(array $options, $code, $global_uuid, $locks, $token, int $attempt = 0): void
    {
        $jobs = [];
        $uuids = [];

        foreach ($locks as $lock) {
            $uuid  = $this->startLockJob('addKeyToLock', $params['tag'] ?? null, $global_uuid->id);
            $uuids[] = $uuid->id;
            $lock_ids[] = $lock->id;

            $jobs[] = (new AddKeyToLockJob(
                $uuid->id,
                $lock->id,
                $code,
                $options['code_name'] ?? '',
                $options['begin_date'] . ' ' . $options['arrival_time'],
                $options['end_date'] . ' ' . $options['departure_time'],
                $options['utc'] ?? null,
                $options['rent_id']
            ))->delay($this->getDelay());
        }


        $batch = Bus::batch($jobs)
            ->withOption('uuids', $uuids)
            ->withOption('params', $options)
            ->withOption('token', $token)
            ->withOption('locks', $locks)
            ->withOption('code', $code)
            ->withOption('global_uuid', $global_uuid)
            ->withOption('attempt', $attempt)   // <--- Номер попытки
            ->then(fn(Batch $b) => $this->handleBatchThen($b))
            ->catch(fn(Batch $b, Throwable $e) => $this->handleBatchCatch($b, $e))
            ->onQueue('default')
            ->dispatch();
    }




    private function dispatchDelBatch(array $options, $pins, $global_uuid,  $token, int $attempt = 0): void
    {
        $jobs = [];
        $uuids = [];

        foreach ($pins as $pin) {
            $uuid  = $this->startLockJob('delKeyFromLock', $params['tag'] ?? null, $global_uuid->id);
            $uuids[] = $uuid->id;


            $jobs[] = (new DeleteKeyJob(
                $uuid->id,
                $pin->lock->id,
                $pin->pin_code_id,
                $options['rent_id'],
            ))->delay($this->getDelay());
        }


        $batch = Bus::batch($jobs)
            ->withOption('uuids', $uuids)
            ->withOption('params', $options)
            ->withOption('token', $token)
            ->withOption('pins', $pins)
            ->withOption('global_uuid', $global_uuid)
            ->withOption('attempt', $attempt)   // <--- Номер попытки
            ->then(fn(Batch $b) => $this->handleBatchThenDel($b))
            ->catch(fn(Batch $b, Throwable $e) => $this->handleBatchCatchDel($b, $e))
            ->onQueue('default')
            ->dispatch();
    }


    private function dispatchChangeBatch(array $options, $code, $global_uuid, $pins, $token, int $attempt = 0): void
    {
        $jobs = [];
        $uuids = [];

        foreach ($pins as $pin) {
            $uuid  = $this->startLockJob('addKeyToLock', $params['tag'] ?? null, $global_uuid->id);
            $uuids[] = $uuid->id;


            $jobs[] = (new ChangeCodeJob(
                $uuid->id,
                $pin->lock->id,
                $pin->pin_code_id,

                $options['begin_date'] . ' ' . $options['arrival_time'],
                $options['end_date'] . ' ' . $options['departure_time'],
                $options['utc'] ?? null,
                $options['rent_id']

            ))->delay($this->getDelay());
        }


        $batch = Bus::batch($jobs)
            ->withOption('uuids', $uuids)
            ->withOption('params', $options)
            ->withOption('token', $token)
            ->withOption('pins', $pins)
            ->withOption('code', $code)
            ->withOption('global_uuid', $global_uuid)
            ->withOption('attempt', $attempt)   // <--- Номер попытки
            ->then(fn(Batch $b) => $this->handleBatchThenChange($b))
            ->catch(fn(Batch $b, Throwable $e) => $this->handleBatchCatchChange($b, $e))
            ->onQueue('default')
            ->dispatch();
    }



    private function handleBatchThen(Batch $batch): void
    {
        $options = $batch->options;
        $operationId = $batch->id;
        $attempt = (int) ($options['attempt'] ?? 0);
        $maxAttempts = 3;

        // Проверяем, была ли коллизия в этом батче
        if (Cache::get("batch_needs_restart:{$operationId}", false)) {

            $collisionCount = (int) Cache::get("collision_count:{$options['global_uuid']['job_id']}", 0);
            foreach ($options['locks'] as $lock) {
                // удаляем коды со старым ключем, который смог записаться
                $pin_to_delete =  LockPinCode::where(['lock_id' => $lock->id, 'rent_id' => $options['params']['rent_id'], 'pin_code' => $options['params']['code'], 'is_confirm' => false])->get();

                foreach ($pin_to_delete as $pin) {
                    // удаляем коды со старым ключем, который смог записаться
                    $this->deleteKey($lock->id, $pin->pin_code_id, '');
                }
            }


            // Превышен лимит попыток?
            if ($collisionCount >= $maxAttempts) {
                $this->sendFinalError($options, "Не удалось создать ключ после {$maxAttempts} попыток");
                $this->cleanupCache($operationId, $options['global_uuid']['job_id']);
                return;
            }

            // Получаем новый код и запускаем СЛЕДУЮЩИЙ батч
            $newCode = Cache::get("final_code:{$operationId}");


            info("Restarting batch with new code", [
                'operation_id' => $operationId,
                'attempt' => $attempt + 1,
                'new_code' => $newCode,
            ]);

            $options['params']['code'] = $newCode;
            // Рекурсивный запуск следующего батча
            $this->dispatchBatch($options['params'], $newCode, $options['global_uuid'],  $options['locks'], $options['token'], $attempt + 1);

            $this->cleanupCache($operationId);
            return;
        }

        // === Все замки успешно записаны ===

        foreach ($options['locks'] as $lock) {
            LockPinCode::where(['lock_id' => $lock->id, 'rent_id' => $options['params']['rent_id'], 'pin_code' => $options['params']['code']])->update(['is_confirm' => true]);
        }


        $this->sendSuccessResponse($options, 'Ключ успешно записан');
        $this->cleanupCache($operationId, $options['global_uuid']['job_id']);
    }


    private function handleBatchThenDel(Batch $batch): void
    {
        $options = $batch->options;
        $operationId = $batch->id;
        $this->sendSuccessResponse($options, 'Ключ успешно удален');
        $this->cleanupCache($operationId, $options['global_uuid']['job_id']);
    }

        private function handleBatchThenChange(Batch $batch): void
    {
        $options = $batch->options;
        $operationId = $batch->id;
        $this->sendSuccessResponse($options, 'Ключ успешно обновлен');
        $this->cleanupCache($operationId, $options['global_uuid']['job_id']);
    }



    private function handleBatchCatch(Batch $batch, Throwable $e): void
    {
        $options = $batch->options;
        $operationId = $batch->id;;

        // Если это коллизия — она уже обработана в then(), игнорируем
        if ($e instanceof \RuntimeException && str_contains($e->getMessage(), 'уже есть в замке')) {
            return;
        }

        // Настоящая ошибка (сеть, таймаут, исчерпаны $tries)
        info("Batch failed", [
            'operation_id' => $operationId,
            'error' => $e->getMessage(),
            'batch_id' => $batch->id,
        ]);

        $this->sendFinalError($options, "Не удалось загрузить ключ: {$e->getMessage()}");
        $this->cleanupCache($operationId, $options['global_uuid']['job_id']);
    }


    private function handleBatchCatchDel(Batch $batch, Throwable $e): void
    {
        $options = $batch->options;
        $operationId = $batch->id;;

        // Настоящая ошибка (сеть, таймаут, исчерпаны $tries)
        info("Batch failed", [
            'operation_id' => $operationId,
            'error' => $e->getMessage(),
            'batch_id' => $batch->id,
        ]);

        $this->sendFinalError($options, "Не удалось удалить ключ: {$e->getMessage()}");
        $this->cleanupCache($operationId, $options['global_uuid']['job_id']);
    }


        private function handleBatchCatchChange(Batch $batch, Throwable $e): void
    {
        $options = $batch->options;
        $operationId = $batch->id;;

        // Настоящая ошибка (сеть, таймаут, исчерпаны $tries)
        info("Batch failed", [
            'operation_id' => $operationId,
            'error' => $e->getMessage(),
            'batch_id' => $batch->id,
        ]);

        $this->sendFinalError($options, "Не удалось обновить ключ: {$e->getMessage()}");
        $this->cleanupCache($operationId, $options['global_uuid']['job_id']);
    }


    private function sendSuccessResponse(array $options, string $msg): void
    {
        $data = [
            'job' => $options['global_uuid']['job_id'],
            'status' => true,
            'message' => $msg,
            'code' => $options['code'],
            'rent_id' => $options['params']['rent_id'],
            'method' => 'createBooking',
        ];

        $this->dispatchStatusJobs($options['uuids'] ?? [], true);
        info('Batch success', $data);
        $this->sendToApi($data, $options['token']);
    }

    private function sendFinalError(array $options, string $error): void
    {

        $data = [
            'job' => $options['global_uuid']['job_id'],
            'status' => false,
            'error' => $error,
            'code' => $options['code'],
            'rent_id' => $options['params']['rent_id'],
            'method' => 'createBooking',
        ];

        $this->dispatchStatusJobs($options['uuids'], false);
        $this->sendToApi($data, $options['token']);
    }



    private function dispatchStatusJobs(array $uuids, bool $status): void
    {
        foreach ($uuids as $uuid) {
            SetStatusJob::dispatch($uuid, $status)->onQueue('default');
        }
    }


    private function sendToApi(array $data, $token): void
    {
        info('sendToApi send', $data);
        // Http::withToken(config('services.rentysoft.token'))
        Http::withToken($token)
            ->withBody(json_encode($data), 'application/json')
            ->post('https://test.realtycalendar.ru/v2/integrations/renty_soft/receive_lock_code');
    }


    private function cleanupCache(string $operationId, string $collisionId = ''): void
    {
        info('cleanupCache');
        Cache::forget("final_code:{$operationId}");
        Cache::forget("batch_needs_restart:{$operationId}");
        if ($collisionId) Cache::forget("collision_count:{$collisionId}");
        Cache::forget("regenerate_lock:{$operationId}");
    }





    //******************************************************************************************************** */


    public function getCodesList($lock_id, $page_number, $page_size, $tag)
    {
        $uuid = $this->startLockJob('getCodesList', $tag);
        $lock = auth()->user()->locks->where('lock_id', $lock_id)->first();

        GetCodesListJob::dispatch(1, $uuid->id, $lock ? $lock?->id : 0, $page_number, $page_size)->onQueue('default')->chain([
            new SetStatusJob($uuid->id,  $lock ? true : false)
        ])->delay($this->getDelay());

        return response()->json(['job_id' => $uuid->job_id, 'status' => true,], 200);
    }




    public function changeCode($lock_id, $code_id, $begin, $end, $tag, $utc)
    {
        $uuid = $this->startLockJob('ChangeCode', $tag);
        $lock = auth()->user()->locks->where('lock_id', $lock_id)->first();

        ChangeCodeJob::dispatch($uuid->id, $lock ? $lock?->id : 0, $code_id,  $begin, $end, $utc)->onQueue('default')->chain([
            new SetStatusJob($uuid->id,  $lock ? true : false)
        ])->delay($this->getDelay());

        return response()->json(['job_id' => $uuid->job_id, 'status' => true,], 200);
    }



    public function setPassageModeOn($lock_id, $tag)
    {
        $uuid = $this->startLockJob('setPassageModeOn', $tag);
        $lock = auth()->user()->locks->where('lock_id', $lock_id)->first();
        SetPassageModeOnJob::dispatch($uuid->id, $lock ? $lock?->id : 0)->onQueue('default')->chain([
            new SetStatusJob($uuid->id,  $lock ? true : false)
        ])->delay($this->getDelay());

        return response()->json(['job_id' => $uuid->job_id, 'status' => true,], 200);
    }


    public function setPassageModeOff($lock_id, $tag)
    {
        $uuid = $this->startLockJob('setPassageModeOff', $tag);
        $lock = auth()->user()->locks->where('lock_id', $lock_id)->first();
        SetPassageModeOffJob::dispatch($uuid->id, $lock ? $lock?->id : 0)->onQueue('default')->chain([
            new SetStatusJob($uuid->id,  $lock ? true : false)
        ])->delay($this->getDelay());

        return response()->json(['job_id' => $uuid->job_id, 'status' => true,], 200);
    }

    public function deleteKey($lock_id, $pwdID, $tag)
    {
        $uuid = $this->startLockJob('deleteKey', $tag);
        $lock = auth()->user()->locks->where('lock_id', $lock_id)->first();
        DeleteKeyJob::dispatch($uuid->id, $lock ? $lock?->id : 0, $pwdID)->onQueue('default')->chain([
            new SetStatusJob($uuid->id,  $lock ? true : false)
        ])->delay($this->getDelay());

        return response()->json(['job_id' => $uuid->job_id, 'status' => true,], 200);
    }

    public function createCredential($user, $password, $tag)
    {
        $uuid = $this->startLockJob('createCredential', $tag);
        $user_id = auth()->user()->id;
        CreateCredentialJob::dispatch($uuid->id, $user, $password, $user_id)->onQueue('default')->chain([
            new SetStatusJob($uuid->id, true)
        ])->delay($this->getDelay());

        return response()->json(['job_id' => $uuid->job_id, 'status' => true,], 200);
    }



    public static function getLockEvents($lock_id, $lock_type, $record_type, $personal)
    {

        $evets = LockEvent::select('id', 'lock_id', 'record_type_from_lock', 'record_type', 'success', 'username', 'keyboard_pwd', 'lock_date')->where('lock_id', $lock_id);
        if ($lock_type) $evets = $evets->where('record_type_from_lock', $lock_type);
        if ($record_type && !$personal) $evets = $evets->where('record_type', $record_type);

        if ($personal) {
            $lock = Lock::where('lock_id', $lock_id)->first();
            $pins = $lock->allpincodes->pluck('pin_code')->unique()->toArray();
            $evets = $evets->where('success', true)->whereNotIn('keyboard_pwd', $pins)->where('record_type', 4);
        }
        return  $evets->orderBy('id', 'DESC')->take(100)->get();
    }


    public static function getCodeEvents($lock_id, $code)
    {

        $evets = LockEvent::select('id', 'lock_id', 'record_type_from_lock', 'record_type', 'success', 'username', 'keyboard_pwd', 'lock_date')->where('lock_id', $lock_id)
            ->where('keyboard_pwd', $code);


        return  $evets->orderBy('id', 'DESC')->take(100)->get();
    }


    public static function getLockEvents2($lock_ids, $type, $code)
    {

        $locks_ = explode(',', $lock_ids);
        foreach ($locks_ as $l) {
            $lock = auth()->user()->locks->where('lock_id', $l)->first();
            if (!$lock) return ['status' => false, 'msg' => "Неизвестный замок"];
        }


        if ($type == 1) {
            if ($code) {
                $event = LockEvent::select('id', 'lock_id', 'record_type_from_lock', 'record_type', 'success', 'username', 'keyboard_pwd', 'lock_date')->whereIn('lock_id', $locks_)
                    ->where('keyboard_pwd', $code)->where('success', true)->where('record_type_from_lock', 4)->orderBy('lock_date', 'ASC')->first();

                return $event;
            } else return '';
        } else   if ($type == 2) {

            $allpins = [];
            foreach ($locks_ as $l) {
                $lock = Lock::where('lock_id', $l)->first();
                $pins = $lock->allpincodes->pluck('pin_code')->unique()->toArray();

                $allpins = array_merge($allpins, $pins);
            }

            $events = LockEvent::select('id', 'lock_id', 'record_type_from_lock', 'record_type', 'success', 'username', 'keyboard_pwd', 'lock_date')->whereIn('lock_id', $locks_)
                ->where('success', true)->whereNotIn('keyboard_pwd', $allpins)->where('record_type', 4)->orderBy('id', 'DESC')->take(100)->get();

            return $events;
        } else {
            $events = LockEvent::select('id', 'lock_id', 'record_type_from_lock', 'record_type', 'success', 'username', 'keyboard_pwd', 'lock_date')->whereIn('lock_id', $locks_)
                ->orderBy('id', 'DESC')->take(100)->get();

            return $events;
        }
    }


    public static function addCodesCount($codes_count, $expired_at)
    {

        $code_packet = CodePacket::firstOrCreate(['user_id' => auth()->user()->id]);
        $code_packet->refresh();
        if ($codes_count == -1) $code_packet->count = -100;
        else $code_packet->count = $code_packet->count + $codes_count;
        $code_packet->end = $expired_at;
        $code_packet->save();
        return ['status' => true, 'msg' => "Пакет ключей успешно добавлен", 'codes_count' => $code_packet->count, 'expired_at' => $code_packet->end];
    }


    public static function SetCodesCount($codes_count)
    {

        $code_packet = CodePacket::where(['user_id' => auth()->user()->id])->first();
        if (!$code_packet)   return ['status' => false,];
        $code_packet->refresh();
        if ($codes_count == -1) $code_packet->count = -100;
        else $code_packet->count = $codes_count;
        $code_packet->save();
        return ['status' => true,  'msg' => "Пакет ключей успешно установлен", 'codes_count' => $code_packet->count, 'expired_at' => $code_packet->end];
    }


    public static function getCodesCount()
    {
        $code_packet = CodePacket::firstOrCreate(['user_id' => auth()->user()->id]);
        $code_packet->refresh();
        return ['status' => true, 'codes_count' => $code_packet->count, 'expired_at' => $code_packet->end];
    }



    public function openLock($lock_id, $tag)
    {
        $uuid = $this->startLockJob('openLock', $tag);
        $lock = auth()->user()->locks->where('lock_id', $lock_id)->first();
        OpenLockJob::dispatch($uuid->id, $lock ? $lock?->id : 0)->onQueue('default')->chain([
            new SetStatusJob($uuid->id,  $lock ? true : false)
        ])->delay($this->getDelay());

        return response()->json(['job_id' => $uuid->job_id, 'status' => true,], 200);
    }


    public function chatPush($message, $tag)
    {
        $uuid = $this->startLockJob('chatPush', $tag);
      
        ChatPusJob::dispatch($uuid->id, $message)->onQueue('default')->chain([
            new SetStatusJob($uuid->id, true )
        ])->delay($this->getDelay());

        return response()->json(['job_id' => $uuid->job_id, 'status' => true,], 200);
    }
}
