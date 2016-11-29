<?php

class Culqi_Pago_Model_Paymentgo {

    const URL_VALIDACION_AUTORIZACION = "/api/v1/web/crear/";
    const URL_ANULACION = "/api/v1/devolver/";
    const URL_CONSULTA = "/api/v1/consultar/";

    const PARAM_COD_COMERCIO = "codigo_comercio";
    const PARAM_EXTRA = "extra";
    const PARAM_SDK_INFO = "sdk";

    const PARAM_NUM_PEDIDO = "numero_pedido";
    const PARAM_MONTO = "monto";
    const PARAM_MONEDA = "moneda";
    const PARAM_DESCRIPCION = "descripcion";

    const PARAM_COD_PAIS = "cod_pais";
    const PARAM_CIUDAD = "ciudad";
    const PARAM_DIRECCION = "direccion";
    const PARAM_NUM_TEL = "num_tel";

    const PARAM_INFO_VENTA = "informacion_venta";
    const PARAM_TICKET = "ticket";

    const PARAM_VIGENCIA = "vigencia";

    const PARAM_CORREO_ELECTRONICO = "correo_electronico";
    const PARAM_NOMBRES = "nombres";
    const PARAM_APELLIDOS = "apellidos";
    const PARAM_ID_USUARIO_COMERCIO = "id_usuario_comercio";

      private static function getSdkInfo() {
          return array(
              "v" => '1.0.0',
              "lng_n" => "php",
              "lng_v" => phpversion(),
              "os_n" => PHP_OS,
              "os_v" => php_uname()
          );
      }


    /**
     * [crearDatospago description]
     * @param  [type] $params [description]
     * @param  [type] $extra  [description]
     * @return [type]         [description]
     */
    public static function crearDatospago($params, $extra = null) {
        Culqi_Pago_Model_Paymentgo::validateParams($params);

        $cipherData = Culqi_Pago_Model_Paymentgo::getCipherData($params, $extra);
        $validationParams = array(
            Culqi_Pago_Model_Paymentgo::PARAM_COD_COMERCIO => Mage::getStoreConfig('payment/pago/codigo_comercio'),
            Culqi_Pago_Model_Paymentgo::PARAM_INFO_VENTA => $cipherData
        );

        $response = Culqi_Pago_Model_Paymentgo::validateAuth($validationParams);
        if (!empty($response) && array_key_exists(Culqi_Pago_Model_Paymentgo::PARAM_TICKET, $response)) {
            $infoVenta = array(
                Culqi_Pago_Model_Paymentgo::PARAM_COD_COMERCIO => $response[Culqi_Pago_Model_Paymentgo::PARAM_COD_COMERCIO],
                Culqi_Pago_Model_Paymentgo::PARAM_TICKET => $response[Culqi_Pago_Model_Paymentgo::PARAM_TICKET]
            );
            $response[Culqi_Pago_Model_Paymentgo::PARAM_INFO_VENTA] = Mage::helper('pago')->cifrar(json_encode($infoVenta));
        }

        return $response;
    }

    /**
     * [consultar description]
     * @param  [type] $token [description]
     * @return [type]        [description]
     */
    public static function consultar($token) {
        $cipherData = Culqi_Pago_Model_Paymentgo::getCipherData(array(
            Culqi_Pago_Model_Paymentgo::PARAM_TICKET => $token
        ));
        $params = array(
            Culqi_Pago_Model_Paymentgo::PARAM_COD_COMERCIO => Mage::getStoreConfig( 'payment/pago/codigo_comercio' ),
            Culqi_Pago_Model_Paymentgo::PARAM_INFO_VENTA => $cipherData
        );
        return Culqi_Pago_Model_Paymentgo::postJson(Mage::helper('pago')->getApiBase() . Culqi_Pago_Model_Paymentgo::URL_CONSULTA, $params);
    }

    /**
     * [anular description]
     * @param  [type] $token [description]
     * @return [type]        [description]
     */
    public static function anular($token) {
        $cipherData = Culqi_Pago_Model_Paymentgo::getCipherData(array(
            Culqi_Pago_Model_Paymentgo::PARAM_TICKET => $token
        ));
        $params = array(
            Culqi_Pago_Model_Paymentgo::PARAM_COD_COMERCIO => Mage::getStoreConfig( 'payment/pago/codigo_comercio' ),
            Culqi_Pago_Model_Paymentgo::PARAM_INFO_VENTA => $cipherData
        );
        return Culqi_Pago_Model_Paymentgo::postJson( Mage::helper('pago')->getApiBase() . Culqi_Pago_Model_Paymentgo::URL_ANULACION, $params);
    }

    /**
     * [getCipherData description]
     * @param  [type] $params [description]
     * @param  [type] $extra  [description]
     * @return [type]         [description]
     */
    private static function getCipherData($params, $extra = null) {
        $endParams = array_merge(array(
            Culqi_Pago_Model_Paymentgo::PARAM_COD_COMERCIO => Mage::getStoreConfig( 'payment/pago/codigo_comercio' ),
        ), $params);
        if (!empty($extra)) {
            $endParams[Culqi_Pago_Model_Paymentgo::PARAM_EXTRA] = $extra;
        }
        $endParams[Culqi_Pago_Model_Paymentgo::PARAM_SDK_INFO] = Culqi_Pago_Model_Paymentgo::getSdkInfo();
        $jsonData = json_encode($endParams);
        return Mage::helper('pago')->cifrar($jsonData);
    }

    /**
     * [validateAuth description]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    private static function validateAuth($params) {
        return Culqi_Pago_Model_Paymentgo::postJson(Mage::helper('pago')->getApiBase(). Culqi_Pago_Model_Paymentgo::URL_VALIDACION_AUTORIZACION, $params);
    }

    /**
     * [validateParams description]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    private static function validateParams($params){
        if (!isset($params[Culqi_Pago_Model_Paymentgo::PARAM_MONEDA]) or empty($params[Culqi_Pago_Model_Paymentgo::PARAM_MONEDA])) {
            throw new InvalidParamsException("[Error] Debe existir una moneda");
        } else if (strlen(trim($params[Culqi_Pago_Model_Paymentgo::PARAM_MONEDA])) != 3) {
            throw new InvalidParamsException("[Error] La moneda debe contener exactamente 3 caracteres.");
        }
        if (!isset($params[Culqi_Pago_Model_Paymentgo::PARAM_MONTO]) or empty($params[Culqi_Pago_Model_Paymentgo::PARAM_MONTO])) {
            throw new InvalidParamsException("[Error] Debe existir un monto");
        } else if (is_numeric($params[Culqi_Pago_Model_Paymentgo::PARAM_MONTO])) {
            if (!ctype_digit($params[Culqi_Pago_Model_Paymentgo::PARAM_MONTO])) {
                throw new InvalidParamsException("[Error] El monto debe ser un número entero, no flotante.");
            }
        } else {
            throw new InvalidParamsException("[Error] El monto debe ser un número entero.");
        }
    }

    /**
     * [postJson description]
     * @param  [type] $url    [description]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    private static function postJson($url, $params) {
        $opt = array(
            'http' => array(
                'header' => "Content-Type: application/json\r\n"
                            . "User-Agent: php-context\r\n",
                'method' => 'POST',
                'content' => json_encode($params),
                'ignore_errors' => true
            )
        );

        $context = stream_context_create($opt);
        $response = file_get_contents($url, false, $context);

        $decryptedResponse = Mage::helper('pago')->descifrar($response);

        return json_decode($decryptedResponse, true);
    }
}
