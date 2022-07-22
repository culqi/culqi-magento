<?php

namespace Culqi\Pago\Controller\Payment;

use Magento\Framework\Controller\ResultFactory;

use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;

class Gateway extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface
{
    protected $_checkoutSession;
    protected $request;
    protected $order;
    protected $logger;
    protected $statusCompleted = \Magento\Sales\Model\Order::STATE_COMPLETE;
    protected $statusProcessing = \Magento\Sales\Model\Order::STATE_PROCESSING;
    protected $statusCanceled = \Magento\Sales\Model\Order::STATE_CANCELED;
    protected $statusHolded = \Magento\Sales\Model\Order::STATE_HOLDED;
    protected $statusPending = \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT;

    protected $url;
    protected $resultRedirect;
    protected $quoteFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Magento\Framework\Controller\ResultFactory $result,
        \Magento\Framework\UrlInterface $url,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Quote\Model\QuoteFactory $quoteFactory
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->request = $request;
        $this->order = $order;
        $this->resultRedirect = $result;
        $this->url = $url;
        $this->logger = $logger;
        $this->quoteFactory = $quoteFactory;
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
        $result = $this->responseAction();
        return $result;
    }
    
    /* Response Processing */
    public function responseAction()
    {
        // If all is right !
        if ($this->getRequest()->get("orderId") && $this->getRequest()->get("statusOrder") == 'complete_payment') {
            $orderId = $this->getRequest()->get("orderId");
            $card_number = $this->getRequest()->get("card_number");
            $card_brand = $this->getRequest()->get("card_brand");
            $culqi_order_id = $this->getRequest()->get("order_id");
 
            $orderToSet = $this->order->loadByIncrementId($orderId);
            $orderToSet->setState($this->statusCompleted)->setStatus($this->statusCompleted);
            $orderToSet->addStatusToHistory(
                $orderToSet->getStatus(),
                "Venta correcta vía Tarjeta de Crédito (Culqi), espera a su entrega y confirmación."
            );
            $orderToSet->addStatusHistoryComment('Card number: ' . $card_number . ' Card brand: ' . $card_brand );
            $orderToSet->addStatusHistoryComment('Charge Id: ' . $culqi_order_id . '.' );
            $orderToSet->save();

            $resultRedirect = $this->resultRedirect->create(ResultFactory::TYPE_REDIRECT);
            $this->_checkoutSession->setSuccess(true);
            $resultRedirect->setUrl($this->url->getUrl('pago/payment/success'));

            return $resultRedirect;
        } elseif ($this->getRequest()->get("orderId") && $this->getRequest()->get("statusOrder") == 'order'){
            $orderId = $this->getRequest()->get("orderId");
            $culqi_order_id = $this->getRequest()->get("order_id");
            //var_dump($this->getRequest()->get("culqi_order_id")); exit(1);
            $orderToSet = $this->order->loadByIncrementId($orderId);
            $orderToSet->setState($this->statusProcessing)->setStatus($this->statusProcessing);
            $orderToSet->addStatusToHistory(
                $orderToSet->getStatus(),
                "Venta correcta (Culqi), espera a su entrega y confirmación."
            );
            //$orderToSet->addStatusHistoryComment('Order Id: ' . $culqi_order_id . '.' );
            $orderToSet->save();

            $resultRedirect = $this->resultRedirect->create(ResultFactory::TYPE_REDIRECT);
            $this->_checkoutSession->setSuccess(true);
            $resultRedirect->setUrl($this->url->getUrl('pago/payment/success'));

            return $resultRedirect;
        } elseif ($this->getRequest()->get("orderId") && $this->getRequest()->get("statusOrder") == 'fail') {
            $orderId = $this->getRequest()->get("orderId");

            if ($orderId) {
                $orderToSet = $this->order->loadByIncrementId($orderId);
                $orderToSet->setState($this->statusCanceled)->setStatus($this->statusCanceled);
                $orderToSet->addStatusToHistory(
                    $orderToSet->getStatus(),
                    "La orden no fue completada por problemas en el pago."
                );
                $orderToSet->save();
            }

            //Return sesion
            //Return quote
            $quote = $this->quoteFactory->create()->load($orderToSet->getQuoteId());
            if ($quote && $quote->getId()) {
                $quote->setIsActive(true)->setReservedOrderId(null)->save();
                $this->_checkoutSession->replaceQuote($quote);
            }
            $resultRedirect = $this->resultRedirect->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->url->getUrl('checkout/cart/'));
            return $resultRedirect;
        } elseif ($this->getRequest()->get("orderId") && $this->getRequest()->get("statusOrder") == 'pending_payment') {

            $orderId = $this->getRequest()->get("orderId");
            
            $orderToSet = $this->order->loadByIncrementId($orderId);
            $orderToSet->setState($this->statusHolded)->setStatus($this->statusHolded);
            $orderToSet->addStatusToHistory(
                $orderToSet->getStatus(),
                "Orden en espera de pago por medio de PagoEfectivo."
            );
            $orderToSet->save();
    
            $resultRedirect = $this->resultRedirect->create(ResultFactory::TYPE_REDIRECT);
            $this->_checkoutSession->setSuccess(true);
            $resultRedirect->setUrl($this->url->getUrl('pago/payment/success'));

            return $resultRedirect;
        } elseif ($this->getRequest()->get("orderId") && $this->getRequest()->get("statusOrder") == 'cancelado_por_usuario') {
                
            $orderId = $this->getRequest()->get("orderId");

            if ($orderId) {
                $orderToSet = $this->order->loadByIncrementId($orderId);
            }

            //Return sesion
            //Cancel order
            $orderToSet->setState($this->statusCanceled)->setStatus($this->statusCanceled);
            $orderToSet->addStatusToHistory($orderToSet->getStatus(), "Cancelado por el usuario.");
            $orderToSet->save();

            //Return quote
            $quote = $this->quoteFactory->create()->load($orderToSet->getQuoteId());
            if ($quote && $quote->getId()) {
                $quote->setIsActive(true)->setReservedOrderId(null)->save();
                $this->_checkoutSession->replaceQuote($quote);
            }
            $resultRedirect = $this->resultRedirect->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->url->getUrl('checkout/cart/'));
            return $resultRedirect;
        } else {
            $resultRedirect = $this->resultRedirect->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->url->getUrl('checkout/onepage/failure'));
            return $resultRedirect;
        }
    }
}
