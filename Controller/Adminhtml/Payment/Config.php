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
            $plugin_status = ($plugin_status === 'true');
            $rsa_sk_plugin = $this->getRequest()->getParam('rsa_sk_plugin');
            $payment_methods = $this->getRequest()->getParam('payment_methods');

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

            $this->configWriter->save("payment/culqi/pk", $publicKey, ScopeInterface::SCOPE_STORE);
            $this->configWriter->save("payment/culqi/merchant", $merchant, ScopeInterface::SCOPE_STORE);
            $this->configWriter->save("payment/culqi/rsa_pk_culqi", $rsa_pk_culqi, ScopeInterface::SCOPE_STORE);
            $this->configWriter->save("payment/culqi/plugin_status", $plugin_status, ScopeInterface::SCOPE_STORE);
            $this->configWriter->save("payment/culqi/rsa_sk_plugin", $rsa_sk_plugin, ScopeInterface::SCOPE_STORE);
            $this->configWriter->save("payment/culqi/payment_methods", $payment_methods, ScopeInterface::SCOPE_STORE);

            return $resultJson->setData([
                'success' => true,
                'message' => __('Payment method %1 has been %2.', $methodCode, $status ? 'enabled' : 'disabled')
            ]);
        } catch (\Exception $e) {
            return $resultJson->setData(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
