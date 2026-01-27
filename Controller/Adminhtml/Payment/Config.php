<?php

namespace Culqi\Pago\Controller\Adminhtml\Payment;

use Magento\Backend\App\Action;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Payment\Model\Config as Test;


class Config extends Action
{
    protected $configWriter;
    protected $resultJsonFactory;
    protected $_scopeConfig;
    protected $_paymentConfig;

    public function __construct(
        Action\Context $context,
        WriterInterface $configWriter,
        JsonFactory $resultJsonFactory,
        ScopeConfigInterface $scopeConfig,
        Test $paymentConfig,
    ) {
        parent::__construct($context);
        $this->configWriter = $configWriter;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->_paymentConfig = $paymentConfig;
    }

    public function execute()
    {
        // Return the available payment methods as JSON
        $resultJson = $this->resultJsonFactory->create();

        try {
            $methodCode = $this->getRequest()->getParam('method_code');
            $status = $this->getRequest()->getParam('status');
            $publicKey = $this->getRequest()->getParam('publicKey');
            $merchant = $this->getRequest()->getParam('merchant');
            $rsa_pk_culqi = $this->getRequest()->getParam('rsa_pk_culqi');
            $plugin_status = $this->getRequest()->getParam('pluginStatus');
            $plugin_status = ($plugin_status === 'true' || $plugin_status === true || $plugin_status === 1);
            $rsa_sk_plugin = $this->getRequest()->getParam('rsa_sk_plugin');
            $payment_methods = $this->getRequest()->getParam('payment_methods');

            // Debug logging
            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/culqi_config.log');
            $logger = new \Zend_Log();
            $logger->addWriter($writer);
            $logger->info('Received params:');
            $logger->info('publicKey: ' . ($publicKey ?: 'NULL'));
            $logger->info('merchant: ' . ($merchant ?: 'NULL'));
            $logger->info('rsa_pk_culqi length: ' . strlen($rsa_pk_culqi ?: ''));
            $logger->info('rsa_sk_plugin length: ' . strlen($rsa_sk_plugin ?: ''));
            $logger->info('payment_methods: ' . ($payment_methods ?: 'NULL'));

            if (!$methodCode) {
                throw new \Exception(__('Payment method code is missing.'));
            }

            // Save the configuration
            $this->configWriter->save("payment/culqi/active", $plugin_status, ScopeInterface::SCOPE_STORE);

            // Clear configuration and full-page caches
            $this->_objectManager->get(\Magento\Framework\App\Cache\Manager::class)->flush(['config', 'full_page']);

            // Reload configuration to apply changes immediately
            $this->_objectManager->create(\Magento\Framework\App\Config\ReinitableConfigInterface::class)->reinit();

            $paymentMethods = $this->_paymentConfig->getActiveMethods();

            $activeMethods = [];
            foreach ($paymentMethods as $methodCode => $method) {
                // Check if the method is enabled in configuration
                $isActive = $this->_scopeConfig->isSetFlag(
                    "payment/{$methodCode}/active",
                    ScopeInterface::SCOPE_STORE
                );

                if ($isActive) {
                    $activeMethods[] = [
                        'method_code' => $methodCode,
                        'method_title' => $method->getTitle(),
                        'method_icon' => $method->getConfigData('icon')
                    ];
                }
            }

            // Save config values - only save if not empty to prevent overwriting existing values
            if (!empty($publicKey)) {
                $this->configWriter->save("payment/culqi/pk", $publicKey, ScopeInterface::SCOPE_STORE);
            }
            if (!empty($merchant)) {
                $this->configWriter->save("payment/culqi/merchant", $merchant, ScopeInterface::SCOPE_STORE);
            }
            if (!empty($rsa_pk_culqi) && strlen(trim($rsa_pk_culqi)) > 0) {
                $this->configWriter->save("payment/culqi/rsa_pk_culqi", $rsa_pk_culqi, ScopeInterface::SCOPE_STORE);
                $logger->info('Saved rsa_pk_culqi successfully');
            } else {
                $logger->info('rsa_pk_culqi is empty, skipping save to preserve existing value');
            }
            if (!empty($rsa_sk_plugin) && strlen(trim($rsa_sk_plugin)) > 0) {
                $this->configWriter->save("payment/culqi/rsa_sk_plugin", $rsa_sk_plugin, ScopeInterface::SCOPE_STORE);
                $logger->info('Saved rsa_sk_plugin successfully');
            } else {
                $logger->info('rsa_sk_plugin is empty, skipping save to preserve existing value');
            }
            if (!empty($payment_methods)) {
                $this->configWriter->save("payment/culqi/payment_methods", $payment_methods, ScopeInterface::SCOPE_STORE);
            }
            // Always save plugin status
            $this->configWriter->save("payment/culqi/plugin_status", $plugin_status ? '1' : '0', ScopeInterface::SCOPE_STORE);

            return $resultJson->setData([
                'success' => true,
                'message' => __('Payment method %1 has been %2.', $methodCode, $status ? 'enabled' : 'disabled')
            ]);
        } catch (\Exception $e) {
            return $resultJson->setData(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
