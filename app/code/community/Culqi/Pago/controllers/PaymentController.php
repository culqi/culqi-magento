<?php
// PaymentController.php
class Culqi_Pago_PaymentController extends Mage_Core_Controller_Front_Action
{
  public function gatewayAction()
  {
    if ($this->getRequest()->get("orderId"))
    {
      $arr_querystring = array(
        'flag' => 1,
        'orderId' => $this->getRequest()->get("orderId"),
        'statusOrder' => $this->getRequest()->get("statusOrder")
      );

      Mage_Core_Controller_Varien_Action::_redirect('pago/payment/response', array('_secure' => false, '_query'=> $arr_querystring));
    }
  }

  // Pagina intermedia (gateway), aqui Generar la venta
  public function redirectAction()
  { 

    // Nueva orden
    $order = new Mage_Sales_Model_Order();

    $checkout = Mage::getSingleton('checkout/session');
    $orderId =  $checkout->getLastRealOrderId();

    // Obteniendo Data
    $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
    $BAddress = $order->getBillingAddress();
    $first_name = $BAddress->getFirstname();
    $last_name = $BAddress->getLastname();
    $phone_number = $BAddress->getTelephone();
    $currency  = $order->getOrderCurrencyCode();
    $customerEmail = $order->getCustomerEmail();
    $total = number_format($order->getGrandTotal(),2,'',''); 

    $productName = '';
    $items = $order->getAllItems();
    if ($items)
    {
      foreach($items as $item)
        {
          if ($item->getParentItem()) continue;
          $ProductName .= $item->getName() . '; ';
        }
    }
    $productName = rtrim($productName, '; ');  

    // Render 
    $this->loadLayout();
    /*$block = $this->getLayout()
    ->createBlock('Mage_Core_Block_Template','pago',array('template' => 'pago/redirect.phtml'))
    ->setData('orderIdFront','aaaa'); */


    $block = $this->getLayout()->createBlock('core/template')   
    ->setTemplate('pago/redirect.phtml');

    if(Mage::getStoreConfig('payment/pago/active_multipayment')){

      // Generar orden previa  
      $culqi = Mage::getModel('pago/culqi');  
      $orderReq = $culqi->crearOrden($total, $currency, Mage::app()->getStore()->getFrontendName()
      , $first_name, $last_name, 
      $phone_number, $customerEmail, $orderId);   

      Mage::log("response  ==>  ".$orderReq, null, 'system.log', true);        
      $orderReq = json_decode($orderReq, true); 
        
      $block->setData('orderIdFront', $orderReq['id']); 
    }    

    $block->setData('orderId', $orderId); 
    $block->setData('currency', $currency); 
    $block->setData('customerEmail', $customerEmail); 
    $block->setData('total', $total); 
    $block->setData('productName', $productName); 

    $this->getLayout()->getBlock('content')->append($block);
    $this->renderLayout();

  }

  /* AJAX - Procesar respuesta */
  public function responseAction()
  {

    // SI esta todo correcto
    if ($this->getRequest()->get("flag") == "1" && $this->getRequest()->get("orderId") && $this->getRequest()->get("statusOrder") == 'done')
    {
      $orderId = $this->getRequest()->get("orderId");
      $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
      //$order->setState(Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW, true, 'Pago correcto.');
      $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true, 'Venta correcta vía Tarjeta de Crédito (Culqi), espera a su entrega y confirmación.');
      $order->save();

      Mage::getSingleton('checkout/session')->unsQuoteId();
      Mage_Core_Controller_Varien_Action::_redirect('checkout/onepage/success', array('_secure'=> false));
    }

    elseif ($this->getRequest()->get("flag") == "1" && $this->getRequest()->get("orderId") && $this->getRequest()->get("statusOrder") == 'fail') {

      $orderId = $this->getRequest()->get("orderId");
      $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);

      $order->setState(Mage_Sales_Model_Order::STATE_CANCELED, true, 'La orden no fue completada por problemas en el pago.');
      $order->save();

      // Devolver sesion
      $session = Mage::getSingleton('checkout/session');
      if ($session->getLastRealOrderId())
      {
          $order = Mage::getModel('sales/order')->loadByIncrementId($session->getLastRealOrderId());
          if ($order->getId())
          {
              //Cancel order
              if ($order->getState() != Mage_Sales_Model_Order::STATE_CANCELED)
              {
                  $order->registerCancellation("Cancelado por el usuario.")->save();
              }
              $quote = Mage::getModel('sales/quote')->load($order->getQuoteId());
              //Return quote
              if ($quote->getId())
              {
                  $quote->setIsActive(1)
                      ->setReservedOrderId(NULL)
                      ->save();
                  $session->replaceQuote($quote);
              }

              //Unset data
              $session->unsLastRealOrderId();
          }
      }


      return $this->getResponse()->setRedirect( Mage::getUrl('checkout/onepage'));

    } 

    elseif ($this->getRequest()->get("flag") == "1" && $this->getRequest()->get("orderId") && $this->getRequest()->get("statusOrder") == 'pending_payment') {

      $orderId = $this->getRequest()->get("orderId");
      $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);

      $order->setState(Mage_Sales_Model_Order::STATE_HOLDED, true, 'Orden en espera de pago por medio de PagoEfectivo.');
      $order->save();

      Mage::getSingleton('checkout/session')->unsQuoteId();
      Mage_Core_Controller_Varien_Action::_redirect('checkout/onepage/success', array('_secure'=> false));
    }    

    else {

      Mage_Core_Controller_Varien_Action::_redirect('checkout/onepage/failure', array('_secure'=> false));
    }



  }
}
