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
        $paymentMethods = $this->_paymentConfig->getActiveMethods(); // Get all active methods

        $methods = [];
        foreach ($paymentMethods as $methodCode => $method) {
            // Add relevant details for each method (you can customize this)
            $methods[] = [
                'method_code' => $methodCode,
                'method_title' => $method->getTitle(),
                'method_icon' => $method->getConfigData('icon')
            ];
        }

        // Return the available payment methods as JSON
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData(['payment_methods' => $methods]);
        $resultJson = $this->resultJsonFactory->create();

        try {
            $methodCode = $this->getRequest()->getParam('method_code');
            $status = (int) $this->getRequest()->getParam('status');

            if (!$methodCode) {
                throw new \Exception(__('Payment method code is missing.'));
            }

            // Save the configuration
            $this->configWriter->save("payment/culqi/active", $status, ScopeInterface::SCOPE_STORE);

            // Clear cache
            $this->_objectManager->get('Magento\Framework\App\Cache\Manager')->flush(['config']);

            return $resultJson->setData(['success' => true, 'message' => __('Payment method updated successfully.')]);
        } catch (\Exception $e) {
            return $resultJson->setData(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
