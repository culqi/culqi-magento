<?php
namespace Culqi\Pago\Block\Adminhtml\Form\Field;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Config\Block\System\Config\Form\Field;

class Disable extends Field
{
    protected function _getElementHtml(AbstractElement $element)
    {
        $element->setReadonly(false);
        return $element->getElementHtml();
    }
}