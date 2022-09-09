<?php

namespace Culqi\Pago\Controller\Webhook;

use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;

class Event extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface
{
    protected $response;
    protected $order;
    protected $logger;

    protected $statusProcessing = \Magento\Sales\Model\Order::STATE_PROCESSING;
    protected $statusCanceled = \Magento\Sales\Model\Order::STATE_CANCELED;
    protected $statusCompleted = \Magento\Sales\Model\Order::STATE_COMPLETE;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->order = $order;
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
        // Recepcion del mensaje de webhook
        $inputJSON = $this->getRequest()->getContent();
        
        $input = json_decode($inputJSON);
        
        $data = json_decode($input->data);

        $this->logger->debug("Mensaje de webhook recibido");

        $this->logger->debug($data);

        if (empty($data->metadata)) {
            exit("Error: Metadata vacia");
        }

        if (empty($data->amount) || empty($data->currency_code) || empty($data->state)) {
            exit("Error: valores de la orden incorrectos");
        }

        if ($input->object == 'event' && $input->type == 'order.status.changed') {
            $mgtOrderId = (int)$data->metadata->mgt_order_id;

            $this->logger->debug('Evento de Culqi, cambio de orden identificado. Orden: '.$mgtOrderId);

            $orderToSet = $this->order->loadByIncrementId($mgtOrderId);

            if ($orderToSet && $orderToSet->getStatus() != $this->statusCompleted) {
                if ($data->state == 'paid') {
                    $this->logger->debug('Orden Pagada');
                    $orderToSet->setState($this->statusCompleted)->setStatus($this->statusCompleted);
                    $orderToSet->addStatusToHistory(
                        $orderToSet->getStatus(),
                        "Venta completada. El pago se realizó con éxito."
                    );
                    $orderToSet->save();
                }

                if ($data->state == 'expired') {
                    $this->logger->debug('Orden Experiada');
                    $orderToSet->setState($this->statusCanceled)->setStatus($this->statusCanceled);
                    $orderToSet->addStatusToHistory(
                        $orderToSet->getStatus(),
                        "Venta expirada, el pago no se completó"
                    );
                    $orderToSet->save();
                }
            }
        }
    }
}
