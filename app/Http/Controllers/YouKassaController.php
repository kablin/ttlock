<?php

namespace App\Http\Controllers;

use App\Services\YooKassaWebhookService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use YooKassa\Model\Notification\NotificationEventType;
use Illuminate\Support\Facades\App;
use App\Models\YouKassaTransaction;
use App\Models\YouKassaTransactionType;
use App\Models\User;
use App\Models\Tarif;
use App\Services\YooKassaPaymentService;
use Illuminate\Support\Facades\DB;
use App\Models\LockApiLog;
use Illuminate\Support\Facades\Validator;


class YouKassaController extends Controller
{
  public function webhook(Request $request, YooKassaWebhookService $service)
  {
    $factory = new \YooKassa\Model\Notification\NotificationFactory();
    $notificationObject = $factory->factory($request->all());
    $responseObject = $notificationObject->getObject();
    switch ($notificationObject->getEvent()) {
      case NotificationEventType::PAYMENT_SUCCEEDED:
        $service->paymentSucceded($responseObject);
        break;
      case NotificationEventType::PAYMENT_CANCELED:
        $service->paymentCanceled($responseObject);
        break;
      case NotificationEventType::REFUND_SUCCEEDED:
        $service->refundSucceded($responseObject['payment_id']);
        break;
      default:
        throw ValidationException::withMessages(['message' => 'Тип платежа не найден']);
    }
  }



  public function pay(Request $request)
  {


    LockApiLog::create([
      'is_ttlock_result' => false,
      'api_method' => 'pay',
      'user_id' => auth()->user()->id,
      'ip' => json_encode($request->ip()),
      'params' => json_encode($request->all()),
    ]);

    $validator = Validator::make($request->all(), [
      //  'amount'  => 'required|integer',
      'tarif_id' => 'required|integer|exists:tarifs,id',

    ]);

    $validated = $validator->safe()->only([ 'tarif_id']);

    $tarif = Tarif::find($validated['tarif_id']);

    if ($validator->fails()) {
      return response()->json(['status' => false], 400);
    }

    $service = new YooKassaPaymentService();

    DB::beginTransaction();
    try {
      $transaction = YouKassaTransaction::create([
        'you_kassa_transaction_types_id' => YouKassaTransactionType::PAYMENT_PENDING,
        'amount' =>$tarif->price,
        'test' => App::environment('local') || App::environment('development'),
        'user_id' =>  auth()->user()->id,
      ]);  // Здесь нужно инициализировать транзакцию

      $service->setAmount($tarif->price)
        ->setCurrency()
        ->setCapture(true)
        ->setConfirmation(route('dashboard'))
        ->setDescription("Оплата использования приложения «TTLock»")
        ->setMetadata([
          'user_id'        => auth()->user()->id,
          'transaction_id' => $transaction->id,
        ])
        ->setPaymentMethods([
          'type' => 'sbp',
        ])
        ->setReceiptEmail(auth()->user()->email)
        ->setReceiptPhone(auth()->user()->phone)
        ->setReceiptItems("Право использования приложения «TTLock» для бесконтактного заселения, без НДС", $tarif->price);

      $confirmation_url = $service->makeConfirmationUrl();
      DB::commit();

      // Перенаправление на страницу подтверждения оплаты
      return response()->json(['status' => true, 'url'=>$confirmation_url], 200);
      //return redirect()->to($confirmation_url);
    } catch (\Exception $exception) {
      DB::rollBack();
      info($exception->getMessage());
      return response()->json(['status' => false], 400);
    //  $this->alert('error', 'Ошибка при выполнении оплаты: ' . $exception->getMessage());
    }
  }
}
