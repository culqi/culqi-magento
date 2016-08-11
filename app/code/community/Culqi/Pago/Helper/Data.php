<?php
// app/code/local/Envato/Custompaymentmethod/Helper/Data.php
class Culqi_Pago_Helper_Data extends Mage_Core_Helper_Abstract
{


  public static $servidorBase;
  public static $apiVersion = '1.0.0';
  public static $sdkVersion = '1.2.0';


  function getPaymentGatewayUrl()
  {
    return Mage::getUrl('pago/payment/gateway', array('_secure' => false));
  }



  // Metodos adicionales (refactorizar despues..)

  /**
   * Obtiene servidor base dependiendo el entorno
   * @return string URL para los requests.
   */
  public static function getApiBase()
  {

      self::$servidorBase = "https://integ-pago.culqi.com";

      return self::$servidorBase;
  }

  /**
   * Cifra mediante la llave secreta
   * @param  string $txt Cadena a cifrar
   * @return string      Cadena cifrada
   */
  public static function cifrar($txt) {
      $aes = Mage::getModel('pago/cifrado');
      $aes->setBase64Key(Mage::getStoreConfig('payment/pago/llave_codigo_comercio'));
      return $aes->urlBase64Encrypt($txt);
  }

  /**
   * Descifra mediante la llave secreta
   * @param  string $txt Cadena cifrada
   * @return string      Cadena descifrada
   */
  public static function descifrar($txt) {


      $aes = Mage::getModel('pago/cifrado');
      $aes->setBase64Key(Mage::getStoreConfig('payment/pago/llave_codigo_comercio'));
      return $aes->urlBase64Decrypt($txt);
  }




}
