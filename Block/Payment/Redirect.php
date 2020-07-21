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
}
