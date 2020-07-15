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

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Sales\Model\Order $order,
        \Culqi\Pago\Model\Payment\Culqi $culqiopera,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->request = $request;
        $this->culqiopera = $culqiopera;
        $this->order = $order;
        $this->logger = $logger;
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
        $source_id = $this->getRequest()->getPost('token_id');
        $orderId = $this->getRequest()->getPost('order_id');
        $installments = $this->getRequest()->getPost('installments');

        // === Load Order Data ===
        $orders = $this->order->loadByIncrementId($orderId);

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

        // Culqi functions
        // Create Cargo
        $cargo = $this->culqiopera->crearCargo(
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
        );

        $this->logger->debug("Cargo response ==> ".$cargo);
        $this->getResponse()->setBody(Json::encode($cargo));
    }
}
