<?php 

class Culqi_Pago_WebhookController extends Mage_Core_Controller_Front_Action {

    public function indexAction() {
        $this->loadLayout(false);
        $this->renderLayout();
    }

    public function eventAction() {

        // Recepción de evento 
        $inputJSON = file_get_contents('php://input');
        $input = json_decode($inputJSON); 
        $data = json_decode($input->data);       
           
        Mage::log('Llega peticion a webhook');

        if($input->object == 'event' && $input->type == 'order.status.changed') {    
            
            // Obtener informacion de la orden desde Culqi

            $culqi = Mage::getModel('pago/culqi');  
            $orderReq = $culqi->consultarOrden($data->id);   

            Mage::log("response  ==>  ".$orderReq, null, 'system.log', true);        
            $orderReq = json_decode($orderReq, true); 

 
            $mgtOrderId = $data->metadata->mgt_order_id;  

            Mage::log('Evento de Culqi, cambio de orden identificado. Orden: '.$mgtOrderId);
            
            $order = Mage::getModel('sales/order')->loadByIncrementId($mgtOrderId);  

            if($order && $order->getState() != Mage_Sales_Model_Order::STATE_PROCESSING){  
            
                if($orderReq->state == 'paid'){  

                  Mage::log('Orden pagada');      
                  $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true, 'Venta completada. El pago se realizó con éxito.');
                  $order->save();
                } 

                if($orderReq->state == 'expired') {

                  Mage::log('Orden expirada');  
                  $order->setState(Mage_Sales_Model_Order::STATE_CANCELED, true, 'Venta expirada, el pago no se completó.');
                  $order->save();
                }      
            }           
            
            
        }

    }
    


}