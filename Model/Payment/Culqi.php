<?php

namespace Culqi\Pago\Model\Payment;

use Zend\Http\Client;
use Zend\Http\Request;
use Zend\Json\Json;

class Culqi extends \Magento\Payment\Model\Method\AbstractMethod
{
    protected $resultJsonFactory;
    protected $_url_base = "https://api.culqi.com/v2";
    protected $storeConfig;
    protected $_private_key;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Culqi\Pago\Block\Payment\Redirect $storeConfig
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->storeConfig = $storeConfig;
    }

    public function createOrder(
        $amount,
        $currencyCode,
        $description,
        $storeName,
        $firstName,
        $lastName,
        $phoneNumber,
        $email,
        $orderId
    ) {
        $result = $this->resultJsonFactory->create();

        $prefixOrder = 'MGT';
        $actualPrefix = $this->storeConfig->getPrefix();

        $hoursExpiration = $this->storeConfig->getDuracionMaxima();

        if (!empty($actualPrefix)) {
            $prefixOrder = $actualPrefix;
        }

        $listOfCharacters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $randomString = str_shuffle($listOfCharacters);
        $randomString = substr($randomString, 0, 10);

        $total = number_format($amount, 2, '', '');

        $data =  [
            'amount' => $total,
            'currency_code' => $currencyCode,
            'description' => $description,
            'order_number' => $prefixOrder.'-'.$randomString,
            'client_details'=> [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'phone_number' => $phoneNumber,
                    'email' => $email
            ],
            'expiration_date' => time() + $hoursExpiration * 60 * 60,
            'confirm' => false,
            'metadata' => ["mgt_order_id" => $orderId]
        ];

        $this->_private_key = $this->storeConfig->getLlaveSecreta();

        $client = new Client();
        $client->setUri($this->_url_base."/orders/");
        $client->setOptions(['timeout' => 120]);
        $client->setHeaders([
            'Authorization' => "Bearer ".$this->_private_key,
            'Content-Type' => "application/json"]);
        $client->setRawBody(Json::encode($data));
        $client->setMethod('POST');
        $json = $client->send()->getBody();
        return $json;
    }

    public function crearCargo(
        $amount,
        $address,
        $addressCity,
        $countryCode,
        $firstName,
        $lastName,
        $phoneNumber,
        $currencyCode,
        $description,
        $installments,
        $email,
        $source_id,
        $orderId
    ) {

        $data =  [
            'amount' => $amount,
            'antifraud_details'=> [
            'address' => $address,
            'address_city' => $addressCity,
            'country_code' => $countryCode,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'phone_number' => $phoneNumber
            ],
            'capture' => true,
            'currency_code' => $currencyCode,
            'description' => $description,
            'installments' => $installments,
            'metadata' => ["order_id" => (string) $orderId],
            'email' => $email,
            'source_id' => $source_id
        ];

        $this->_private_key = $this->storeConfig->getLlaveSecreta();

        $client = new Client();
        $client->setUri($this->_url_base."/charges/");
        $client->setOptions(['timeout' => 120]);
        $client->setHeaders([
            'Authorization' => "Bearer ".$this->_private_key,
            'Content-Type' => "application/json"]);
        $client->setRawBody(Json::encode($data));
        $client->setMethod('POST');
        $json = $client->send()->getBody();
        return $json;
    }
}
