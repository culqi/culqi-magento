<?php
declare(strict_types=1);

namespace Culqi\Pago\Model\Payment;

class Pago extends \Magento\Payment\Model\Method\AbstractMethod
{
    const CODE = 'culqi';
    protected $_isInitializeNeeded = true;
    protected $_code = self::CODE;
}
