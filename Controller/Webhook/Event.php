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
    protected $storeConfig;

    protected $statusProcessing = \Magento\Sales\Model\Order::STATE_PROCESSING;
    protected $statusCanceled = \Magento\Sales\Model\Order::STATE_CANCELED;
    protected $statusCompleted = \Magento\Sales\Model\Order::STATE_COMPLETE;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Psr\Log\LoggerInterface $logger,
        \Culqi\Pago\Block\Payment\Redirect $storeConfig
    ) {
        $this->logger = $logger;
        $this->order = $order;
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
        $inputJSON = $this->getRequest()->getContent();
        
        $input = json_decode($inputJSON);
        
        $data = json_decode($input->data);

        $this->logger->debug("Mensaje de webhook recibido");

        $headers = getallheaders();

        if(!isset($headers['Authorization'])){
            exit("Error: No headers present");
        }else{
            $headers = $headers['Authorization'];
        }
        $authorization = substr($headers,6);
        $credenciales = base64_decode($authorization);
        $credenciales = explode( ':', $credenciales );
        $username = $credenciales[0];
        $password = $credenciales[1];

        if(!isset($username) or !isset($password)){
            exit("Error: No autorizado");
        }

        $usernameBD = $this->storeConfig->getWebhookUsername();
        $passwordBD = $this->storeConfig->getWebhookPassword();

        if($username <> $usernameBD or $password <> $passwordBD){
            exit("Error: Crendenciales Incorrectas");
        }

        if (empty($data->metadata)) {
            exit("Error: Metadata vacia");
        }

        if (empty($data->amount)) {
            exit("Error: No envió el amount");
        }

        if (empty($data->id) || empty($data->order_number) || empty($data->currency_code) || empty($data->state)) {
            exit("Error: order_id, order_number, currency_code o state vacios");
        }

        if ($input->object == 'event' && $input->type == 'order.status.changed') {
            $mgtOrderId = (int)$data->metadata->order_id;

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
                    $this->logger->debug('Orden Expirada');
                    $orderToSet->setState($this->statusCanceled)->setStatus($this->statusCanceled);
                    $orderToSet->addStatusToHistory(
                        $orderToSet->getStatus(),
                        "Venta expirada, el pago no se completó"
                    );
                    $orderToSet->save();
                }
                echo "Operación satisfactoria";
            }
        }
    }
}
