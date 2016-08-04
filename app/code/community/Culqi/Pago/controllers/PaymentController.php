<?php
// app/code/local/Envato/Custompaymentmethod/controllers/PaymentController.php
class Culqi_Pago_PaymentController extends Mage_Core_Controller_Front_Action
{
  public function gatewayAction()
  {
    if ($this->getRequest()->get("orderId"))
    {
      $arr_querystring = array(
        'flag' => 1,
        'orderId' => $this->getRequest()->get("orderId")
      );

      Mage_Core_Controller_Varien_Action::_redirect('pago/payment/response', array('_secure' => false, '_query'=> $arr_querystring));
    }
  }

  // Pagina intermedia (gateway), aqui Generar la venta
  public function redirectAction()
  {

    /* ============== ACCIÓN: Crear la venta ============*/
    $pago = Mage::getModel('pago/paymentgo');

    $checkout = Mage::getSingleton('checkout/session');
    $orderId =  $checkout->getLastRealOrderId();


    // Obteniendo Data de la sesion
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

    // Un array con todos los parametros

    // Generar un pedido aleatorio (for development - debug)
    $listOfCharacters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $string = str_shuffle($listOfCharacters);
    $string = substr($string, 0, 15);


    $datosDeClienteVenta = array(
      //Apellidos del cliente
      'apellidos'          => $CustomerLastName,
      //Ciudad del cliente
      'ciudad'             => $city,
      //Código de país del cliente
      'cod_pais'           => $codPais,
      //Correo electrónico del cliente
      'correo_electronico' => $CustomerEmail,
      //Dirección del cliente
      'direccion'          => $Address,
      //Nombre o nobres del  cliente
      'nombres'            => $CustomerName,
      //Número de teléfono del cliente
      'num_tel'            => $telephone,

      //Identificador de usuario del cliente
      'id_usuario_comercio' => $CustomerID,
      //Descripción de la venta
      'descripcion' => $ProductName,
      //Moneda de la venta ("PEN" O "USD")
      'moneda' => $currency,
      //Monto de la venta (ejem: 10.25, no se incluye el punto decimal)
      'monto' => $Total,
      //Número de pedido de la venta, y debe ser único (de no ser así, recibirá como respuesta un error)
      'numero_pedido' => $string

    );

    // Crear venta
    $data_venta = $pago->crearDatospago($datosDeClienteVenta);

    // Info venta (para vista)
    $informacionVenta = $data_venta['informacion_venta'];


    $this->loadLayout();
    $block = $this->getLayout()->createBlock('Mage_Core_Block_Template','pago',array('template' => 'pago/redirect.phtml'));

    // Enviar informacion de venta a la vita
    Mage::register('informacion_venta', $informacionVenta);

    $this->getLayout()->getBlock('content')->append($block);
    $this->renderLayout();




  }

  public function responseAction()
  {

    // SI esta todo correcto
    if ($this->getRequest()->get("flag") == "1" && $this->getRequest()->get("orderId"))
    {
      $orderId = $this->getRequest()->get("orderId");
      $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
      //$order->setState(Mage_Sales_Model_Order::STATE_PAID, true, 'Pago correcto.');
      $order->setState(Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW, true, 'Venta correcta, espera a su revisión del pago.');
      $order->save();

      Mage::getSingleton('checkout/session')->unsQuoteId();
      Mage_Core_Controller_Varien_Action::_redirect('checkout/onepage/success', array('_secure'=> false));
    }
    else
    {
      Mage_Core_Controller_Varien_Action::_redirect('checkout/onepage/failure', array('_secure'=> false));
    }
  }
}
