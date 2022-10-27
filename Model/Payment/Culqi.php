<?php

namespace Culqi\Pago\Model\Payment;

include_once dirname(__FILE__, 3) . '/libraries/culqi-php/lib/culqi.php';

use Laminas\Http\Client;
use Laminas\Http\Request;
use Laminas\Json\Json;

class Culqi extends \Magento\Payment\Model\Method\AbstractMethod
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

    public function crearCargo(
        $amount,
        $currencyCode,
        $email,
        $source_id
    ) {

        $this->_private_key = $this->storeConfig->getLlaveSecreta();

        $culqi = new Culqi\Culqi(array('api_key' => $this->_private_key));

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
