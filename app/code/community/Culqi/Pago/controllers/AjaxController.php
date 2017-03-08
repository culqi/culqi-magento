<?php
class Culqi_Pago_AjaxController extends Mage_Core_Controller_Front_Action {

  public function indexAction() {
        $this->loadLayout();
        $this->renderLayout();
  }


  /* Crear cargo */

  public function checkAction() {

       //Recepcion de respuesta
       $tokenId = $this->getRequest()->getPost('token_id');
       $orderId = $this->getRequest()->getPost('order_id');

       // === Load Order Data ===
       $checkout = Mage::getSingleton('checkout/session');
       $orderId =  $checkout->getLastRealOrderId();

       // Obteniendo Data de la orden
       $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
       $currency   = $order->getOrderCurrencyCode();
       $BAddress = $order->getBillingAddress();

       $Address = $order->getShippingAddress()->getStreet1().' '.$order->getShippingAddress()->getStreet2();

       $CustomerName = $BAddress->getFirstname();
       $CustomerLastName = $BAddress->getLastname();
       $CustomerID = $orderId;
       $CustomerEmail = $order->getCustomerEmail();

       $Total = number_format($order->getGrandTotal(),2,'','');

       $city = $BAddress->getCity();
       $telephone = $BAddress->getTelephone();

       $codPais = 'PE';

       $ProductName = '';
       $items = $order->getAllItems();
       if ($items)
         {
             foreach($items as $item)
             {
               if ($item->getParentItem()) continue;
         $ProductName .= $item->getName() . '; ';
             }
         }
       $ProductName = rtrim($ProductName, '; ');

       // Generar un pedido aleatorio (for development - debug)
       $listOfCharacters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
       $string = str_shuffle($listOfCharacters);
       $string = substr($string, 0, 15);


       // ======================


       // Incluir clase Culqi
       $objCulqi = Mage::getModel('pago/culqi');

       // Crear Cargo
       $cargo = $objCulqi->crearCargo($tokenId,
                                      $currency,
                                      $Total,
                                      $ProductName,
                                      $string,
                                      $codPais,
                                      $city,
                                      $CustomerEmail,
                                      $Address,
                                      $telephone,
                                      $CustomerName,
                                      $CustomerLastName,
                                      $CustomerEmail
                                      );

       error_log($cargo);

       $cargo = json_decode($cargo);

       //Encode resultado en JSON
       $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($cargo));
       return $cargo;

  }








}
