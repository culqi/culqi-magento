<?php

namespace Culqi\Pago\Controller\Payment;

class Redirect extends \Magento\Framework\App\Action\Action
{
    protected $culqiopera;
    protected $resultPageFactory;
    protected $_checkoutSession;
    protected $logger;
    
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Culqi\Pago\Model\Payment\Culqi $culqiopera,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->culqiopera = $culqiopera;
        $this->resultPageFactory = $resultPageFactory;
        $this->_checkoutSession = $checkoutSession;
        $this->logger = $logger;
        parent::__construct($context);
    }

    public function execute()
    {
        //var_dump($this->_checkoutSession->getData()); exit(1);
        $amount = $this->_checkoutSession->getAmount();
        $currencyCode = $this->_checkoutSession->getCurrencyCode();
        $description = $this->_checkoutSession->getDescription();
        $storeName = $this->_checkoutSession->getStoreName();
        $firstName = $this->_checkoutSession->getFirstName();
        $lastName = $this->_checkoutSession->getLastName();
        $phoneNumber = $this->_checkoutSession->getPhoneNumber();
        $email = $this->_checkoutSession->getEmail();
        $orderId = $this->_checkoutSession->getOrderId();
        $activeMultiPay = $this->_checkoutSession->getActiveMultiPay();

        $data =  [
            'amount' => $amount,
            'currency_code' => $currencyCode,
            'description' => $description,
            'store_name' => $storeName,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'phone_number' => $phoneNumber,
            'email' => $email,
            'orderId' => $orderId
        ];
    
        $page = $this->resultPageFactory->create();
        $block = $page->getLayout()->getBlock('payment.redirect');
        if ($activeMultiPay) {
            $orderReq = $this->culqiopera->createOrder(
                $amount,
                $currencyCode,
                $description,
                $storeName,
                $firstName,
                $lastName,
                $phoneNumber,
                $email,
                $orderId
            );
            $this->logger->debug("Order response ==> ".$orderReq);
            $orderReq = json_decode($orderReq, true);
            $block->setData('orderIdCq', $orderReq['id']);
        }

        $block->setData('dataOrder', $data);
        $block->setData('activeMultiPay', $activeMultiPay);
        return $page;
    }
}
