<?php

namespace Culqi\Pago\Controller\Payment;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;
use Culqi\Pago\Helper\Data;
use Psr\Log\LoggerInterface;

class UpdateOrder extends Action implements CsrfAwareActionInterface
{
    protected $jsonSerializer;
    protected $orderRepository;
    protected $resultJsonFactory;
    protected $helper;
    protected $logger;
    protected $transactionBuilder;

    public function __construct(
        Context $context,
        Json $jsonSerializer,
        OrderRepositoryInterface $orderRepository,
        JsonFactory $resultJsonFactory,
        Data $helper,
        LoggerInterface $logger,
        BuilderInterface $transactionBuilder
    ) {
        parent::__construct($context);
        $this->jsonSerializer = $jsonSerializer;
        $this->orderRepository = $orderRepository;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->helper = $helper;
        $this->logger = $logger;
        $this->transactionBuilder = $transactionBuilder;
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();

        try {
            // Get Authorization header
            $authorization = $this->getRequest()->getHeader('Authorization');
            
            if (!$authorization) {
                $this->logger->error('Culqi Update Order: Missing Authorization header');
                return $resultJson->setData([
                    'success' => false,
                    'message' => 'Missing Authorization header'
                ])->setHttpResponseCode(401);
            }

            // Extract token from "Bearer {token}"
            $authParts = explode(' ', $authorization);
            if (count($authParts) !== 2 || $authParts[0] !== 'Bearer') {
                $this->logger->error('Culqi Update Order: Invalid Authorization format');
                return $resultJson->setData([
                    'success' => false,
                    'message' => 'Invalid Authorization format'
                ])->setHttpResponseCode(401);
            }

            $token = $authParts[1];

            // Verify JWT token
            $isVerified = $this->helper->verify_jwt_token($token);
            if (!$isVerified) {
                $this->logger->error('Culqi Update Order: Invalid or expired token');
                return $resultJson->setData([
                    'success' => false,
                    'message' => 'Invalid or expired token'
                ])->setHttpResponseCode(401);
            }

            // Get and parse request body
            $requestBody = $this->getRequest()->getContent();
            $data = $this->jsonSerializer->unserialize($requestBody);

            // Validate required fields
            if (!isset($data['orderId']) || !isset($data['status']) || !isset($data['transactionId'])) {
                $this->logger->error('Culqi Update Order: Missing required fields');
                return $resultJson->setData([
                    'success' => false,
                    'message' => 'Missing required fields: orderId, status, transactionId'
                ])->setHttpResponseCode(400);
            }

            $orderId = $data['orderId'];
            $status = $data['status'];
            $transactionId = $data['transactionId'];
            
            $this->logger->info('Culqi Update Order: Received status = ' . $status . ' for order ' . $orderId);

            // Load the order
            $order = $this->loadOrderByIncrementId($orderId);

            if (!$order || !$order->getId()) {
                $this->logger->error('Culqi Update Order: Order not found - ' . $orderId);
                return $resultJson->setData([
                    'success' => false,
                    'message' => 'Order not found'
                ])->setHttpResponseCode(404);
            }

            // Determine payment type (order or charge)
            $paymentType = $this->helper->get_payment_type($transactionId);

            // Process based on payment type
            if ($paymentType === "charge") {
                $this->processChargePayment($order, $status, $transactionId, $data);
            } else {
                $this->processOrderPayment($order, $status, $transactionId, $data);
            }

            $this->logger->info('Culqi Update Order: Successfully updated order ' . $orderId . ' to status ' . $status);

            return $resultJson->setData([
                'success' => true,
                'message' => 'Order status updated successfully'
            ])->setHttpResponseCode(200);

        } catch (\Exception $e) {
            $this->logger->error('Culqi Update Order Error: ' . $e->getMessage());
            return $resultJson->setData([
                'success' => false,
                'message' => 'Error on update order status: ' . $e->getMessage()
            ])->setHttpResponseCode(400);
        }
    }

    /**
     * Load order by increment ID
     * 
     * @param string $incrementId
     * @return Order|null
     */
    private function loadOrderByIncrementId($incrementId)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $searchCriteria = $objectManager->create('\Magento\Framework\Api\SearchCriteriaBuilder')
            ->addFilter('increment_id', $incrementId, 'eq')
            ->create();
        
        $orderList = $this->orderRepository->getList($searchCriteria)->getItems();
        
        return !empty($orderList) ? reset($orderList) : null;
    }

    /**
     * Process charge payment
     * 
     * @param Order $order
     * @param string $status
     * @param string $transactionId
     * @param array $data
     */
    private function processChargePayment($order, $status, $transactionId, $data)
    {
        // Map status to Magento order states
        $magentoStatus = $this->mapStatusToMagentoState($status);

        if ($status === "refunded") {
            $comment = "Culqi Payment Refunded:\n" .
                "Transaction Id: " . $transactionId;
            $order->addCommentToStatusHistory($comment);
        } else {
            $cardNumber = isset($data['cardNumber']) ? $data['cardNumber'] : '';
            $cardBrand = isset($data['cardBrand']) ? $data['cardBrand'] : '';
            $referenceCode = isset($data['referenceCode']) ? $data['referenceCode'] : '';

            // Reduce stock levels
            $this->reduceStockLevels($order);

            // Add order comment
            $comment = "Culqi Charge Created:\n" .
                "Id: " . $transactionId . "\n" .
                "Tarjeta: " . $cardNumber . "\n" .
                "Marca: " . $cardBrand . "\n" .
                "Cod. Referencia: " . $referenceCode;

            $order->addCommentToStatusHistory($comment);
            
            // Save transaction
            $this->saveTransaction($order, $transactionId, $data);
        }

        // Update order status
        $order->setState($magentoStatus['state'])
              ->setStatus($magentoStatus['status']);
        
        $order->addCommentToStatusHistory('Order status changed to ' . $status);
        
        $this->orderRepository->save($order);
    }

    /**
     * Process order payment
     * 
     * @param Order $order
     * @param string $status
     * @param string $transactionId
     * @param array $data
     */
    private function processOrderPayment($order, $status, $transactionId, $data)
    {
        // Map status to Magento order states
        $magentoStatus = $this->mapStatusToMagentoState($status);

        if ($status === "pending") {
            $cip = isset($data['cip']) ? $data['cip'] : '';
            $orderNumber = isset($data['orderNumber']) ? $data['orderNumber'] : '';

            $comment = "Culqi Order Created:\n" .
                "Id: " . $transactionId . "\n" .
                "CIP: " . $cip . "\n" .
                "Order Number: " . $orderNumber;

            $order->addCommentToStatusHistory($comment);
            
            // Save transaction
            $this->saveTransaction($order, $transactionId, $data);
        }

        if ($status === "processing" || $status === "completed") {
            // Reduce stock levels
            $this->reduceStockLevels($order);
        }

        // Update order status
        $order->setState($magentoStatus['state'])
              ->setStatus($magentoStatus['status']);
        
        $order->addCommentToStatusHistory('Order status changed to ' . $status);
        
        $this->orderRepository->save($order);
    }

    /**
     * Map WooCommerce/custom status to Magento order states
     * 
     * @param string $status
     * @return array
     */
    private function mapStatusToMagentoState($status)
    {
        $statusMap = [
            'pending' => ['state' => Order::STATE_PENDING_PAYMENT, 'status' => Order::STATE_PENDING_PAYMENT],
            'processing' => ['state' => Order::STATE_PROCESSING, 'status' => Order::STATE_PROCESSING],
            'completed' => ['state' => Order::STATE_PROCESSING, 'status' => Order::STATE_PROCESSING],
            'refunded' => ['state' => Order::STATE_CLOSED, 'status' => 'closed'],
            'cancelled' => ['state' => Order::STATE_CANCELED, 'status' => Order::STATE_CANCELED],
            'on-hold' => ['state' => Order::STATE_HOLDED, 'status' => Order::STATE_HOLDED],
        ];

        return isset($statusMap[$status]) ? $statusMap[$status] : ['state' => Order::STATE_PROCESSING, 'status' => Order::STATE_PROCESSING];
    }

    /**
     * Save payment transaction
     * 
     * @param Order $order
     * @param string $transactionId
     * @param array $data
     */
    private function saveTransaction($order, $transactionId, $data)
    {
        try {
            $payment = $order->getPayment();
            
            // Set transaction ID
            $payment->setTransactionId($transactionId);
            $payment->setLastTransId($transactionId);
            
            // Add additional transaction information
            $additionalInfo = [];
            if (isset($data['cardBrand'])) {
                $additionalInfo['card_brand'] = $data['cardBrand'];
            }
            if (isset($data['cardNumber'])) {
                $additionalInfo['card_number'] = $data['cardNumber'];
            }
            if (isset($data['referenceCode'])) {
                $additionalInfo['reference_code'] = $data['referenceCode'];
            }
            if (isset($data['cip'])) {
                $additionalInfo['cip'] = $data['cip'];
            }
            if (isset($data['orderNumber'])) {
                $additionalInfo['order_number'] = $data['orderNumber'];
            }
            
            if (!empty($additionalInfo)) {
                $payment->setAdditionalInformation($additionalInfo);
            }
            
            // Create transaction
            $transaction = $this->transactionBuilder
                ->setPayment($payment)
                ->setOrder($order)
                ->setTransactionId($transactionId)
                ->setFailSafe(true)
                ->build(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE);
            
            $transaction->save();
            $payment->save();
            
            $this->logger->info('Culqi Transaction saved: ' . $transactionId);
        } catch (\Exception $e) {
            $this->logger->error('Culqi Save Transaction Error: ' . $e->getMessage());
        }
    }

    /**
     * Reduce stock levels for order items
     * 
     * @param Order $order
     */
    private function reduceStockLevels($order)
    {
        try {
            // Check if stock has already been reduced
            if ($order->getInventoryProcessed()) {
                return;
            }

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            
            // Use Magento's stock management
            $stockManagement = $objectManager->get('\Magento\CatalogInventory\Api\StockManagementInterface');
            
            foreach ($order->getAllItems() as $item) {
                if (!$item->getIsVirtual() && !$item->getParentItem()) {
                    $productId = $item->getProductId();
                    $qty = $item->getQtyOrdered();
                    
                    try {
                        $stockManagement->backItemQty($productId, $qty, $order->getStore()->getWebsiteId());
                    } catch (\Exception $e) {
                        $this->logger->warning('Culqi Update Order: Could not reduce stock for product ' . $productId . ': ' . $e->getMessage());
                    }
                }
            }

            $order->setInventoryProcessed(true);
        } catch (\Exception $e) {
            $this->logger->error('Culqi Update Order: Error reducing stock levels: ' . $e->getMessage());
        }
    }
}
