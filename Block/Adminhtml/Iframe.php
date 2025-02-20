<?php

namespace Culqi\Pago\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Config\ReinitableConfigInterface;

class Iframe extends Template
{
    protected $storeManager;
    protected $scopeConfig;

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context, $data);
    }

    public function getIframeUrl()
    {
        // Reinitialize config to ensure updated values
        $config = ObjectManager::getInstance()->get(ReinitableConfigInterface::class);
        $config->reinit();

        list($public_key, $merchant, $payment_methods, $plugin_status) = $this->getIframeParameters();

        $shopUrl = $this->storeManager->getStore()->getBaseUrl();

        return CULQI_CONFIG_URL . '?platform=' . PLATFORM . '&status=' . urlencode( $plugin_status ) . '&pk=' . urlencode( $public_key ) . '&merchant=' . urlencode( $merchant ) . '&activePaymentMethods=' . urlencode($payment_methods) . '&shop=' . urlencode($shopUrl);
    }

    private function getIframeParameters()
    {
        $payment_methods = $this->scopeConfig->getValue(
            "payment/culqi/payment_methods",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ) ?? '';

        if (empty($payment_methods)) {
            $resource = ObjectManager::getInstance()->get('Magento\\Framework\\App\\ResourceConnection');
            $connection = $resource->getConnection();
            $tableName = $resource->getTableName('core_config_data');
            $query = "SELECT value FROM $tableName WHERE path = 'payment/culqi/payment_methods' LIMIT 1";
            $payment_methods = $connection->fetchOne($query) ?: '';
        }
        
        $publicKey = $this->scopeConfig->getValue(
            'payment/culqi/pk',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ) ?? '';

        if (empty($publicKey)) {
            $resource = ObjectManager::getInstance()->get('Magento\\Framework\\App\\ResourceConnection');
            $connection = $resource->getConnection();
            $tableName = $resource->getTableName('core_config_data');
            $query = "SELECT value FROM $tableName WHERE path = 'payment/culqi/pk' LIMIT 1";
            $publicKey = $connection->fetchOne($query) ?: '';
        }
        
        $merchant = $this->scopeConfig->getValue(
            "payment/culqi/merchant",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ) ?? '';

        if (empty($merchant)) {
            $resource = ObjectManager::getInstance()->get('Magento\\Framework\\App\\ResourceConnection');
            $connection = $resource->getConnection();
            $tableName = $resource->getTableName('core_config_data');
            $query = "SELECT value FROM $tableName WHERE path = 'payment/culqi/merchant' LIMIT 1";
            $merchant = $connection->fetchOne($query) ?: '';
        }
        
        $plugin_status = $this->scopeConfig->getValue(
            "payment/culqi/plugin_status",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ) ?? '';

        if (empty($plugin_status)) {
            $resource = ObjectManager::getInstance()->get('Magento\\Framework\\App\\ResourceConnection');
            $connection = $resource->getConnection();
            $tableName = $resource->getTableName('core_config_data');
            $query = "SELECT value FROM $tableName WHERE path = 'payment/culqi/plugin_status' LIMIT 1";
            $plugin_status = $connection->fetchOne($query) ?: 0;
            $plugin_status = ($plugin_status === 'true');
        }

        return [$publicKey, $merchant, $payment_methods, $plugin_status];
    }
}
