<?php

namespace Culqi\Pago\Controller\Payment;

use Magento\Framework\Controller\Result\JsonFactory;

class RestoreCart extends \Magento\Framework\App\Action\Action
{
    protected $checkoutSession;
    protected $resultJsonFactory;
    protected $orderRepository;
    protected $quoteRepository;
    protected $orderManagement;
    
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        JsonFactory $resultJsonFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Sales\Api\OrderManagementInterface $orderManagement
    ) {
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->orderRepository = $orderRepository;
        $this->quoteRepository = $quoteRepository;
        $this->orderManagement = $orderManagement;
    }

    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        
        try {
            $order = $this->checkoutSession->getLastRealOrder();
            
            if ($order && $order->getId()) {
                // Cancel the pending order
                if ($order->canCancel()) {
                    $this->orderManagement->cancel($order->getId());
                }
                
                // Restore the quote
                $quote = $this->quoteRepository->get($order->getQuoteId());
                if ($quote->getId()) {
                    $quote->setIsActive(true)->setReservedOrderId(null);
                    $this->quoteRepository->save($quote);
                    $this->checkoutSession->replaceQuote($quote);
                }
            }
            
            return $resultJson->setData([
                'success' => true
            ]);
        } catch (\Throwable $e) {
            return $resultJson->setData([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}
