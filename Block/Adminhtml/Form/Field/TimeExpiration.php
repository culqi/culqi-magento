<?php

//namespace Vendor\Module\Block\Adminhtml\System\Config;
namespace Culqi\Pago\Block\Adminhtml\Form\Field;

class TimeExpiration extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * Template path
     *
     * @var string
     */
    protected $_template = 'system/config/expiration.phtml';
    const CONFIG_PATH = 'payment/culqi/timexp';
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
        return $this->_decorateRowHtml($element, "<td class='label'>Tiempo de expiración de pago</td><td>" . $this->toHtml() . '<p class="note"><span>Ingresar el número de horas que tendrá el cliente para pagar su orden. Ejem: 24.</span></p></td><td></td>');
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