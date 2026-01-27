<?php
declare(strict_types=1);

namespace Culqi\Pago\Model\Payment;

class Pago extends \Magento\Payment\Model\Method\AbstractMethod
{
    const CODE = 'culqi';
    
    protected $_code = self::CODE;
    protected $_isInitializeNeeded = true;
    protected $_isGateway = true;
    protected $_canOrder = true;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canRefund = false;
    protected $_canRefundInvoicePartial = false;
    protected $_canVoid = false;
    protected $_canUseInternal = false;
    protected $_canUseCheckout = true;
    protected $_canUseForMultishipping = false;
}
