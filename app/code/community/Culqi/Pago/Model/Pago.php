<?php
class Culqi_Pago_Model_Pago extends Mage_Payment_Model_Method_Abstract {
  protected $_code  = 'pago';
  protected $_formBlockType = 'pago/form_pago';
  protected $_infoBlockType = 'pago/info_pago';

  public function getOrderPlaceRedirectUrl()
  {
    return Mage::getUrl('pago/payment/redirect', array('_secure' => false));
  }
  
}
