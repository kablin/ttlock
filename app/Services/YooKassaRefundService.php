<?php

namespace App\Services;

use YooKassa\Client;
use YooKassa\Request\Refunds\CreateRefundRequest;
use YooKassa\Request\Refunds\CreateRefundRequestBuilder;

class YooKassaRefundService
{
    public Client $client;
    public CreateRefundRequestBuilder $builder;

    public function __construct()
    {
        $this->client = new Client();
        $this->client->setAuth(config('app.yookassa_shop_id'), config('app.yookassa_secret_key'));
        $this->builder = CreateRefundRequest::builder();
    }

    public function setPaymentId(string $paymentId): YooKassaRefundService
    {
        $this->builder->setPaymentId($paymentId);

        return $this;
    }

    public function setDescription(string $description): YooKassaRefundService
    {
        $this->builder->setDescription($description);

        return $this;
    }

    public function setAmount($amount): YooKassaRefundService
    {
        $this->builder->setAmount($amount);

        return $this;
    }

    public function setCurrency(): YooKassaRefundService
    {
        $this->builder->setCurrency(\YooKassa\Model\CurrencyCode::RUB);

        return $this;
    }

    public function setReceiptItems(array $items): YooKassaRefundService
    {
        foreach ($items as $item) {
            $name = $item['product']['name'];
            if ($item['size']) {
                $name .= sprintf("<br>%s %s %sсм", $item['size']['name'], $item['size']['width'], $item['size']['length']);
            }
            if ($item['product']['height']) {
                $name .= "<br>" . $item['product']['height'] . 'см';
            }
            if ($item['textile']) {
                $name .= "<br>" . $item['textile']['name'];
            }

            $this->builder->addReceiptItem(
                $name,
                $item['cart_price'] * $item['quantity'],
                $item['quantity'],
                1,
                'full_payment',
            );
        }

        return $this;
    }


    public function addAssemblyItem(int $assembly_price): YooKassaRefundService
    {
        $this->builder->addReceiptItem(
            'Сборка товаров',
            $assembly_price,
            1,
            1,
            'full_payment'
        );

        return $this;
    }

    public function addReceiptShipping(int $delivery_price): YooKassaRefundService
    {
        $this->builder->addReceiptShipping(
            'Доставка',
            $delivery_price,
            1,
            \YooKassa\Model\Receipt\PaymentMode::FULL_PAYMENT,
            \YooKassa\Model\Receipt\PaymentSubject::SERVICE
        );

        return $this;
    }

    public function setReceiptEmail(string $email): YooKassaRefundService
    {
        $this->builder->setReceiptEmail($email);

        return $this;
    }

    public function setReceiptPhone(string $phone): YooKassaRefundService
    {
        $this->builder->setReceiptPhone($phone);

        return $this;
    }

    public function makeRefund()
    {
//        $this->builder
        $request = $this->builder->build();
        $idempotenceKey = uniqid('', true);
        $this->client->createRefund($request, $idempotenceKey);
    }
}