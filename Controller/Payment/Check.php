<?php

namespace Culqi\Pago\Controller\Payment;



use Zend\Json\Json;

use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;

class Check extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface
{
    protected $_checkoutSession;
    protected $request;
    protected $culqiopera;
    protected $order;
    protected $logger;
    protected $storeConfig;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Sales\Model\Order $order,
        \Culqi\Pago\Model\Payment\Method $culqiopera,
        \Psr\Log\LoggerInterface $logger,
        \Culqi\Pago\Block\Payment\Redirect $storeConfig
        //\Culqi\Culqi $culqilib
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->request = $request;
        $this->culqiopera = $culqiopera;
        $this->order = $order;
        $this->logger = $logger;
        $this->storeConfig = $storeConfig;
        parent::__construct($context);
    }

    public function createCsrfValidationException(RequestInterface $request): ? InvalidRequestException
    {
        return null;
    }
        
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    public function execute()
    {
        //echo('hoi'); exit(1);
        // Reception of Post parameters
        $source_id = $this->getRequest()->getPost('token_id');
        $orderId = $this->getRequest()->getPost('order_id');
        $emailCulqi = $this->getRequest()->getPost('email');
        $installments = $this->getRequest()->getPost('installments');
        $device = $this->getRequest()->getPost('device');
        $parameters3DS = $this->getRequest()->getPost('parameters3DS');

        // === Load Order Data ===
        $orders = $this->order->loadByIncrementId($orderId);
        //var_dump($orders); exit(1);
        $total=$orders->getGrandTotal();
        $amount = number_format($total, 2, '', '');

        $currencyCode = $orders->getOrderCurrencyCode();

        $items =$orders->getAllItems();
        // product Names
        $productNames = [];
        foreach ($items as $item) {
            $productNames[]= $item->getName();
        }
        $orderId = $orders->getRealOrderId();
        
        if (count($productNames)>1) {
            $description = $orderId;
        } else {
            $description = $productNames[0];
        }

        $storeName = $orders->getStoreName();

        $firstName = $orders->getCustomerFirstname();
        $lastName = $orders->getCustomerLastname();
        $email = $orders->getCustomerEmail();

        $billingaddress = $orders->getBillingAddress();
        $addressCity = $billingaddress->getCity();
        $billingstreet = $billingaddress->getStreet();
        $phoneNumber = $billingaddress->getTelephone();
        $countryCode = 'PE';

        $address = join(" ", $billingstreet);

        // Generar un pedido aleatorio (for development - debug)
        $listOfCharacters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $string = str_shuffle($listOfCharacters);
        $string = substr($string, 0, 15);

        // ======================
        //var_dump($this->culqiopera); exit(1);
        // Culqi functions
        // Create Cargo
        $cargo = $this->crearCargo(
            $orderId,
            $amount,
            $currencyCode,
            $emailCulqi,
            $source_id,
            $device,
            $parameters3DS
        );

        $this->getResponse()->setBody(Json::encode($cargo));
    }

    private function crearCargo(
        $orderId,
        $amount,
        $currencyCode,
        $email,
        $source_id,
        $device,
        $parameters3DS
    ) {
        include_once dirname(__FILE__, 3).'/libraries/Requests/library/Requests.php';
        \Requests::register_autoloader();
        include_once dirname(__FILE__, 3) . '/libraries/culqi-php/lib/culqi.php';
        //\Requests::register_autoloader();
        $this->_private_key = $this->storeConfig->getLlaveSecreta();
        $this->_enviroment = $this->storeConfig->getURLEnviroment();
        //var_dump($this->_enviroment); exit(1);
        $culqi = new \Culqi\Culqi(array('api_key' => $this->_private_key ));

        try {
            $args_charge = array(
                'amount' => $amount,
                'currency_code' => $currencyCode,
                'email' => $email,
                'source_id' => $source_id,
                'capture' => false,
                'enviroment' => $this->_enviroment,
                'antifraud_details' => array('device_finger_print_id'=>$device),
                'metadata' => ["order_id" => (string) $orderId],
            );

            if( is_array($parameters3DS)){
                $args_charge['authentication_3DS'] = $parameters3DS;
            }

            $culqi_charge = $culqi->Charges->create($args_charge);
            
            return $culqi_charge;

        } catch (Exception $e) {
            return $e->getMessage();
        } 

    }
}
