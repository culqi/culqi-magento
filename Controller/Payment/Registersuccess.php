<?php

namespace Culqi\Pago\Controller\Payment;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session;
use Magento\Framework\Controller\Result\JsonFactory;

class Registersuccess extends Action
{
    protected $checkoutSession;
    protected $resultJsonFactory;

    public function __construct(
        Context $context,
        Session $checkoutSession,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    public function execute()
    {
        $this->checkoutSession->setSuccess(true);
        
        $result = $this->resultJsonFactory->create();
        return $result->setData(['success' => true]);
    }
}
