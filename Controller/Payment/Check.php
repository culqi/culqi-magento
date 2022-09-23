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
            $parameters3DS,
            $firstName,
            $lastName,
            $countryCode,
            $addressCity,
            $address,
            $phoneNumber
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
        $parameters3DS,
        $firstName,
        $lastName,
        $countryCode,
        $addressCity,
        $address,
        $phoneNumber
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
            $antifraud_charges = array();
            if(isset($firstName) and !empty($firstName) and !is_null($firstName) and $firstName!=''){
                $antifraud_charges['first_name']=$firstName;
            }
            if(isset($lastName) and !empty($lastName) and !is_null($lastName) and $lastName!=''){
                $antifraud_charges['last_name']=$lastName;
            }
            if(isset($address) and !empty($address) and !is_null($address) and $address!=''){
                $antifraud_charges['address']=$address;
            }
            if(isset($addressCity) and !empty($addressCity) and !is_null($addressCity) and $addressCity!=''){
                $antifraud_charges['address_city']=$addressCity;
            }
            if(isset($countryCode) and !empty($countryCode) and !is_null($countryCode) and $countryCode!=''){
                $antifraud_charges['country_code']=$countryCode;
            }
            if(isset($phoneNumber) and !empty($phoneNumber) and !is_null($phoneNumber) and $phoneNumber!=''){
                $antifraud_charges['phone_number']=$phoneNumber;
            }
            $antifraud_charges['device_finger_print_id']=$device;
            $args_charge = array(
                'amount' => $amount,
                'currency_code' => $currencyCode,
                'email' => $email,
                'source_id' => $source_id,
                'capture' => true,
                'enviroment' => $this->_enviroment,
                'antifraud_details' => $antifraud_charges,
                'metadata' => ["order_id" => (string) $orderId, "sponsor" => "magento"],
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
