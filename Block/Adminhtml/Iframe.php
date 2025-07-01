<?php

namespace Culqi\Pago\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;

class Iframe extends Template
{
    public function __construct(Context $context, array $data = [])
    {
        parent::__construct($context, $data);
    }

    public function getIframeUrl()
    {
        return 'http://localhost:5173/?platform=prestashop&status=1&pk=pk_live_6b3914664db6a6be&merchant=QA%20EXP%20CORE%2001&activePaymentMethods=tarjeta%2Cyape%2CbancaMovil%2Cagente%2Ccuotealo&shop=https%3A%2F%2Fprestashop-qa.culqi.xyz%2F';
    }
}


