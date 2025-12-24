<?php

namespace App\Services;

use App\Models\User;
use YooKassa\Client;
use YooKassa\Model\Payment\ConfirmationType;
use YooKassa\Request\Payments\CreatePaymentRequest;
use YooKassa\Request\Payments\CreatePaymentRequestBuilder;

class YooKassaPaymentService implements YooKassaInterface
{
  public Client $client;
  public User $user;
  public CreatePaymentRequestBuilder $builder;
  public function __construct()
  {
    $this->client = new Client();
    $this->client->setAuth(config('app.yookassa_shop_id'), config('app.yookassa_secret_key'));
    $this->builder = CreatePaymentRequest::builder();
  }

  public function setUser(User $user): YooKassaPaymentService
  {
    $this->user = $user;

    return $this;
  }

  public function setPaymentMethods(array $data): YooKassaPaymentService
  {
    $this->builder->setPaymentMethodData($data);

    return $this;
  }

  public function setMetaData(array $meta): YooKassaPaymentService
  {
    $this->builder->setMetadata($meta);

    return $this;
  }

  public function setAmount($amount): YooKassaPaymentService
  {
    $this->builder->setAmount($amount);

    return $this;
  }

  public function setCurrency(): YooKassaPaymentService
  {
    $this->builder->setCurrency(\YooKassa\Model\CurrencyCode::RUB);

    return $this;
  }

  public function setCapture(bool $value): YooKassaPaymentService
  {
    $this->builder->setCapture($value);

    return $this;
  }

  public function setDescription(string $description): YooKassaPaymentService
  {
    $this->builder->setDescription($description);

    return $this;
  }

  public function setConfirmation(string $link): YooKassaPaymentService
  {
    $this->builder->setConfirmation([
      'type' => ConfirmationType::REDIRECT,
      'returnUrl' => $link
    ]);

    return $this;
  }

  public function setReceiptEmail(string $email): YooKassaPaymentService
  {
    $this->builder->setReceiptEmail($email);

    return $this;
  }

  public function setReceiptPhone(string $phone): YooKassaPaymentService
  {
    $this->builder->setReceiptPhone($phone);

    return $this;
  }

  public function setReceiptItems(string $name, int $amount): YooKassaPaymentService
  {
    $this->builder->addReceiptItem(
      $name,
      $amount,
      1,
      1,
      \YooKassa\Model\Receipt\PaymentMode::FULL_PAYMENT,
      \YooKassa\Model\Receipt\PaymentSubject::SERVICE
    );

    return $this;
  }

  public function makeConfirmationUrl(): ?string
  {
    // Создаем объект запроса
    $requestBuilder = $this->builder->build();

    $idempotenceKey = uniqid('', true);
    $response = $this->client->createPayment($requestBuilder, $idempotenceKey);

    //получаем confirmationUrl для дальнейшего редиректа
    return $response->getConfirmation()->getConfirmationUrl() ?? null;
  }
}
