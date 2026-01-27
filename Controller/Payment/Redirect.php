<?php

namespace Culqi\Pago\Controller\Payment;

use Magento\Framework\Serialize\Serializer\Json;
use Culqi\Pago\Helper\Data;
class Redirect extends \Magento\Framework\App\Action\Action
{
    protected $curl;
    protected $orderRepository;
    protected $jsonSerializer;

    protected $culqiopera;
    protected $resultPageFactory;
    protected $_checkoutSession;
    protected $logger;
    protected $storeManager;
    protected $helper;
    
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        Json $jsonSerializer,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Culqi\Pago\Model\Payment\Culqi $culqiopera,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Data $helper,
    ) {
        parent::__construct($context);
        $this->curl = $curl;
        $this->orderRepository = $orderRepository;
        $this->culqiopera = $culqiopera;
        $this->resultPageFactory = $resultPageFactory;
        $this->_checkoutSession = $checkoutSession;
        $this->jsonSerializer = $jsonSerializer;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
        $this->helper = $helper;
    }

    public function execute()
    {
        $amount = $this->_checkoutSession->getAmount() ?? 0;
        $currency_code = $this->_checkoutSession->getCurrencyCode();
        $city = $this->_checkoutSession->getBillingCity();
        $street = $this->_checkoutSession->getBillingStreet();
        $country_code = $this->_checkoutSession->getCountryCode();
        $description = $this->_checkoutSession->getDescription();
        $store_name = $this->_checkoutSession->getStoreName();
        $store_url = $this->storeManager->getStore()->getBaseUrl();
        $store_url = preg_replace("/^https?:\/\//", "", $store_url);
        $store_url = rtrim($store_url, "/");
        $first_name = $this->_checkoutSession->getFirstName();
        $last_name = $this->_checkoutSession->getLastName();
        $phone_umber = $this->_checkoutSession->getPhoneNumber();
        $email = $this->_checkoutSession->getEmail();
        $order_id = $this->_checkoutSession->getOrderId();
        $env = $this->get_env();
        $api_url = CULQI_API_URL . 'shopify/public/save-order';
        $activeMultiPay = $this->_checkoutSession->getActiveMultiPay();
        $token = $this->helper->generate_token(true);
        $payment_methods = $this->helper->get_payment_methods();

    
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
        
        //var_dump('Response Body:', $responseBody);
        
        $result = $this->jsonSerializer->unserialize($responseBody);
        $gateway_url = '';

        //var_dump('Parsed Result:', $result);

        if (isset($result['redirect_url'])) {
            $gateway_url = $result['redirect_url'];
        } else {
            $errorMessage = 'Payment error: Invalid response from payment gateway. Response: ' . $responseBody;
            $this->logger->error($errorMessage);
            //var_dump('Error:', $errorMessage);
            throw new \Exception($errorMessage);
        }

        $block->setData('gateway_url', $gateway_url);
        $block->setData('show_modal', true);
        $block->setData('payment_methods', $payment_methods);
        $this->logger->info($gateway_url);
        return $page;
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
