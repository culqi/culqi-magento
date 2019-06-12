<?php

class Culqi_Pago_Block_Adminhtml_System_Config_Separator
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

        $html = '<tr class="system-fieldset-sub-head"><td colspan="5"><h4> Pagos con efectivo </h4> </td></tr>'; 

        return $html;
    }
}