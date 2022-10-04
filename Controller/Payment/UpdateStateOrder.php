<?php

namespace Culqi\Pago\Controller\Payment;



use Zend\Json\Json;

use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;

class UpdateStateOrder extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface
{
    protected $_checkoutSession;
    protected $request;
    protected $culqiopera;
    protected $order;
    protected $logger;
    protected $storeConfig;
    protected $statusProcessing = \Magento\Sales\Model\Order::STATE_PROCESSING;

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
        $order = $this->getRequest()->getPost('order');
        $orderId = $this->getRequest()->getPost('order_id');
        $cip = $this->getRequest()->getPost('cip');
        $culqi_order_id = $this->getRequest()->getPost('order_culqi');
        
        // === Load Order Data ===
        $orders = $this->order->loadByIncrementId($orderId);
        $orders->addStatusHistoryComment('Order Id: <b>' . $culqi_order_id . '.</b>' );
        $orders->save();

        $orderToSet = $this->order->loadByIncrementId($orderId);
        $orderToSet->setState($this->statusProcessing)->setStatus($this->statusProcessing);
        $orderToSet->addStatusHistoryComment('Culqi CIP Code: ' . $cip . '.' );
        $orderToSet->save();

        $this->getResponse()->setBody(Json::encode('OK'));
    }

}
