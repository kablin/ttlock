<?php

use App\Models\Lock;
use App\Models\LocksCredential;
use App\Models\LocksToken;
use App\Services\TTLockService;



if (!function_exists('updateRefreshToken')) {
	function updateRefreshToken(\App\Models\LocksCredential $credential): bool
	{
		try {
			if (!$credential->login || !$credential->password) {
				return false;
			}

			$servise =  new TTLockService();
			$auth = $servise->auth($credential);
			if ($auth['status']) {

				$tokens = LocksToken::updateOrCreate([
					'credential_id' => $credential->id
				], [
					'access_token' => $auth['data']['access_token'],
					'uid' => $auth['data']['uid'],
					'expires_in' => now()->addSeconds($auth['data']['expires_in'])->subDays(4)->toDateTime(),
					'refresh_token' => $auth['data']['refresh_token'],
				]);

				info("У ttlock credentional с id $credential->id обновился Token");

				$servise->refreshToken($credential);

				return true;
			}

			return false;
		} catch (\Exception $exception) {
			info($exception->getMessage());
			return false;
		}
	}
}



if (!function_exists('testCredential')) {
	function testCredential(string $login, string $password): bool
	{
		try {


			$servise =  new TTLockService();
			$auth = $servise->testAuth($login, $password);
			if ($auth['status']) {
				return true;
			}

			return false;
		} catch (\Exception $exception) {
			info($exception->getMessage());
			return false;
		}
	}
}




if (!function_exists('get_msg_by_type')) {
	function get_msg_by_type(int $type): string
	{

		switch ($type) {
			case 4:
				return 'Замок успешно открыт';
				break;
			case 7:
				return 'Неизветный код';
				break;
			case 11:
				return 'Код истек';
				break;
			default:
			    return 'Ошибка';
		}
	}
}
