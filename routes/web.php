<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
  return Inertia::render('Welcome');
})->name('home');







Route::middleware(['auth', 'verified'])->group(function () {

  Route::get('/dashboard', [\App\Http\Controllers\DashboarController::class, 'index'])->name('dashboard');


  // wizard old

  Route::get('/wizard', [\App\Http\Controllers\WizardController::class, 'index'])->name('wizard');
  Route::post('wizard/attach_lock', [\App\Http\Controllers\WizardController::class, 'attach_lock'])->name('wizard_attach_lock');
  Route::post('wizard/detach_lock', [\App\Http\Controllers\WizardController::class, 'detach_lock'])->name('wizard_detach_lock');
  Route::post('wizard/dattach_lock', [\App\Http\Controllers\WizardController::class, 'dattach_lock'])->name('wizard_dattach_lock');
  Route::post('wizard/page', [\App\Http\Controllers\WizardController::class, 'page'])->name('wizard_page');



  //wizard new


  Route::post('/getLockList', [\App\Http\Controllers\LockController::class, 'getLockList'])->name('wizard_lock_list');
  Route::get('/setup/wizard/step1', [\App\Http\Controllers\WizardController::class, 'step1'])->name('wizard_step1');
  Route::get('/setup/wizard/step2', [\App\Http\Controllers\WizardController::class, 'step2'])->name('wizard_step2');
  Route::get('/setup/wizard/step3', [\App\Http\Controllers\WizardController::class, 'step3'])->name('wizard_step3');
  Route::get('/setup/wizard/step4', [\App\Http\Controllers\WizardController::class, 'step4'])->name('wizard_step4');
  Route::get('/setup/wizard/step5', [\App\Http\Controllers\WizardController::class, 'step5'])->name('wizard_step5');

  Route::post('wizard/map', [\App\Http\Controllers\WizardController::class, 'map'])->name('wizard_map');
  Route::post('wizard/unmap', [\App\Http\Controllers\WizardController::class, 'unmap'])->name('wizard_unmap');



  /*
    Route::get('rents_objects', function () {
        return Inertia::render('RentsObjects');
    })->name('rents_objects');*/


  //RENTS-------------------------------------

  Route::get('/objects', [\App\Http\Controllers\RentController::class, 'objects2'])->name('objects2');

  Route::post('rents/create2', [\App\Http\Controllers\RentController::class, 'create2'])->name('rent_create2');
  Route::post('rents/delete2', [\App\Http\Controllers\RentController::class, 'delete2'])->name('rent_delete2');
  Route::post('rents/update2', [\App\Http\Controllers\RentController::class, 'update2'])->name('rent_update2');
  Route::post('rents/attach_lock2', [\App\Http\Controllers\RentController::class, 'attach_lock2'])->name('rent_attach_lock2');
  Route::post('rents/dattach_lock2', [\App\Http\Controllers\RentController::class, 'dattach_lock2'])->name('rent_dattach_lock2');
  Route::post('rents/detach_lock2', [\App\Http\Controllers\RentController::class, 'detach_lock2'])->name('rent_detach_lock2');

  Route::post('rents/page', [\App\Http\Controllers\RentController::class, 'page'])->name('rent_rent_page');


  //unused
  Route::get('/objects_', [\App\Http\Controllers\RentController::class, 'objects'])->name('objects');
  Route::post('rents/create', [\App\Http\Controllers\RentController::class, 'create'])->name('rent_create');
  Route::post('rents/delete', [\App\Http\Controllers\RentController::class, 'delete'])->name('rent_delete');
  Route::post('rents/update', [\App\Http\Controllers\RentController::class, 'update'])->name('rent_update');
  Route::post('rents/attach_lock', [\App\Http\Controllers\RentController::class, 'attach_lock'])->name('rent_attach_lock');
  Route::post('rents/detach_lock', [\App\Http\Controllers\RentController::class, 'detach_lock'])->name('rent_detach_lock');




  //groups

  Route::get('/groups', [\App\Http\Controllers\GroupController::class, 'index'])->name('groups');

  Route::post('groups/create', [\App\Http\Controllers\GroupController::class, 'create'])->name('group_create');
  Route::post('groups/delete', [\App\Http\Controllers\GroupController::class, 'delete'])->name('group_delete');
  Route::post('groups/update', [\App\Http\Controllers\GroupController::class, 'update'])->name('group_update');

  Route::post('groups/attach_lock', [\App\Http\Controllers\GroupController::class, 'attach_lock'])->name('group_attach_lock');
  Route::post('groups/detach_lock', [\App\Http\Controllers\GroupController::class, 'detach_lock'])->name('group_detach_lock');
  Route::post('groups/dattach_lock', [\App\Http\Controllers\GroupController::class, 'dattach_lock'])->name('group_dattach_lock');


  Route::post('groups/attach_rent', [\App\Http\Controllers\GroupController::class, 'attach_rent'])->name('group_attach_rent');
  Route::post('groups/detach_rent', [\App\Http\Controllers\GroupController::class, 'detach_rent'])->name('group_detach_rent');
  Route::post('groups/dattach_rent', [\App\Http\Controllers\GroupController::class, 'dattach_rent'])->name('group_dattach_rent');



  Route::post('groups/page', [\App\Http\Controllers\GroupController::class, 'page'])->name('groups_page');




  // pincodes
  Route::post('pincodes/getlist/{lock_id}', [\App\Http\Controllers\LockPinCodeController::class, 'pincodesList'])->name('pincodes_list');
  Route::post('pincodes/page/{lock_id}', [\App\Http\Controllers\LockPinCodeController::class, 'page'])->name('pincodes_page');



  //Logs
  Route::get('lockevents', [\App\Http\Controllers\LockEventController::class, 'index'])->name('lockevents');

  Route::post('lockevents/page/{lock_id}', [\App\Http\Controllers\LockEventController::class, 'lock_page'])->name('lockevents_lock_page');
  Route::post('lockevents/page', [\App\Http\Controllers\LockEventController::class, 'page'])->name('lockevents_page');


  //Route::get('/rents_objects', [\App\Http\Controllers\RentController::class, 'index'])->name('rents_objects');

  //lock_list
  Route::get('/lockList', [\App\Http\Controllers\LockController::class, 'lockList'])->name('lockList');
  Route::post('/lockList_refresh', [\App\Http\Controllers\LockController::class, 'lockList_refresh'])->name('lockList_refresh');



  //settings
  Route::get('/credential', [\App\Http\Controllers\SettingsController::class, 'show'])->name('settings');
  Route::get('/realty', [\App\Http\Controllers\SettingsController::class, 'realty'])->name('realty');
  Route::post('/refresh_token', [\App\Http\Controllers\SettingsController::class, 'refreshToken'])->name('refreshToken');
  Route::post('/save_credential', [\App\Http\Controllers\SettingsController::class, 'saveCredential'])->name('saveCredential');
  Route::post('/refresh_realty_key', [\App\Http\Controllers\SettingsController::class, 'refreshKey'])->name('refreshKey');


  //tarifs
  Route::get('/tarifs', [\App\Http\Controllers\TarifsController::class, 'index'])->name('tarifs');



  //youkassa
  Route::post('/yookassa', [\App\Http\Controllers\YouKassaController::class, 'webhook'])->name('webhook.yookassa');
  Route::post('/pay', [\App\Http\Controllers\YouKassaController::class, 'pay'])->name('webhook_pay');


  //  api

  Route::post('/v1/get_lock_list', [\App\Http\Controllers\CallbackApiController::class, 'getLockList'])->name('getLockList');
  Route::post('/v1/open_lock', [\App\Http\Controllers\CallbackApiController::class, 'openLock'])->name('openLock');
  Route::post('/v1/get_codes_list', [\App\Http\Controllers\CallbackApiController::class, 'getCodesList'])->name('getCodesList');
  Route::post('/v1/delete_code_from_lock', [\App\Http\Controllers\CallbackApiController::class, 'deleteCode'])->name('deleteKey');
  Route::post('/v1/add_code_to_lock', [\App\Http\Controllers\CallbackApiController::class, 'addCodeToLock'])->name('addCodeToLock');



  // Route::post('/v1/get_job_result/{job_id}', [\App\Http\Controllers\CallbackApiController::class, 'getJobResult'])->name('getJobResult');
});





/*

Route::get('/test', function ()
{
            $servise =  new \App\Services\TTLockService(App\Models\User::find(6));
           // return  $servise->getLockDetails(\App\Models\Lock::find(5));
           // return  $servise->getLockElectricQuantity(\App\Models\Lock::find(5));
            return  $servise->getLockOpenState(\App\Models\Lock::find(5));
            
}
);*/



require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
