<?php

namespace Culqi\Pago\Controller\Payment;

use Magento\Framework\Serialize\Serializer\Json;
use Culqi\Pago\Helper\Data;
use Magento\Framework\Controller\Result\JsonFactory;

class CreateSession extends \Magento\Framework\App\Action\Action
{
    protected $curl;
    protected $orderRepository;
    protected $jsonSerializer;
    protected $_checkoutSession;
    protected $logger;
    protected $storeManager;
    protected $helper;
    protected $resultJsonFactory;
    
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        Json $jsonSerializer,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Data $helper,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->curl = $curl;
        $this->orderRepository = $orderRepository;
        $this->_checkoutSession = $checkoutSession;
        $this->jsonSerializer = $jsonSerializer;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
        $this->helper = $helper;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        try {
            $order = $this->_checkoutSession->getLastRealOrder();
            $amount = $order->getGrandTotal();
            $currency_code = $order->getOrderCurrencyCode();
            $billingAddress = $order->getBillingAddress();
            $shippingAddress = $order->getShippingAddress();
            $city = ($shippingAddress && $shippingAddress->getCity()) ? $shippingAddress->getCity() : '';
            $street = ($shippingAddress && $shippingAddress->getStreet()) ? $shippingAddress->getStreet() : [''];
            $country_code = ($shippingAddress && $shippingAddress->getCountryId()) ? $shippingAddress->getCountryId() : '';
            $description = $order->getIncrementId();
            $store_name = $this->storeManager->getStore()->getName();
            $store_url = $this->storeManager->getStore()->getBaseUrl();
            $store_url = preg_replace("/^https?:\/\//", "", $store_url);
            $store_url = rtrim($store_url, "/");
            $first_name = ($shippingAddress && $shippingAddress->getFirstname()) ? $shippingAddress->getFirstname() : '';
            $last_name = ($shippingAddress && $shippingAddress->getLastname()) ? $shippingAddress->getLastname() : '';
            $phone_number = ($shippingAddress && $shippingAddress->getTelephone()) ? $shippingAddress->getTelephone() : '';
            $email = $order->getCustomerEmail();
            $order_id = $order->getIncrementId();
            $env = $this->get_env();
            $api_url = CULQI_API_URL . 'shopify/public/save-order';
            $activeMultiPay = $this->_checkoutSession->getActiveMultiPay();
            $token = $this->helper->generate_token(true);
            $payment_methods = $this->helper->get_payment_methods();

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
                        "line1" => $street[0],
                        "line2" => '',
                        "city" => $city,
                        "postal_code" => '',
                        "province" => '',
                        "country_code" => $country_code
                    ),
                    "shipping_address" => array(
                        "given_name" => $first_name,
                        "family_name" => $last_name,
                        "line1" => $street[0],
                        "line2" => '',
                        "city" => $city,
                        "postal_code" => '',
                        "province" => '',
                        "country_code" => $country_code
                    ),
                    "email" => $email,
                    "locale" => "en-PE"
                ),
                "cancel_url" => '',
                "merchant_locale" => "en-PE",
                "shop_domain" => $store_url,
                "order_key" => '123',
            );

            $headers = [
                'Content-Type' => 'application/json',
                'shopify-shop-domain' => '',
                'Authorization' => 'Bearer ' . $token
            ];

            $jsonBody = $this->jsonSerializer->serialize($body);

            // Set options and make the request
            $this->curl->setHeaders($headers);
            $this->curl->setTimeout(45);
            $this->curl->post($api_url, $jsonBody);
            $responseBody = $this->curl->getBody();
            
            $this->logger->info('API URL: ' . $api_url);
            $this->logger->info('Request Body: ' . $jsonBody);
            $this->logger->info('Response Body: ' . $responseBody);
            
            $result = $this->jsonSerializer->unserialize($responseBody);

            if (isset($result['redirect_url'])) {
                return $resultJson->setData([
                    'success' => true,
                    'redirect_url' => $result['redirect_url'],
                    'payment_methods' => $payment_methods
                ]);
            } else {
                $errorMessage = 'Payment error: Invalid response from payment gateway. Response: ' . $responseBody;
                $this->logger->error($errorMessage);
                return $resultJson->setData([
                    'success' => false,
                    'error' => $errorMessage
                ]);
            }
        } catch (\Exception $e) {
            $this->logger->error('Payment session creation error: ' . $e->getMessage());
            return $resultJson->setData([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function get_env()
    {
        $public_key = $this->helper->get_public_key();
        if(!$public_key) {
            return;
        }

        if (str_starts_with($public_key, 'pk_test')) {
            return 'test';
        } elseif (str_starts_with($public_key, 'pk_live')) {
            return 'live';
        }
        return false;
    }
}
