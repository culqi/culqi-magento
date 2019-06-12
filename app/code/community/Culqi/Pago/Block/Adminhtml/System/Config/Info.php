<?php

class Culqi_Pago_Block_Adminhtml_System_Config_Info
    extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{

    /**
     * Render Information element
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
       
        
        if ($storeId == null && $this->getStore()) {
            $storeId = $this->getStore();
        }
        $webhookUrl =  Mage::app()->getStore($storeId)
            ->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK).
            'authorizenet/directpost_payment/response';


        $html = '<div class="section-config"> <div class="config-heading"> <div class="heading"><strong>Culqi<a class="link-more" href="https://www.culqi.com/docs" target="_blank">Leer más</a></strong><span class="heading-intro"> Con Culqi comienza a aceptar pagos con tarjeta de crédito/debito y también en efectivo (<b>¡Nuevo!</b>).</span></div> </div> </div>';
        return $html;
    }
}