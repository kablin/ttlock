<?php

namespace App\Services;

use App\Models\YouKassaTransaction;
use App\Models\YouKassaTransactionType;
use Illuminate\Support\Facades\DB;

class YooKassaWebhookService
{

public  function toFixed($number, $decimals): float
  {
    return (float) number_format($number, $decimals, '.', "");
  }



   public function paymentSucceded($response_object)
   {
     $metadata = $response_object->getMetadata();
     $transaction_id = $metadata['transaction_id'];

     if ($transaction = YouKassaTransaction::find($transaction_id)) {
       DB::beginTransaction();
       try {
         $response_amount = $response_object['amount']['value'];
         $transaction->update([
           'you_kassa_transaction_types_id' => YouKassaTransactionType::PAYMENT_SUCCEEDED,
           'amount' => $this->toFixed($response_amount,0),
           'payment_id' => $response_object['payment_method']['id'],
           'payment_type' => $response_object['payment_method']['type'],
           'metadata' => json_encode($metadata),
           'card_type' => isset($response_object['payment_method']['card']) ? $response_object['payment_method']['card']['card_type'] : null,
           'issuer_country' => isset($response_object['payment_method']['card']) ? $response_object['payment_method']['card']['issuer_country'] : null,
           'issuer_name' => isset($response_object['payment_method']['card']) ? $response_object['payment_method']['card']['issuer_name'] : null,
           'income_amount' => $response_object['income_amount']['value'],
           'income_amount_currency' => $response_object['income_amount']['currency'],
           'expires_at' => $response_object['expires_at'] ?? null,
         ]);
         DB::commit();
       } catch (\Exception $exception) {
         DB::rollBack();
         info( json_encode([$transaction_id => $exception->getMessage()]));
       }
     }
   }

   public function paymentCanceled($response_object)
   {
       $metadata = $response_object->getMetadata();
       $transaction_id = $metadata['transaction_id'];

       if ($transaction = YouKassaTransaction::find($transaction_id)) {
           $transaction->update([
               'you_kassa_transaction_types_id' => YouKassaTransactionType::PAYMENT_CANCELED,
           ]);
       }
   }

   public function refundSucceded($paymentId)
   {
       if ($transaction = YouKassaTransaction::where([
           'payment_id'            => $paymentId,
           'you_kassa_transaction_types_id' => YouKassaTransactionType::REFUND_PENDING
       ])->first()) {
           $transaction->update([
               'you_kassa_transaction_types_id' => YouKassaTransactionType::REFUND_SUCCEEDED,
           ]);
       }
   }
}