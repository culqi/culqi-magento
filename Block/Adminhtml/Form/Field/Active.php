<?php

namespace Culqi\Pago\Block\Adminhtml\Form\Field;

class Active extends \Magento\Config\Block\System\Config\Form\Field
{
    const CONFIG_PATH = 'payment/culqi/active';
    /**
     * Template path
     *
     * @var string
     */
    protected $_template = 'system/active.phtml';

    protected $_value = null;

    /**
     * Render fieldset html
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->setNamePrefix($element->getName())
                ->setHtmlId($element->getHtmlId())
                ->setName($element->getName());
        //$columns = $this->getRequest()->getParam('website') || $this->getRequest()->getParam('store') ? 5 : 4;
        //$this->setName('payment_methods[]');
        return $this->_decorateRowHtml($element, "<td class='label'>Culqi Checkout</td><td>" . $this->toHtml() . '</td><td></td>');
    }

    public function getIsChecked($name)
    {
        //var_dump($name); exit(1);
        //var_dump($this->getCheckedValues()); exit(1);

        if ($this->getCheckedValues() == 'yes') {
            return true;
        }

        return false;
        //return in_array($name, $this->getCheckedValues());
    }

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

    public function getValue()
    {
        if (is_null($this->_value)) {
            $data = $this->getConfigData();
            if (isset($data[self::CONFIG_PATH])) {
                $data = $data[self::CONFIG_PATH];
            } else {
                $data = '';
            }
            $this->_value = $data;
        }
        return $this->_value;
    }
}