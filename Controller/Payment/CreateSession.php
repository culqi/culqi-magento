<?php

namespace Culqi\Pago\Controller\Payment;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\UrlInterface;
use Culqi\Pago\Helper\Data;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\ProductMetadataInterface;

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

    protected $productMetadata;
    protected $design;
    protected $url;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        Json $jsonSerializer,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Data $helper,
        ProductMetadataInterface $productMetadata,
        JsonFactory $resultJsonFactory,
        DesignInterface $design,
        UrlInterface $url
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
        $this->productMetadata = $productMetadata;
        $this->design = $design;
        $this->url = $url;
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

            $billingAddress = $order->getBillingAddress();
            $shippingAddress = $order->getShippingAddress();

            $billingStreet = ($billingAddress && $billingAddress->getStreet()) ? $billingAddress->getStreet() : [''];
            $shippingStreet = ($shippingAddress && $shippingAddress->getStreet()) ? $shippingAddress->getStreet() : [''];

            $billingFirstName = ($billingAddress && $billingAddress->getFirstname()) ? $billingAddress->getFirstname() : '';
            $billingLastName = ($billingAddress && $billingAddress->getLastname()) ? $billingAddress->getLastname() : '';
            $billingPhone = ($billingAddress && $billingAddress->getTelephone()) ? $billingAddress->getTelephone() : '';

            $shippingFirstName = ($shippingAddress && $shippingAddress->getFirstname()) ? $shippingAddress->getFirstname() : '';
            $shippingLastName = ($shippingAddress && $shippingAddress->getLastname()) ? $shippingAddress->getLastname() : '';
            $shippingPhone = ($shippingAddress && $shippingAddress->getTelephone()) ? $shippingAddress->getTelephone() : '';

            $billingCity = ($billingAddress && $billingAddress->getCity()) ? $billingAddress->getCity() : '';
            $shippingCity = ($shippingAddress && $shippingAddress->getCity()) ? $shippingAddress->getCity() : '';

            $billingCountry = ($billingAddress && $billingAddress->getCountryId()) ? $billingAddress->getCountryId() : '';
            $shippingCountry = ($shippingAddress && $shippingAddress->getCountryId()) ? $shippingAddress->getCountryId() : '';

            $billingPostcode = ($billingAddress && $billingAddress->getPostcode()) ? $billingAddress->getPostcode() : '';
            $shippingPostcode = ($shippingAddress && $shippingAddress->getPostcode()) ? $shippingAddress->getPostcode() : '';

            $billingRegion = ($billingAddress && $billingAddress->getRegion()) ? $billingAddress->getRegion() : '';
            $shippingRegion = ($shippingAddress && $shippingAddress->getRegion()) ? $shippingAddress->getRegion() : '';

            $shippingMethod = $order->getShippingDescription() ?: 'No method selected';
            $shippingTotal = number_format((float) $order->getShippingAmount(), 2, '.', '');
            $shippingTax = number_format((float) $order->getShippingTaxAmount(), 2, '.', '');

            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
            $remoteIp = $this->getRemoteIp();
            $phone = $billingPhone ?: $shippingPhone;
            $browser = $userAgent;

            $themeConfig = $this->getThemeInfo();
            $products = $this->getOrderProducts($order);
            $cancelUrl = $this->url->getUrl('checkout/cart');

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
                        "cancel_url" => $cancelUrl
                    )
                ),
                "customer" => array(
                    "billing_address" => array(
                        "given_name" => $billingFirstName,
                        "family_name" => $billingLastName,
                        "line1" => $billingStreet[0] ?? '',
                        "line2" => $billingStreet[1] ?? '',
                        "city" => $billingCity,
                        "postal_code" => $billingPostcode,
                        "province" => $billingRegion,
                        "country_code" => $billingCountry
                    ),
                    "shipping_address" => array(
                        "given_name" => $shippingFirstName,
                        "family_name" => $shippingLastName,
                        "line1" => $shippingStreet[0] ?? '',
                        "line2" => $shippingStreet[1] ?? '',
                        "city" => $shippingCity,
                        "postal_code" => $shippingPostcode,
                        "province" => $shippingRegion,
                        "country_code" => $shippingCountry
                    ),
                    "shipping_data" => array(
                        "method" => $shippingMethod,
                        "total" => $shippingTotal,
                        "tax" => $shippingTax
                    ),
                    "email" => $email,
                    "locale" => "en-PE"
                ),
                "cancel_url" => $cancelUrl,
                "merchant_locale" => "en-PE",
                "shop_domain" => $store_url,
                "order_key" => $this->getOrderKey($order),
                "phone" => $phone,
                "browser" => $browser,
                "products" => $products,
                "audit_data" => array(
                    "integration_type" => "plugin",
                    "ip" => $remoteIp,
                    "user_agent" => $userAgent,
                    "checkout_version" => defined('CHECKOUT_VERSION') ? CHECKOUT_VERSION : '',
                    "threeds" => defined('CULQI_3DS') ? CULQI_3DS : '',
                    "plugin_version" => defined('PLUGIN_VERSION') ? PLUGIN_VERSION : '',
                    "cms" => PLATFORM,
                    "cms_version" => $this->productMetadata->getVersion(),
                    "php_version" => phpversion(),
                    "name_theme" => $themeConfig['name'],
                    "version_theme" => $themeConfig['version'],
                    "url_theme" => $themeConfig['url'],
                ),
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

    private function getRemoteIp()
    {
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return trim($ip);
    }

    private function getThemeInfo()
    {
        try {
            $themeName = $this->design->getThemeName() ?: '';
            $theme = $this->design->getDesignTheme();
            $themeVersion = '';
            if ($theme && method_exists($theme, 'getThemeVersion')) {
                $themeVersion = $theme->getThemeVersion() ?: '';
            }
            return [
                'name' => $themeName,
                'version' => $themeVersion,
                'url' => $themeName
            ];
        } catch (\Exception $e) {
            return ['name' => '', 'version' => '', 'url' => ''];
        }
    }

    private function getOrderProducts($order)
    {
        try {
            $items = $order->getAllItems();
            $products = [];
            foreach ($items as $item) {
                if ($item->getParentItem()) {
                    continue;
                }
                $qty = (int)$item->getQtyOrdered();
                $lineTotal = (float)$item->getRowTotalInclTax();
                $unitPrice = $qty > 0 ? $lineTotal / $qty : (float)$item->getPrice();
                $products[] = [
                    'name' => $item->getName() ?: '',
                    'quantity' => $qty > 0 ? $qty : 1,
                    'unit_price' => number_format($unitPrice, 2, '.', ''),
                ];
            }
            return $products;
        } catch (\Exception $e) {
            $this->logger->error('Error getting order products: ' . $e->getMessage());
            return null;
        }
    }

    private function getOrderKey($order)
    {
        try {
            $customerId = $order->getCustomerId();
            $orderId = $order->getIncrementId();
            return hash('sha256', $orderId . $customerId . time());
        } catch (\Exception $e) {
            return hash('sha256', $order->getIncrementId() . time());
        }
    }
}
