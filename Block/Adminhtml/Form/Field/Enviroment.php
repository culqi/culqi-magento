<?php 
namespace Culqi\Pago\Block\Adminhtml\Form\Field;

//use Magento\Widget\Block\BlockInterface;
use Magento\Framework\Option\ArrayInterface;

class Enviroment implements ArrayInterface { 

    public function toOptionArray() { 
        
        return [
            ['value' => 'integ', 'label' => __('Integración')], 
            ['value' => 'prod', 'label' => __('Producción')],
        ];
	}
}
