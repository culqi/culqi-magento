<?php

class Culqi_Pago_Model_Culqi extends Mage_Payment_Model_Method_Abstract
{
  
  protected $_url_base = "https://api.culqi.com/v2"; 
  protected $_private_key;


  public function __construct() {   
      $this->_private_key = Mage::getStoreConfig('payment/pago/llave_secreta');    
  } 


  public function crearOrden($amount, $currency_code, $description, $first_name, $last_name, 
  $phone_number, $email, $order_id) { 
      
    // Generar numero de orden  
    $preffixOrder = 'MGT'; 
    $actualPreffix = Mage::getStoreConfig('payment/pago/order_preffix');   

    $hoursExpiration = Mage::getStoreConfig('payment/pago/duracion_maxima'); 

    if(!empty($actualPreffix)){ 
      $preffixOrder = $actualPreffix;
    } 
    
    $listOfCharacters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $randomString = str_shuffle($listOfCharacters);
    $randomString = substr($randomString, 0, 10);

    $data =  array(
      'amount' => $amount, 
      'currency_code' => $currency_code, 
      'description' => $description, 
      'order_number' => $preffixOrder.'-'.$randomString,       
      'client_details'=> array(  
        'first_name' => $first_name,
        'last_name' => $last_name,
        'phone_number' => $phone_number, 
        'email' => $email
      ),
      'expiration_date' => time() + $hoursExpiration*60*60,
      'confirm' => false,
      'metadata' => array("mgt_order_id"=>(string)$order_id)
    ); 

    $data = Mage::helper('core')->jsonEncode($data);

    $client = new Zend_Http_Client($this->_url_base."/orders/");
    $client->setConfig(array('timeout'=>120));
    $client->setHeaders(
            array(
                'Authorization' => "Bearer ".$this->_private_key,
                'Content-Type' => "application/json",
            )
        );

    $client->setParameterPost($data);
    $json = $client->setRawData($data, null)->request('POST')->getBody(); 
    return $json;   

  }

  
  public function crearCargo(
      $amount,
      $address,
      $address_city,
      $country_code,
      $first_name,
      $last_name,
      $phone_number,
      $currency_code,
      $description,
      $installments,
      $email,
      $source_id,
      $order_id)
  {

    //$llave_secreta = Mage::getStoreConfig('payment/pago/llave_secreta');
    //$url_base = "https://api.culqi.com/v2";

    $data =  array(
      'amount' => $amount,
      'antifraud_details'=> array(
        'address' => $address,
        'address_city' => $address_city,
        'country_code' => $country_code,
        'first_name' => $first_name,
        'last_name' => $last_name,
        'phone_number' => $phone_number
      ),
      'capture' => true,
      'currency_code' => $currency_code,
      'description' => $description,
      'installments' => $installments,
      'metadata' => array("order_id"=>(string)$order_id),
      'email' => $email,
      'source_id' => $source_id
    );


    $data = Mage::helper('core')->jsonEncode($data);

    $client = new Zend_Http_Client($this->_url_base."/charges/"); 
    $client->setConfig(array('timeout'=>120));
    $client->setHeaders(
            array(
                'Authorization' => "Bearer ".$this->_private_key,
                'Content-Type' => "application/json",
            )
        );

    $client->setParameterPost($data);
    $json = $client->setRawData($data, null)->request('POST')->getBody();
    return $json;

  }

}
