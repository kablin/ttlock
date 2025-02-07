<?php

use App\Models\Lock;
use App\Models\LocksCredential;
use App\Models\LocksToken;
use App\Services\TTLockService;



if (!function_exists('updateRefreshToken')) {
		function updateRefreshToken( \App\Models\Lock $lock): bool
		{
				try {
						if (!$lock?->credential->login || !$lock?->credential->password) {
								return false;
						}
                         
                        $servise =  new TTLockService($lock);
						$auth = $servise->auth(true);
						if ($auth['status']) {

								$tokens = LocksToken::updateOrCreate([
										'lock_id' => $lock->id,
										'credential_id' => $lock?->credential->id
								], [
										'access_token' => $auth['data']['access_token'],
										'uid' => $auth['data']['uid'],
										'expires_in' => now()->addSeconds($auth['data']['expires_in'])->subDays(4)->toDateTime(),
										'refresh_token' => $auth['data']['refresh_token'],
								]);

								$servise->refreshToken();
								$locks = $servise->getLockList();
								$find_lock = [];

								foreach ($locks['data']['list'] as $datum) {
										if ($datum['lockId'] == $lock->lock_id) {
												$find_lock['lock_alias'] = $datum['lockAlias'] ?? null;
												$find_lock['no_key_pwd'] = $datum['noKeyPwd'] ?? null;
												$find_lock['electric_quantity'] = $datum['electricQuantity'] ?? 0;
										}
								}

								$lock->update($find_lock);

								return true;
						}

						return false;
				} catch (\Exception $exception) {
						info($exception->getMessage());
						return false;
				}
		}
}
