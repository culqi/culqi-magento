<?php

namespace Culqi\Pago\Block\Adminhtml\Form\Field;

class PasswordWebhook extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * Template path
     *
     * @var string
     */
    protected $_template = 'system/config/password_webhook.phtml';
    const CONFIG_PATH = 'payment/culqi/password_webhook';
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

        return $this->_decorateRowHtml($element, "<td>" . $this->toHtml() . '</td>');
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

    public function generate_autentication_wh()
    {
        return PASSWORD_WEBHOOK;

    }
}