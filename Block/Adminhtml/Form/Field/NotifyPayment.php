<?php

//namespace Vendor\Module\Block\Adminhtml\System\Config;
namespace Culqi\Pago\Block\Adminhtml\Form\Field;

class NotifyPayment extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * Template path
     *
     * @var string
     */
    protected $_template = 'system/config/notify.phtml';
    const CONFIG_PATH = 'payment/culqi/notpay';
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
        $auth_webhook = $this->generate_autentication_webhook();
        $username_webhook = $auth_webhook['username_webhook'];
        $password_webhook = $auth_webhook['password_webhook'];
        $text_webhook = '<p class="note"><span>Si no iniciaste sesión con tu cuenta de CulqiPanel, tienes que configurar esta URL colocando estas credenciales:<br><b>Username:'. $username_webhook .'</b><b>Password:'. $password_webhook .'</b></span></p>';
        return $this->_decorateRowHtml($element, "<td class='label'>Notificaciones de Pago<div class='tooltip'><span class='help'><span></span></span><div class='tooltip-content' style='text-align:left;'>Ingresa a tu Culqipanel en la sección de eventos, hacer clic a +Añadir. Se abrirá un popup, en donde deberás escoger order.status.changed y pegar la siguiente URL</div></div></td><td>" . $this->toHtml() . $this->text_webhook. '</td><td></td>');
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

    private function generate_autentication_webhook()
    {
      return $data = [
        'username_webhook' => bin2hex(random_bytes(5)),
        'password_webhook' => bin2hex(random_bytes(10))
        ];
    }
}