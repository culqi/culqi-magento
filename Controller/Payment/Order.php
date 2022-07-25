<?php

namespace Culqi\Pago\Controller\Payment;



use Zend\Json\Json;

use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;

class Order extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface
{
    protected $_checkoutSession;
    protected $request;
    protected $culqiopera;
    protected $order;
    protected $logger;
    protected $storeConfig;
    //protected $orderRepo;
    protected $_block;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Sales\Model\Order $order,
        \Culqi\Pago\Model\Payment\Method $culqiopera,
        //\Culqi\Pago\Block\Payment\Success $block,
        //\Magento\Sales\Api\Data\OrderInterface $orderInterface,
        \Psr\Log\LoggerInterface $logger,
        //\Magento\Sales\Api\OrderRepositoryInterface $orderRepo,
        \Culqi\Pago\Block\Payment\Redirect $storeConfig
        //\Culqi\Culqi $culqilib
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->request = $request;
        $this->culqiopera = $culqiopera;
        $this->order = $order;
        $this->logger = $logger;
        $this->storeConfig = $storeConfig;
        //$this->orderRepo = $orderRepo;
        //$this->_block = $block;
        //$this->orderInterface = $orderInterface;
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
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        // Reception of Post parameters
        $orderId = $this->_checkoutSession->getOrderId(); 
        $total = $this->_checkoutSession->getAmount();
        $currencyCode = $this->_checkoutSession->getCurrencyCode();
        $description = $this->_checkoutSession->getDescription();
        $email = $this->_checkoutSession->getEmail();
        $firstName = $this->_checkoutSession->getFirstName();
        $lastName = $this->_checkoutSession->getLastName();
        $phone = $this->_checkoutSession->getPhoneNumber();

        $amount = number_format($total, 2, '', '');
        $timexp = $this->storeConfig->getTimeExpiration();

        $expiration_date = time() + $timexp * 60 * 60;
        //var_dump($orderId); exit(1);
        //$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        //$orderToSet = $objectManager->create('\Magento\Sales\Api\Data\OrderInterface')->load($orderId);

//$orderToSet = $objectManager->create('\Magento\Sales\Model\OrderRepository')->get($orderId);   
        //$orderToSet = $this->orderRepo->get($orderId);
        //$orders = $this->order->load($orderId);
        //var_dump($orderToSet); exit(1);
        //var_dump($this->_checkoutSession->getOrderId()); exit(1);
        //echo('hoi'); exit(1);
        
       /*  //$source_id = $this->getRequest()->getPost('token_id');
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
        $string = substr($string, 0, 15); */

        // ======================
        //var_dump($this->culqiopera); exit(1);
        // Culqi functions
        // Create Cargo
        $order = $this->crearOrder(
            // $amount,
            // $currencyCode,
            // $emailCulqi,
            // $source_id,
            // $device,
            // $parameters3DS
            $orderId,
            $amount,
            $currencyCode,
            $description,
            $email,
            $firstName,
            $lastName,
            $phone,
            $expiration_date
        );
        //$orderToSet->addStatusHistoryComment('Order Id: ' . $order . '.' );
        //$orderToSet->save();
        $this->getResponse()->setBody(Json::encode($order));
    }

    private function crearOrder(
        $orderId,
        $amount,
        $currencyCode,
        $description,
        $email,
        $firstname,
        $lastname,
        $phone,
        $expiration_date
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
            /* $args_charge = array(
                'amount' => $amount,
                'currency_code' => $currencyCode,
                'email' => $email,
                'source_id' => $source_id,
                'capture' => false,
                'enviroment' => $this->_enviroment,
                'antifraud_details' => array('device_finger_print_id'=>$device)
            ); */

            $args_order = array(
             
                'amount' => $amount,
                'currency_code' => $currencyCode,
                //'description' => $description,
                'description' => 'Venta desde Plugin Magento',
                //'order_number' => (string)$orderId . $expiration_date,
                'order_number' => 'mgt-' . $expiration_date,
                'client_details' => array(
                    'email' => $email,
                    'first_name' => $firstname,
                    'last_name' => $lastname,
                    'phone_number' => $phone
                ),
                'expiration_date' => $expiration_date,
                'confirm' => false,
                'enviroment' => $this->_enviroment,
                'metadata' => ["order_id" => $orderId, "sponsor" => "magento"]
    
            );
            //var_dump($args_order); exit(1);
            $culqi_order = $culqi->Orders->create( $args_order );
            //var_dump($culqi_order); exit(1);

            return $culqi_order->id;

        } catch (Exception $e) {
            return $e->getMessage();
        } 

    }
}
