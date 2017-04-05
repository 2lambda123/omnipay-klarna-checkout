<?php

namespace MyOnlineStore\Omnipay\KlarnaCheckout\Message;

use Klarna\Rest\Checkout\Order;

/**
 * Creates a Klarna Checkout order if it does not exist
 */
final class AuthorizeRequest extends AbstractRequest
{
    use ItemDataTrait;

    /**
     * @inheritDoc
     */
    public function getData()
    {
        $this->validate(
            'amount',
            'currency',
            'items',
            'locale',
            'notifyUrl',
            'returnUrl',
            'tax_amount',
            'terms_url'
        );

        return [
            'locale' => str_replace('_', '-', $this->getLocale()),
            'order_amount' => $this->getAmountInteger(),
            'order_tax_amount' => $this->toCurrencyMinorUnits($this->getTaxAmount()),
            'order_lines' => $this->getItemData($this->getItems()),
            'merchant_urls' => [
                'checkout' => $this->getReturnUrl(),
                'confirmation' => $this->getReturnUrl(),
                'push' => $this->getNotifyUrl(),
                'terms' => $this->getTermsUrl(),
            ],
            'purchase_country' =>  explode('_', $this->getLocale())[1],
            'purchase_currency' => $this->getCurrency(),
        ];
    }

    /**
     * @return string
     */
    public function getRenderUrl()
    {
        return $this->getParameter('render_url');
    }

    /**
     * @return string
     */
    public function getTermsUrl()
    {
        return $this->getParameter('terms_url');
    }

    /**
     * @inheritDoc
     */
    public function sendData($data)
    {
        $order = new Order($this->getConnector(), $this->getTransactionReference());

        if (!$this->getTransactionReference()) {
            $order->create($data);
        }

        $order->fetch();

        return new AuthorizeResponse($this, $order, $this->getRenderUrl());
    }

    /**
     * @param string $url
     *
     * @return $this
     */
    public function setRenderUrl($url)
    {
        $this->setParameter('render_url', $url);

        return $this;
    }

    /**
     * @param string $url
     *
     * @return $this
     */
    public function setTermsUrl($url)
    {
        $this->setParameter('terms_url', $url);

        return $this;
    }
}
