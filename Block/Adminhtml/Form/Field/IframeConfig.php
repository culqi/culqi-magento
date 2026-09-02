<?php

namespace Culqi\Pago\Block\Adminhtml\Form\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Culqi\Pago\Helper\Data;

class IframeConfig extends \Magento\Config\Block\System\Config\Form\Field
{
    protected $storeManager;
    protected $scopeConfig;
    protected $helper;

    protected $_template = 'Culqi_Pago::system/config/iframe.phtml';

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->storeManager = $storeManager;
        $this->scopeConfig = $context->getScopeConfig();
        $this->helper = $helper;
    }

    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        $element->setLabel('');
        return parent::render($element);
    }

    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_toHtml();
    }

    public function getIframeUrl()
    {
        // Reinitialize config to ensure updated values
        $config = ObjectManager::getInstance()->get(ReinitableConfigInterface::class);
        $config->reinit();

        list($public_key, $merchant, $payment_methods, $plugin_status) = $this->getIframeParameters();
        $token = $this->helper->generate_token(true);
        
        if($token === null) {
            $token = '';
        }

        $shopUrl = $this->storeManager->getStore()->getBaseUrl();
        $shopName = $this->storeManager->getStore()->getName();

        return CULQI_CONFIG_URL . '?platform=' . PLATFORM . '&status=' . urlencode( $plugin_status ) . '&pk=' . urlencode( $public_key ) . '&merchant=' . urlencode( $merchant ) . '&activePaymentMethods=' . urlencode($payment_methods) . '&shop=' . urlencode($shopUrl) . '&shop_name=' . urlencode($shopName) . '&token=' . urlencode($token);
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

        $resource = ObjectManager::getInstance()->get('Magento\\Framework\\App\\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName('core_config_data');
        $query = "SELECT value FROM $tableName WHERE path = 'payment/culqi/active' LIMIT 1";
        $plugin_status = $connection->fetchOne($query) ?: 0;

        return [$publicKey, $merchant, $payment_methods, $plugin_status];
    }
}
