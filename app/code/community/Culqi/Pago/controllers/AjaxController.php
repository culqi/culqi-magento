<?php
class Culqi_Pago_AjaxController extends Mage_Core_Controller_Front_Action {

  public function indexAction() {
    $this->loadLayout(false);
    $this->renderLayout();
  }

  /* Crear cargo */
  public function checkAction() {

    //Recepcion de respuesta
    $source_id = $this->getRequest()->getPost('token_id');
    $orderId = $this->getRequest()->getPost('order_id');
    $installments = $this->getRequest()->getPost('installments');

    // === Load Order Data ===
    $checkout = Mage::getSingleton('checkout/session');
    $orderId =  $checkout->getLastRealOrderId();

    // Obteniendo Data de la orden
    $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);

    $currency_code  = $order->getOrderCurrencyCode();

    $BAddress = $order->getBillingAddress();

    $address = $order->getShippingAddress()->getStreet1().' '.$order->getShippingAddress()->getStreet2();

    $first_name = $BAddress->getFirstname();

    $last_name = $BAddress->getLastname();

    $email = $order->getCustomerEmail();

    $amount = number_format($order->getGrandTotal(),2,'','');

    $address_city = $BAddress->getCity();

    $phone_number = $BAddress->getTelephone();

    $country_code = 'PE';

    $description = '';
    $items = $order->getAllItems();
    if ($items)
      {
          foreach($items as $item)
          {
            if ($item->getParentItem()) continue;
      $description .= $item->getName() . '; ';
          }
      }
    $description = rtrim($description, '; ');

    // Generar un pedido aleatorio (for development - debug)
    $listOfCharacters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $string = str_shuffle($listOfCharacters);
    $string = substr($string, 0, 15);

    // ======================

    // Incluir clase Culqi
    $objCulqi = Mage::getModel('pago/culqi');

    // Crear Cargo
    $cargo = $objCulqi->crearCargo(
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
      $orderId
    );

    Mage::log("response  ==>  ".$cargo, null, 'system.log', true);

    //Encode resultado en JSON
    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($cargo));

  }

}
