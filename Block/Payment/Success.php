<?php

namespace Culqi\Pago\Block\Payment;

class Success extends \Magento\Checkout\Block\Onepage\Success
{
    protected $checkoutSession;
    protected $customerSession;
    protected $_orderFactory;
    
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $checkoutSession, $orderConfig, $httpContext);
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->_orderFactory = $orderFactory;
    }

    public function getOrder()
    {
        return  $this->_order = $this->_orderFactory->create()->loadByIncrementId(
            $this->checkoutSession->getLastRealOrderId()
        );
    }

    public function getCustomerId()
    {
        return $this->customerSession->getCustomer()->getId();
    }
}
