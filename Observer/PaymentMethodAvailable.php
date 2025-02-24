<?php
namespace Culqi\Pago\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
class PaymentMethodAvailable implements ObserverInterface
{
    /**
     * payment_method_is_active event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */

    protected $scopeConfig;
    protected $logger;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        \Psr\Log\LoggerInterface $logger,
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
    }
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $resource = ObjectManager::getInstance()->get('Magento\\Framework\\App\\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName('core_config_data');
        $query = "SELECT value FROM $tableName WHERE path = 'payment/culqi/active' LIMIT 1";
        $plugin_status = $connection->fetchOne($query) ?: 0;
        $plugin_status = ($plugin_status != 0);

        if($observer->getEvent()->getMethodInstance()->getCode()=="culqi") {
            $checkResult = $observer->getEvent()->getResult();
            $checkResult->setData('is_available', $plugin_status);
        }
    }
}