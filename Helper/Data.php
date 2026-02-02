<?php

namespace Culqi\Pago\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\ObjectManager;

class Data extends AbstractHelper
{
    public function getConfig($config_path)
    {
        return $this->scopeConfig->getValue(
            $config_path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    
    function generate_token($is_admin = false)
    {
        $public_key = $this->get_public_key();
        $rsa_pk_culqi = $this->scopeConfig->getValue(
            "payment/culqi/rsa_pk_culqi",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ) ?? '';

        if (empty($rsa_pk_culqi)) {
            $resource = ObjectManager::getInstance()->get('Magento\\Framework\\App\\ResourceConnection');
            $connection = $resource->getConnection();
            $tableName = $resource->getTableName('core_config_data');
            $query = "SELECT value FROM $tableName WHERE path = 'payment/culqi/rsa_pk_culqi' LIMIT 1";
            $rsa_pk_culqi = $connection->fetchOne($query) ?: '';
        }
        try {
            $minutes = EXPIRATION_TIME;
            $expirationTimeInSeconds = $minutes * 60;
            $exp = time() + $expirationTimeInSeconds;

            if(!$rsa_pk_culqi) {
                if(!$is_admin) {
                    
                    return '';
                }
            } else {
                $data = [
                    "pk" => $public_key,
                    "exp" => $exp
                ];
        
                $encryptedData = $this->encrypt_data_with_rsa(json_encode($data), $rsa_pk_culqi);
                return $encryptedData;
            }
        } catch(\Exception $e) {
            return '';
        }
    }

    function encrypt_data_with_rsa(string $jsonData, string $publicKeyString): ?string {
        try {
            $publicKey = openssl_pkey_get_public($publicKeyString);
            if ($publicKey === false) {
                throw new \Exception("Invalid public key: " . openssl_error_string());
            }
    
            $encrypted = '';
            $result = openssl_public_encrypt($jsonData, $encrypted, $publicKey, OPENSSL_PKCS1_OAEP_PADDING);
            
            if (PHP_VERSION_ID < 80000) {
                openssl_free_key($publicKey);
            }
    
            if ($result === false) {
                throw new \Exception("Encryption failed: " . openssl_error_string());
            }
    
            return base64_encode($encrypted);
        } catch (\Exception $e) {
            return null;
        }
    }

    function get_public_key()
    {
        $public_key = $this->scopeConfig->getValue(
            "payment/culqi/pk",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ) ?? '';

        if (empty($public_key)) {
            $resource = ObjectManager::getInstance()->get('Magento\\Framework\\App\\ResourceConnection');
            $connection = $resource->getConnection();
            $tableName = $resource->getTableName('core_config_data');
            $query = "SELECT value FROM $tableName WHERE path = 'payment/culqi/pk' LIMIT 1";
            $public_key = $connection->fetchOne($query) ?: '';
        }
        
        return $public_key;
    }
    
    function get_payment_methods()
    {
        $payment_methods = $this->scopeConfig->getValue(
            "payment/culqi/payment_methods",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ) ?? '';

        if (empty($payment_methods)) {
            $resource = ObjectManager::getInstance()->get('Magento\\Framework\\App\\ResourceConnection');
            $connection = $resource->getConnection();
            $tableName = $resource->getTableName('core_config_data');
            $query = "SELECT value FROM $tableName WHERE path = 'payment/culqi/payment_methods' LIMIT 1";
            $payment_methods = $connection->fetchOne($query) ?: '';
        }

        $payment_methods = explode(',', $payment_methods);
        
        return $payment_methods;
    }

    /**
     * Verify JWT token
     * 
     * @param string $token
     * @return bool
     */
    public function verify_jwt_token($token)
    {
        if (empty($token)) {
            return false;
        }

        try {
            $rsa_id_culqi = $this->scopeConfig->getValue(
                "payment/culqi/rsa_id_culqi",
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            ) ?? '';

            if (empty($rsa_id_culqi)) {
                $resource = ObjectManager::getInstance()->get('Magento\\Framework\\App\\ResourceConnection');
                $connection = $resource->getConnection();
                $tableName = $resource->getTableName('core_config_data');
                $query = "SELECT value FROM $tableName WHERE path = 'payment/culqi/rsa_id_culqi' LIMIT 1";
                $rsa_id_culqi = $connection->fetchOne($query) ?: '';
            }

            if (empty($rsa_id_culqi)) {
                return false;
            }

            // Decrypt the token
            $decryptedData = $this->decrypt_data_with_rsa($token, $rsa_id_culqi);
            
            if (!$decryptedData) {
                return false;
            }

            $data = json_decode($decryptedData, true);
            
            if (!isset($data['exp']) || !isset($data['pk'])) {
                return false;
            }

            // Check if token has expired
            if (time() > $data['exp']) {
                return false;
            }

            // Verify public key matches
            $public_key = $this->get_public_key();
            if ($data['pk'] !== $public_key) {
                return false;
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Decrypt data with RSA private key
     * 
     * @param string $encryptedData
     * @param string $privateKeyString
     * @return string|null
     */
    private function decrypt_data_with_rsa($encryptedData, $privateKeyString)
    {
        try {
            $encryptedData = base64_decode($encryptedData);
            
            $privateKey = openssl_pkey_get_private($privateKeyString);
            if ($privateKey === false) {
                throw new \Exception("Invalid private key: " . openssl_error_string());
            }

            $decrypted = '';
            $result = openssl_private_decrypt($encryptedData, $decrypted, $privateKey, OPENSSL_PKCS1_OAEP_PADDING);
            
            if (PHP_VERSION_ID < 80000) {
                openssl_free_key($privateKey);
            }

            if ($result === false) {
                throw new \Exception("Decryption failed: " . openssl_error_string());
            }

            return $decrypted;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get payment type from transaction ID
     * 
     * @param string $id
     * @return string
     */
    public function get_payment_type($id)
    {
        $type = (substr($id, 0, 4) === "ord_") ? "order" : "charge";
        return $type;
    }
}
