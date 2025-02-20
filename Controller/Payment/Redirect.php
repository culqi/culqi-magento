<?php

namespace Culqi\Pago\Controller\Payment;

use Magento\Framework\HTTP\Client\Curl;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Serialize\Serializer\Json;
class Redirect extends \Magento\Framework\App\Action\Action
{
    private $curl;
    private $orderRepository;
    private $jsonSerializer;

    protected $culqiopera;
    protected $resultPageFactory;
    protected $_checkoutSession;
    protected $logger;
    
    public function __construct(
        Curl $curl,
        OrderRepositoryInterface $orderRepository,
        Json $jsonSerializer,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Culqi\Pago\Model\Payment\Culqi $culqiopera,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->curl = $curl;
        $this->orderRepository = $orderRepository;
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
        $currency_code = $this->_checkoutSession->getCurrencyCode();
        $description = $this->_checkoutSession->getDescription();
        $store_name = $this->_checkoutSession->getStoreName();
        $first_name = $this->_checkoutSession->getFirstName();
        $last_name = $this->_checkoutSession->getLastName();
        $phone_umber = $this->_checkoutSession->getPhoneNumber();
        $email = $this->_checkoutSession->getEmail();
        $order_id = $this->_checkoutSession->getOrderId();
        $env = $this->get_env();
        $api_url = CULQI_API_URL . 'shopify/public/save-order';
        $activeMultiPay = $this->_checkoutSession->getActiveMultiPay();

    
        $page = $this->resultPageFactory->create();
        $block = $page->getLayout()->getBlock('payment.redirect');

        $body = array(
            "id" => $order_id,
            "platform" => PLATFORM,
            "gid" => "gid://magento/PaymentSession/" . $order_id,
            "amount" => number_format($amount, 2, '.', ''),
            "currency" => $currency_code,
            "proposed_at" => gmdate('Y-m-d\TH:i:s'),
            "kind" => "sale",
            "test" => $env,
            "payment_method" => array(
                "type" => "offsite",
                "data" => array(
                    "cancel_url" => ''
                )
            ),
            "customer" => array(
                "billing_address" => array(
                    "given_name" => $first_name,
                    "family_name" => $last_name,
                    "line1" => '',
                    "line2" => '',
                    "city" => '',
                    "postal_code" => '',
                    "province" => '',
                    "country_code" => ''
                ),
                "shipping_address" => array(
                    "given_name" => $first_name,
                    "family_name" => $last_name,
                    "line1" => '',
                    "line2" => '',
                    "city" => '',
                    "postal_code" => '',
                    "province" => '',
                    "country_code" => ''
                ),
                "email" => $email,
                "locale" => "en-PE"
            ),
            "cancel_url" => '',
            "merchant_locale" => "en-PE",
            "shop_domain" => $store_name,
            "order_key" => '123',
        );

        $headers = [
            'Content-Type' => 'application/json',
            'shopify-shop-domain' => '',
            'Authorization' => 'Bearer ' . ''
        ];

        $jsonBody = $this->jsonSerializer->serialize($body);

        // Set options and make the request
        $this->curl->setHeaders($headers);
        $this->curl->setTimeout(45);
        $this->curl->post($api_url, $jsonBody);
        $responseBody = $this->curl->getBody();
        $result = $this->jsonSerializer->unserialize($responseBody);
        $gateway_url = '';

        if (isset($result['redirect_url'])) {
            $gateway_url = $result['redirect_url'];
        } else {
            throw new \Exception('Payment error: Invalid response from payment gateway.');
        }

        $order->setStatus('pending_payment');
        $order->addCommentToStatusHistory(__('Payment pending, redirecting to gateway.'));
        $this->orderRepository->save($order);

        $block->setData('redirect', $gateway_url);
        $block->setData('show_modal', true);
        return $page;
    }

    private function get_env()
    {
        return 'test';
        /* $config = culqi_get_config();
        if(!$config->public_key) {
            wc_add_notice(__('Debes configurar tu llave pública.', 'culqi'), 'error');
            return;
        }

        $public_key = $config->public_key;

        if (str_starts_with($public_key, 'pk_test')) {
            return 'test';
        } elseif (str_starts_with($public_key, 'pk_live')) {
            return 'live';
        }
        return false; */
    }
}
