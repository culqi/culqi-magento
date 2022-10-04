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
    protected $_block;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Sales\Model\Order $order,
        \Culqi\Pago\Model\Payment\Method $culqiopera,
        \Psr\Log\LoggerInterface $logger,
        \Culqi\Pago\Block\Payment\Redirect $storeConfig
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

        $order = $this->crearOrder(
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
        $this->_private_key = $this->storeConfig->getLlaveSecreta();
        $this->_enviroment = $this->storeConfig->getURLEnviroment();
        $culqi = new \Culqi\Culqi(array('api_key' => $this->_private_key ));

        try {
            $args_order = array(
             
                'amount' => $amount,
                'currency_code' => $currencyCode,
                'description' => 'Venta desde Plugin Magento',
                'order_number' => 'mgt-' . time(),
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
            $culqi_order = $culqi->Orders->create( $args_order );

            return $culqi_order->id;

        } catch (Exception $e) {
            return $e->getMessage();
        } 

    }
}
