<?php


namespace App\Services;




use App\Models\Lock;
use App\Models\User;
use App\Models\LockApiLog;
use App\Models\LocksCredential;
use App\Models\LocksToken;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

use Illuminate\Support\Facades\Log;

class TTLockService
{
	public string $api_url = 'https://euapi.sciener.com';
	public string $client_id = '2b900bc5aa5a44699367af2970da0672';
	public string $client_secret = '6fa6d3c8a261022a26007bc8e8119ac8';

	//		private string $login;
	//		private string $password;
	//		public int|null $lock_id;
	//		public ?object $tokens;
	//		public ?object $user;


	public function __construct(private ?User $user = null) {}


	/*	public function setTokens($tokens)
		{
				$this->tokens = $tokens;
				return $this;
		}
*/



	/*	public function forceUpdateRefreshToken($token_id)
		{
				$this->tokens = LocksToken::find($token_id);
				if (Carbon::parse($this->tokens?->expires_in) < Carbon::now()->addDay()->endOfDay()) {
						$this->refreshToken();
				}
				return $this;
		}

	*/


	public  function auth(LocksCredential $cred): array
	{

		$data = $this->request('/oauth2/token', [
			'username' => (is_int($cred->login)) ? '+' . $cred->login : $cred->login,
			'password' => ($this->isValidMd5($cred->password)) ? $cred->password : md5($cred->password),
			'client_secret' => $this->client_secret,
		]);

		LockApiLog::create([
			'api_method	' => 'oauth2',
			'params' => $data,
		]);

		return $data;
	}

	public function refreshToken(LocksCredential $cred): array
	{

		$request = $this->request('/oauth2/token', [
			'grant_type' => 'refresh_token',
			'refresh_token' => $cred->token->refresh_token,
			'accessToken' => $cred->token->access_token,
			'client_secret' => $this->client_secret,
		]);

		if ($request['status'] && isset($request['data']['access_token'])) {
			info("У ttlock credentional с id $cred->id обновился RefreshToken");
			$cred->token->access_token = $request['data']['access_token'];
			$cred->token->refresh_token = $request['data']['refresh_token'];
			$cred->expires_in = Carbon::now()->addSeconds($request['data']['expires_in'])->toDateTimeString();

			$cred->token->save();
		}

		LockApiLog::create([
			'api_method	' => 'refresh_token',
			'params' => $request,
		]);

		return $request;
	}

	public function getLockList()
	{
		$data = $this->request('/v3/lock/list', [
			'pageNo' => 1,
			'pageSize' => 10000,
		]);

		LockApiLog::create([
			'api_method	' => '/v3/lock/list',
			'params	' => $data,
			'user_id' => $this->user?->id,
		]);

		return   $data;
	}


	public function getLockDetails(Lock $lock)
	{
		$data = $this->request('/v3/lock/detail', [
			'lockId' => $lock->lock_id,
		]);

		$lock->api_logs()->create([
			'api_method	' => '/v3/lock/detail',
			'params	' => $data,
			'user_id' => $this->user?->id,
		]);
		return   $data;
	}



	public function getLockEvents(Lock $lock, $count = 100, $pageNo = 1)
	{
		$data = [
			'lockId' => $lock->lock_id,
			'pageNo' => $pageNo,
			'pageSize' => $count,
		];

		$request =   $this->request('/v3/lockRecord/list', $data);

		$lock->api_logs()->create([
			'api_method	' => '/v3/lockRecord/list',
			'params	' => $request,
		]);

		return $request;
	}


	
	public function openLock(Lock $lock)
	{
		$result =  $this->request('/v3/lock/unlock', [
			'lockId' => $lock->lock_id,
		]);

		$lock->api_logs()->create([
			'api_method	' => '/v3/lock/unlock',
			'params	' => $result,
		]); 

		return  $result;
	}


/*


	public function changeOpenTime($time = 10)
	{
		return $this->request('/v3/lock/setAutoLockTime', [
			'lockId' => $this->lock_id,
			'seconds' => $time,
			'type' => 2,
		]);
	}

	//Системное время замка
	public function changeTimeInLock()
	{
		return $this->request('/v3/lock/updateDate', [
			'lockId' => $this->lock_id,
		]);
	}
*/



	public function setPassageModeOn(Lock $lock)
	{

		$result = $this->request('/v3/lock/configPassageMode', [
			'lockId' => $lock->lock_id,
			'passageMode' => 1,
			'isAllDay' => 1,
			'weekDays' => '[1,2,3,4,5,6,7]',
			'autoUnlock' => 1,
			'type' => 2,
		]);

		$lock->api_logs()->create([
			'api_method	' => '/v3/lock/configPassageMode',
			'params	' => $result,
		]);

		$this->openLock($lock);

		return  $result;
	}


	public function setPassageModeOff(Lock $lock)
	{
		$result = $this->request('/v3/lock/configPassageMode', [
			'lockId' => $lock->lock_id,
			'passageMode' => 2,
			'type' => 2,
		]);

		$lock->api_logs()->create([
			'api_method	' => '/v3/lock/configPassageMode',
			'params	' => $result,
		]);
		return  $result;
	}




	public function newKey($code, Lock $lock, $begin = null, $end = null): array
	{
		$name = 'Ключ от Renty api';

		if (is_null($begin)) {
			$begin = Carbon::now()->unix();
		} else {
			$begin = Carbon::parse($begin)->unix();
		}


		if (is_null($end)) {
			$end = Carbon::today()->endOfDay()->unix();
		} else {
			$end = Carbon::parse($end)->unix();
		}

		$request = $this->request('/v3/keyboardPwd/add', [
			'lockId' => $lock->lock_id,
			'keyboardPwd' => $code,
			'addType' => 2,
			'keyboardPwdName' => $name,
			'startDate' => $begin * 1000,
			'endDate' => $end * 1000,
		]);


		$lock->api_logs()->create([
			'api_method	' => '/v3/keyboardPwd/add',
			'params	' => $request,
			'user_id' => $this->user?->id,
		]);


		if ($request['status']) {
			return [
				'status' => true,
				'msg' => 'Ключ успешно загружен в замок, ID: ' . $request['data']['keyboardPwdId'],
				'type' => 'success',
				'data' => $request['data']
			];
		} else {
			return [
				'status' => false,
				'msg' => $request['msg'],
				'type' => 'danger',
			];
		}
	}
	/*
	public function changePeriod($keyId, $begin = null, $end = null)
	{
		if (is_null($begin)) {
			$begin = Carbon::now()->unix();
		} else {
			$begin = Carbon::parse($begin)->unix();
		}

		if (is_null($end)) {
			$end = Carbon::today()->endOfDay()->unix();
		} else {
			$end = Carbon::parse($end)->unix();
		}

		$request = $this->request('/v3/key/changePeriod', [
			'keyId' => $keyId,
			'startDate' => $begin * 1000,
			'endDate' => $end * 1000,
		]);

		if ($request['status']) {
			return [
				'status' => true,
				'msg' => 'Ключ успешно изменен!',
				'type' => 'success',
			];
		} else {
			return [
				'status' => false,
				'msg' => 'Ошибка изменения время действия ключа. Ошибка: ' . $request['msg'],
				'type' => 'danger',
			];
		}
	}

	public function updateKey($pwdID, $begin = null, $end = null)
	{
		if (is_null($begin)) {
			$begin = Carbon::now()->unix();
		} else {
			$begin = Carbon::parse($begin)->setTimezone('Europe/Moscow')->unix();
		}

		if (is_null($end)) {
			$end = Carbon::today()->endOfDay()->unix();
		} else {
			$end = Carbon::parse($end)->setTimezone('Europe/Moscow')->unix();
		}

		$request = $this->request('/v3/keyboardPwd/change', [
			'lockId' => $this->lock_id,
			'keyboardPwdId' => $pwdID,
			'changeType' => 2,
			'startDate' => $begin * 1000,
			'endDate' => $end * 1000,
		]);

		if ($request['status']) {
			return [
				'status' => true,
				'msg' => 'Ключ успешно изменен!',
				'type' => 'success',
			];
		} else {
			return [
				'status' => false,
				'msg' => 'Ошибка обновления ключа. Ошибка: ' . $request['msg'],
				'type' => 'danger',
			];
		}
	}
*/
	public function deleteKey(Lock $lock, $pwdID)
	{

		$request = $this->request('/v3/keyboardPwd/delete', [
			'lockId' => $lock->lock_id,
			'deleteType' => 2,
			'keyboardPwdId' => $pwdID,
		]);


		$lock->api_logs()->create([
			'api_method	' => '/v3/keyboardPwd/delete',
			'params	' => $request,
			'user_id' => $this->user?->id,
		]);

		return $request;

		if ($request['status']) {
			return [
				'status' => true,
				'msg' => 'Ключ успешно удален из замка!',
				'type' => 'success',
			];
		} else {
			return [
				'status' => false,
				'msg' =>  $request['msg'] ,
				'type' => 'danger',
			];
		}
	}
/*
	public function gatewayStatus()
	{
		$request = $this->request('/v3/lock/queryOpenState', [
			'lockId' => $this->lock_id,
		]);

		return $request['status'];
	}

	public function statusToHuman($status)
	{
		$statuses = [
			1 => 'Открыли через приложение/API',
			4 => 'Открыли паролем',
			7 => 'Открыли IC картой',
			8 => 'Открыли отпечатком пальца',
			9 => 'Открыли браслетом',
			10 => 'Открыли ключом',
			12 => 'Открыли через приложение/API',
			29 => 'Самопроизвольная разблокировка',
			32 => 'Открыли изнутри',
			33 => 'Закрыли отпечатком пальца',
			34 => 'Закрыли паролем',
			35 => 'Закрыли IC картой',
			36 => 'Закрыли ключом',
			44 => 'Неправильный код',
			46 => 'Открыли изнутри',
			48 => '5 попыток ввода неверного ключа',
		];

		return (isset($statuses[$status])) ? $statuses[$status] : $status;
	}

	public function warningStatus($status): bool
	{
		$statuses = [
			48,
			44,
			29
		];

		return isset($statuses[$status]);
	}

	public function getWifiLockDetails()
	{
		return $this->request('/v3/wifiLock/detail', [
			'lockId' => $this->lock_id,
		]);
	}
*/
	private function request($url, $array)
	{
		if ($url[0] != '/') $url = '/' . $url;

		$array = array_merge([
			'accessToken' => $this->user?->token?->access_token ?? '',
			'client_id' => $this->client_id,
			'clientId' => $this->client_id,
			'date' => round(microtime(true) * 1000) + 1000,
		], $array);

		try {
			$request = Http::accept('application/json')->asForm()->withHeaders([
				'Content-Type' => 'application/x-www-form-urlencoded',
			])->post($this->api_url . $url, $array);

			$status = false;
			$data = json_decode($request->body(), true);

			if (isset($data['errcode']) && $data['errcode'] != 0) {
				$text = "у замка";

				$default_msg = "";

				if ($data['errcode'] == 1) {
					$status = false;
					$error_msg = 'Ошибка: отказ или неудача';
				} elseif ($data['errcode'] == 10000) {
					$status = false;
					$error_msg = 'Неверный client_id';
				} elseif ($data['errcode'] == 10001) {
					$status = false;
					$error_msg = 'Неверный клиент';
				} elseif ($data['errcode'] == 10003) {
					$status = false;
					$error_msg = 'Неверный токен';
				} elseif ($data['errcode'] == 10004) {
					$status = false;
					$error_msg = 'Неверный grant';
				} elseif ($data['errcode'] == 10007) {
					$status = false;
					$error_msg = 'Неверный логин или пароль';
				} elseif ($data['errcode'] == 10011) {
					$status = false;
					$error_msg = 'Неверный refresh_token';
				} elseif ($data['errcode'] == 20002) {
					$status = false;
					$error_msg = 'Не администратор замка';
				} elseif ($data['errcode'] == 30002) {
					$status = false;
					$error_msg = 'Неверное имя пользователя, допустимы только английские символы и цифры';
				} elseif ($data['errcode'] == 30003) {
					$status = false;
					$error_msg = 'Пользователь уже зарегистрирован';
				} elseif ($data['errcode'] == 30004) {
					$status = false;
					$error_msg = 'Неверный ID пользователя для удаления';
				} elseif ($data['errcode'] == 30005) {
					$status = false;
					$error_msg = 'Пароль должен быть зашифрован с использованием MD5';
				} elseif ($data['errcode'] == 30006) {
					$status = false;
					$error_msg = 'Превышено количество API запросов';
				} elseif ($data['errcode'] == 80000) {
					$status = false;
					$error_msg = 'Дата должна быть текущей с точностью до 5 минут';
				} elseif ($data['errcode'] == 80002) {
					$status = false;
					$error_msg = 'Неверный формат JSON';
				} elseif ($data['errcode'] == 90000) {
					$status = false;
					$error_msg = 'Внутренняя ошибка сервера';
				} elseif ($data['errcode'] == -3) {
					$status = false;
					$error_msg = 'Неверный параметр';
				} elseif ($data['errcode'] == -2018) {
					$status = false;
					$error_msg = 'Доступ запрещен';
				} elseif ($data['errcode'] == -4063) {
					$status = false;
					$error_msg = 'Удалите/перенесите сначала все свои замки';
				} elseif ($data['errcode'] == -1003) {
					$status = false;
					$error_msg = 'Замок не существует';
				} elseif ($data['errcode'] == -2025) {
					$status = false;
					$error_msg = 'Замок заморожен. Управление невозможно';
				} elseif ($data['errcode'] == -3011) {
					$status = false;
					$error_msg = 'Нельзя передать замок самому себе';
				} elseif ($data['errcode'] == -4043) {
					$status = false;
					$error_msg = 'Функция не поддерживается для этого замка';
				} elseif ($data['errcode'] == -4056) {
					$status = false;
					$error_msg = 'Недостаточно памяти';
				} elseif ($data['errcode'] == -4067) {
					$status = false;
					$error_msg = 'NB устройство не зарегистрировано';
				} elseif ($data['errcode'] == -4082) {
					$status = false;
					$error_msg = 'Неверный период автозапирания';
				} elseif ($data['errcode'] == -1008) {
					$status = false;
					$error_msg = 'eKey не существует';
				} elseif ($data['errcode'] == -1016) {
					$status = false;
					$error_msg = 'Уже существует идентичное имя. Пожалуйста, выберите другое';
				} elseif ($data['errcode'] == -1018) {
					$status = false;
					$error_msg = 'Группа не существует';
				} elseif ($data['errcode'] == -1027) {
					$status = false;
					$error_msg = 'Нельзя отправить eKey на аккаунт, связанный с другим';
				} elseif ($data['errcode'] == -2019) {
					$status = false;
					$error_msg = 'Нельзя отправить eKey самому себе';
				} elseif ($data['errcode'] == -2020) {
					$status = false;
					$error_msg = 'Нельзя отправить eKey администратору';
				} elseif ($data['errcode'] == -2023) {
					$status = false;
					$error_msg = 'Невозможно изменить временной интервал';
				} elseif ($data['errcode'] == -4064) {
					$status = false;
					$error_msg = 'eKey можно отправить только на зарегистрированный аккаунт';
				} elseif ($data['errcode'] == -1007) {
					$status = false;
					$error_msg = 'Нет данных пароля для этого замка';
				} elseif ($data['errcode'] == -2009) {
					$status = false;
					$error_msg = 'Неверный пароль';
				} elseif ($data['errcode'] == -3006) {
					$status = false;
					$error_msg = 'Неверный код. Длина должна быть от 6 до 9 цифр';
				} elseif ($data['errcode'] == -3007) {
					$status = false;
					$error_msg = 'Такой код уже существует. Используйте другой';
				} elseif ($data['errcode'] == -3008) {
					$status = false;
					$error_msg = 'Невозможно изменить код, который еще не использовался';
				} elseif ($data['errcode'] == -3009) {
					$status = false;
					$error_msg = 'Недостаточно места для сохранения кодов. Удалите ненужные и повторите попытку';
				} elseif ($data['errcode'] == -2012) {
					$status = false;
					$error_msg = 'Замок не подключен к шлюзу';
				} elseif ($data['errcode'] == -3002) {
					$status = false;
					$error_msg = 'Шлюз отключен. Проверьте соединение';
				} elseif ($data['errcode'] == -3003) {
					$status = false;
					$error_msg = 'Шлюз занят. Повторите позже';
				} elseif ($data['errcode'] == -3016) {
					$status = false;
					$error_msg = 'Невозможно передать шлюз самому себе';
				} elseif ($data['errcode'] == -3034) {
					$status = false;
					$error_msg = 'Сеть не настроена. Пожалуйста, настройте сеть';
				} elseif ($data['errcode'] == -3035) {
					$status = false;
					$error_msg = 'Замок в энергосберегающем режиме. Отключите этот режим';
				} elseif ($data['errcode'] == -3036) {
					$status = false;
					$error_msg = 'Замок отключен. Проверьте подключение';
				} elseif ($data['errcode'] == -3037) {
					$status = false;
					$error_msg = 'Замок занят. Повторите позже';
				} elseif ($data['errcode'] == -4037) {
					$status = false;
					$error_msg = 'Такого шлюза не существует';
				} elseif ($data['errcode'] == -1021) {
					$status = false;
					$error_msg = 'Эта IC-карта не существует';
				} elseif ($data['errcode'] == -1023) {
					$status = false;
					$error_msg = 'Этот отпечаток пальца не существует';
				} else {
					$status = false;
					$error_msg = 'Неизвестная ошибка';
				}
			} elseif (!is_null($data)) {
				$status = true;
			}

			$return = [
				'status' => $status ?? true,
				'msg' => $error_msg ?? '',
				'error_code' => $data['errcode'] ?? '',
				'data' => (!$status) ? '' : $data,
			];
		} catch (\Throwable $exception) {
			$return = [
				'status' => false,
				'msg' => 'Ошибка обращения к серверу TTLock. Повторите попытку позже.',
				'error' => $exception->getMessage()
			];
		}

		return $return;
	}

	private function isValidMd5($md5 = '')
	{
		return strlen($md5) == 32 && ctype_xdigit($md5);
	}

	/*	public function deleteLock()
	{
		$request = $this->request('/v3/lock/delete', [
			'lockId' => $this->lock_id,
		]);

		return $request['status'];
	}
*/
	public function webhookHandler($data) {}
}
