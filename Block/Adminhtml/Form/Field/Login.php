<?php

//namespace Vendor\Module\Block\Adminhtml\System\Config;
namespace Culqi\Pago\Block\Adminhtml\Form\Field;

class Login extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * Template path
     *
     * @var string
     */
    protected $_template = 'system/config/login.phtml';

    /**
     * Render fieldset html
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        //$columns = $this->getRequest()->getParam('website') || $this->getRequest()->getParam('store') ? 5 : 4;
        return $this->_decorateRowHtml($element, "<td class='label'>¡Ahorra tiempo configurando tu Culqi checkout! Inicia sesión con tu cuenta de CulqiPanel</td><td>" . $this->toHtml() . '</td><td></td>');
    }


    public function getUrlWebhookInteg()
    {

        return URLAPI_WEBHOOK_INTEG;
        
    }

    public function getUrlWebhookProd()
    {

        return URLAPI_WEBHOOK_PROD;
        
    }

    public function getUrlLoginInteg()
    {

        return URLAPI_LOGIN_INTEG;
        
    }

    public function getUrlLoginProd()
    {

        return URLAPI_LOGIN_PROD;
        
    }

    public function getUrlMerchantInteg()
    {

        return URLAPI_MERCHANT_INTEG;
        
    }

    public function getUrlMerchantProd()
    {

        return URLAPI_MERCHANT_PROD;
        
    }

    public function getUrlMerchantSingleInteg()
    {

        return URLAPI_MERCHANTSINGLE_INTEG;
        
    }

    public function getUrlMerchantSingleProd()
    {

        return URLAPI_MERCHANTSINGLE_PROD;
        
    }

}