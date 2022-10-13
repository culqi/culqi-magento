<?php

namespace Culqi\Pago\Model\Payment;

include_once dirname(__FILE__, 3) . '/libraries/culqi-php/lib/culqi.php';

use Laminas\Http\Client;
use Laminas\Http\Request;
use Laminas\Json\Json;

class Method extends \Magento\Payment\Model\Method\AbstractMethod
{
    protected $resultJsonFactory;
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
        $currencyCode,
        $email,
        $source_id
    ) {

        $this->_private_key = $this->storeConfig->getLlaveSecreta();

        $culqi = new Culqi(array('api_key' => $this->_private_key));

        try {
            $args_charge = array(
                'amount' => $amount,
                'currency_code' => $currencyCode,
                'email' => $email,
                'source_id' => $source_id,
                'capture' => false
            );

            $culqi_charge = $culqi->Charges->create($args_charge);
            
            return $culqi_charge;

        } catch (Exception $e) {
            return $e->getMessage();
        }

    }
}
