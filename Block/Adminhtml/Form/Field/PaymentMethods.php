<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_DemoModule
 * @author    Webkul
 * @copyright Copyright (c) 2010-2016 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
//namespace Culqi\Pago\Block\System\Config;
namespace Culqi\Pago\Block\Adminhtml\Form\Field;
use Magento\Config\Block\System\Config\Form\Field;

class PaymentMethods extends Field
{
    const CONFIG_PATH = 'payment/culqi/payment_methods';

    //protected $_template = 'Webkul_CulqiPago::system/config/checkbox.phtml';
    protected $_template = 'system/config/checkbox.phtml';

    protected $_values = null;
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Config\Model\Config\Structure $configStructure
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }
    /**
     * Retrieve element HTML markup.
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     *
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->setNamePrefix($element->getName())
            ->setHtmlId($element->getHtmlId());

        return $this->_toHtml();
    }
    
    public function getValues()
    {
        $values = [];
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        foreach ($objectManager->create('Culqi\Pago\Model\Config\Checkbox')->toOptionArray() as $value) {
            $values[$value['value']] = $value['label'];
        }

        return $values;
    }
    /**
     * 
     * @param  $name 
     * @return boolean
     */
    public function getIsChecked($name)
    {
        //var_dump($this->getCheckedValues());
        return in_array($name, $this->getCheckedValues());
    }
    /**
     * 
     *get the checked value from config
     */
    public function getCheckedValues()
    {
        if (is_null($this->_values)) {
            $data = $this->getConfigData();
            //var_dump($data); exit(1);
            //var_dump(isset($data['payment/culqi/checkbox'])); exit(1);
            if (isset($data[self::CONFIG_PATH])) {
                $data = $data[self::CONFIG_PATH];
            } else {
                $data = '';
            }
            $this->_values = explode(',', $data);
        }

        //var_dump($this->_values); exit(1);
        return $this->_values;
    }
}