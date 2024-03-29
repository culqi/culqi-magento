<?php

namespace Culqi\Pago\Block\Payment;

class Redirect extends \Magento\Framework\View\Element\Template
{
    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context  $context
     * @param array $data
     */

    protected $scopeConfig;
    protected $storeManager;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    public function getEnviroment()
    {
        return $this->scopeConfig->getValue(
            'payment/culqi/enviroment',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getLlavePublica()
    {
        return $this->scopeConfig->getValue(
            'payment/culqi/llave_publica',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getLlaveSecreta()
    {
        return $this->scopeConfig->getvalue(
            'payment/culqi/llave_secreta',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getPaymentMethods()
    {
        $methods =  $this->scopeConfig->getvalue(
            'payment/culqi/payment_methods',
            \Magento\Store\model\ScopeInterface::SCOPE_STORE
        );

        return explode(',', (String)$methods);
    }

    public function getDuracionMaxima()
    {
        return $this->scopeConfig->getvalue(
            'payment/culqi/duracion_maxima',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getPrefix()
    {
        return $this->scopeConfig->getvalue(
            'payment/culqi/order_preffix',
            \Magento\Store\model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getStoreName()
    {
        return $this->storeManager->getStore()->getName();
    }

    public function getMagentoVersion(){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productMetadata = $objectManager->get('Magento\Framework\App\ProductMetadataInterface');
        return $productMetadata->getVersion();
    }

    public function getLogo()
    {
        return $this->scopeConfig->getvalue(
            'payment/culqi/logo',
            \Magento\Store\model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getTheme()
    {
        $theme =  $this->scopeConfig->getvalue(
            'payment/culqi/theme',
            \Magento\Store\model\ScopeInterface::SCOPE_STORE
        );

        if(is_null($theme) or $theme==''){
            $theme = '#141414-#00a19b';
        }

        return explode('-', (String)$theme);
    }

    public function getURLEnviroment()
    {
        $enviroment = $this->getEnviroment();

        $urlapi_ordercharges = URLAPI_ORDERCHARGES_INTEG;
        $urlapi_checkout = URLAPI_CHECKOUT_INTEG;

        if($enviroment =='prod'){
            $urlapi_ordercharges = URLAPI_ORDERCHARGES_PROD;
            $urlapi_checkout = URLAPI_CHECKOUT_PROD;
        }

        return $urlapi_ordercharges;
    }

    public function getTimeExpiration()
    {
        $timexp = $this->scopeConfig->getvalue(
            'payment/culqi/timexp',
            \Magento\Store\model\ScopeInterface::SCOPE_STORE
        );
        return isset($timexp) ? $timexp : TIME_EXPIRATION_DEFAULT;

    }
    
    public function getWebhookUsername()
    {
        $username = $this->scopeConfig->getvalue(
            'payment/culqi/username_webhook',
            \Magento\Store\model\ScopeInterface::SCOPE_STORE
        );
        return $username;

    }

    public function getWebhookPassword()
    {
        $password = $this->scopeConfig->getvalue(
            'payment/culqi/password_webhook',
            \Magento\Store\model\ScopeInterface::SCOPE_STORE
        );
        return $password;

    }

    public function getRsaId()
    {
        return $this->scopeConfig->getValue(
            'payment/culqi/rsa_id',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    
    public function getRsaPublickey()
    {
        $rsa_pk = $this->scopeConfig->getValue(
            'payment/culqi/rsa_publickey',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        
        if(is_string($rsa_pk)) {
            return trim($rsa_pk);
        }

        return $rsa_pk;
    }

}
