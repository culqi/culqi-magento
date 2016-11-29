<?php

class Culqi_Pago_Model_Culqi extends Mage_Payment_Model_Method_Abstract
{
   /**
   * @var string  Código de Comercio
   */
  protected $_cod_comercio;

  /**
   * @var string  API Key
   */
  protected $_api_key;

  /**
   * Carga los archivos necesarios y las llaves Culqi
   * @return void
   */
  protected function _construct()
  {

      // Obtener las llaves Culqi de la DB
      //$culqi = Mage::getModel('culqi_gateway/config')->load(1);
      $this->_cod_comercio = Mage::getStoreConfig('payment/pago/codigo_comercio');
      $this->_api_key = Mage::getStoreConfig('payment/pago/llave_codigo_comercio');





  }


  /**
   * Header de autenticación para requests usando Llave Privada
   * @return void
   */
  protected function setEnv()
  {
    // Entorno - To-do: Verificar el entorno fijado del panel

  }

  public function crearCargo($token, $moneda, $monto, $descripcion, $pedido,
  $codigo_pais, $ciudad, $usuario, $direccion, $telefono, $nombres, $apellidos,
  $correo_electronico)
  {

    $data = [
      'token' => $token,
      'moneda'=> $moneda,
      'monto'=> $monto,
      'descripcion'=> $descripcion,
      'pedido'=> $pedido,
      'codigo_pais'=> $codigo_pais,
      'ciudad'=> $ciudad,
      'usuario'=> $usuario,
      'direccion'=> $direccion,
      'telefono'=> $telefono,
      'nombres'=> $nombres,
      'apellidos'=> $apellidos,
      'correo_electronico'=> $correo_electronico,
    ];


    $data = Mage::helper('core')->jsonEncode($data);

    $client = new Zend_Http_Client('https://integ-pago.culqi.com/api/v1/cargos');
    $client->setHeaders(
            array(
                'Authorization' => 'Bearer eLHsrNFGp0PCnHfSMmW4DmgcYrylYkOVqufX5apTiUg=',
                'Content-Type' => 'application/json',
            )
        );

    //$client->setHeaders('content-type', 'application/json');
    //$client->setHeaders('authorization', 'Bearer eLHsrNFGp0PCnHfSMmW4DmgcYrylYkOVqufX5apTiUg=');
    $client->setParameterPost($data);
    $json = $client->setRawData($data, null)->request('POST')->getBody();
    //$json = $client->request(Zend_Http_Client::POST)->getBody();


    return $json;


  }



}
