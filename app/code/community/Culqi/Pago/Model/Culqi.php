<?php

class Culqi_Pago_Model_Culqi extends Mage_Payment_Model_Method_Abstract
{

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

    $llave_secreta = Mage::getStoreConfig('payment/pago/llave_secreta');
    $url_base = "https://api.culqi.com/v2";

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

    $client = new Zend_Http_Client($url_base."/charges/");
    $client->setHeaders(
            array(
                'Authorization' => "Bearer ".$llave_secreta,
                'Content-Type' => "application/json",
            )
        );

    $client->setParameterPost($data);
    $json = $client->setRawData($data, null)->request('POST')->getBody();
    return $json;

  }

}
