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
}
