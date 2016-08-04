<?php

class Culqi_Pago_Model_Cifrado
{
      protected $key;
      protected $cipher = MCRYPT_RIJNDAEL_128;
      protected $mode = MCRYPT_MODE_CBC;

      /**
      *
      * @param type $key
      */
      function __construct($key = null) {
        $this->setBase64Key($key);
      }

      /**
      * [setBase64Key description]
      * @param [type] $key [description]
      */
      public function setBase64Key($key) {
        $this->key = base64_decode($key);
      }

      /**
      *
      * @return boolean
      */
      private function validateParams() {
        if ($this->key != null) {
          return true;
        } else {
          return false;
        }
      }

      private function generateIV() {
        return mcrypt_create_iv(16, MCRYPT_RAND);
      }

      /**
      * @return type
      * @throws Exception
      */
      public function urlBase64Encrypt($data) {

        if ($this->validateParams()) {
          $blocksize = mcrypt_get_block_size($this->cipher, $this->mode);
          $paddedData = Culqi_Pago_Model_Cifrado::pkcs5_pad($data, $blocksize);
          $iv = $this->generateIV();
          return trim(Culqi_Pago_Model_Cifrado::base64_encode_url($iv.mcrypt_encrypt($this->cipher, $this->key, $paddedData, $this->mode, $iv)));
        } else {
          throw new Exception('Invlid params!');
        }
      }

      /**
      *
      * @return type
      * @throws Exception
      */
      public function urlBase64Decrypt($data) {
        if ($this->validateParams()) {
          $rawData = Culqi_Pago_Model_Cifrado::base64_decode_url($data);
          $iv = substr($rawData, 0, 16);
          $encData = substr($rawData, 16);
          return trim(Culqi_Pago_Model_Cifrado::pkcs5_unpad(mcrypt_decrypt($this->cipher, $this->key, $encData, $this->mode, $iv)));
        } else {
          throw new Exception('Invlid params!');
        }
      }

      /**
      * [pkcs5_pad description]
      * @param  [type] $text      [description]
      * @param  [type] $blocksize [description]
      * @return [type]            [description]
      */
      public static function pkcs5_pad($text, $blocksize)
      {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
      }

      /**
      * [pkcs5_unpad description]
      * @param  [type] $text [description]
      * @return [type]       [description]
      */
      public static function pkcs5_unpad($text)
      {
        $pad = ord($text{strlen($text)-1});
        if ($pad > strlen($text)) return false;
        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) return false;
        return substr($text, 0, -1 * $pad);
      }

      /**
      * [base64_encode_url description]
      * @param  [type] $str [description]
      * @return [type]      [description]
      */
      protected function base64_encode_url($str) {
        return strtr(base64_encode($str), "+/", "-_");
      }

      /**
      * [base64_decode_url description]
      * @param  [type] $str [description]
      * @return [type]      [description]
      */
      protected function base64_decode_url($str) {
        return base64_decode(strtr($str, "-_", "+/"));
      }

}
