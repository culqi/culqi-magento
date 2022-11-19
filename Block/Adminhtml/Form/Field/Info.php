<?php

namespace Culqi\Pago\Block\Adminhtml\Form\Field;

use \Magento\Framework\Data\Form\Element\Renderer\RendererInterface as RendererInterface;

class Info extends \Magento\Backend\Block\AbstractBlock implements RendererInterface
{
    public function __construct(\Magento\Framework\Filesystem\DirectoryList $dir)
    {
        $this->_dir = $dir;
    }
    
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = '<div class="section-config with-button""> 
                    <div class="config-heading">
                        <div class=row-heading>
                            '.MPCULQI_PLUGIN_VERSION.'
                            <strong class="text-right">
                                <a class="link-more" href="https://www.culqi.com/docs" target="_blank"> Documentación Culqi </a>
                            </strong>
                        </div>
                    </div>
                 </div>';
        return $html;
    }
}
