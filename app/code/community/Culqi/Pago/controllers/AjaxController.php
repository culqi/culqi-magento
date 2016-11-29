<?php
class Culqi_Pago_AjaxController extends Mage_Core_Controller_Front_Action {

  public function indexAction() {
        $this->loadLayout();
        $this->renderLayout();
  }

  // Chequeamos la respuesta de CulqiJS.respuesta
  public function checAction()
  {
    $datosDeVentaRealizada = $this->getRequest()->getPost('informacionDeVentaCifrada');

    // Tratar respuestas incorrectas
    // Se recibe la respuesta (información de la venta) cifrada a través de una petición POST
    //$datosDeVentaRealizada = $_POST['informacionDeVentaCifrada'];


    // Si determinamos que la respuesta esta cifrada, la cambiaremos a true
    $respuestaCifrada = false;


    switch ($datosDeVentaRealizada) {
        case 'checkout_cerrado':
            $datosDeVentaRealizada = 'Ha cerrado el formulario de pago.';
            break;
        case 'error':
            $datosDeVentaRealizada = 'Ha ocurrido un error mientras CULQI procesaba la transacción.';
            break;
        case 'parametro_invalido':
            $datosDeVentaRealizada = 'La información enviada para cargar el formulario de pago es inválida.';
            break;
        case 'venta_expirada':
            $datosDeVentaRealizada = 'La venta ha expirado, ya que han pasado 10 minutos.';
            break;
        default:
            $respuestaCifrada = true;
            // Se descifra la llave
            $datosDeVentaRealizada = Mage::helper('pago')->descifrar($datosDeVentaRealizada);
            // Se convierte en objeto los datos de la venta
            $datosDeVentaRealizada = json_decode($datosDeVentaRealizada);
            break;
    }


  //  $this->getResponse()->clearHeaders()->setHeader('Content-type','application/json',true);
    //  $this->getResponse()->setBody(json_encode($data));



      //Encode Result in JSON
      $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($datosDeVentaRealizada));

      return $datosDeVentaRealizada;


    // Si es correcto, mandar a vista exitosa (POST)
    //$this->loadLayout();
    // $this->renderLayout();


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


       // ======================


       // Incluir clase Culqi
       $objCulqi = Mage::getModel('pago/culqi');

       // Crear Cargo
       $cargo = $objCulqi->crearCargo($tokenId,
                                      $currency,
                                      $Total,
                                      $ProductName,
                                      $orderId,
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
