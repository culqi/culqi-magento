<?php

use Magento\Framework\Component\ComponentRegistrar;

define( 'MPCULQI_PLUGIN_VERSION' , 'v3.1.2');

define('URLAPI_INTEG', 'https://integ-panel.culqi.com');
define('URLAPI_PROD', 'https://panel.culqi.com');

define('URLAPI_INTEG_3DS', 'https://3ds.culqi.com');
define('URLAPI_PROD_3DS', 'https://3ds.culqi.com');

define('URLAPI_ORDERCHARGES_INTEG', 'https://api.culqi.com/v2');
define('URLAPI_CHECKOUT_INTEG', 'https://js.culqi.com/checkout-js');
define('URLAPI_LOGIN_INTEG', URLAPI_INTEG.'/user/login');
define('URLAPI_MERCHANT_INTEG', URLAPI_INTEG.'/secure/merchant/');
define('URLAPI_MERCHANTSINGLE_INTEG', URLAPI_INTEG.'/secure/keys/?merchant=');
define('URLAPI_WEBHOOK_INTEG', URLAPI_INTEG.'/secure/events');

define('URLAPI_ORDERCHARGES_PROD', 'https://api.culqi.com/v2');
define('URLAPI_CHECKOUT_PROD', 'https://js.culqi.com/checkout-js');
define('URLAPI_LOGIN_PROD', URLAPI_PROD.'/user/login');
define('URLAPI_MERCHANT_PROD', URLAPI_PROD.'/secure/merchant/');
define('URLAPI_MERCHANTSINGLE_PROD', URLAPI_PROD.'/secure/keys/?merchant=');
define('URLAPI_WEBHOOK_PROD', URLAPI_PROD.'/secure/events');

//By default, we assume that PHP is NOT running on windows.
$isWindows = false;

//If the first three characters PHP_OS are equal to "WIN",
//then PHP is running on a Windows operating system.
if(strcasecmp(substr(PHP_OS, 0, 3), 'WIN') == 0){
    $isWindows = true;
}
//var_dump(strcasecmp(substr(PHP_OS, 0, 3)); exit(1);
//$u_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';

//var_dump($isWindows); exit(1);
define('CULQI_OS', $isWindows);
define('TIME_EXPIRATION_DEFAULT', 24);

define('USERNAME_WEBHOOK', bin2hex(random_bytes(5)));
define('PASSWORD_WEBHOOK', bin2hex(random_bytes(10)));

ComponentRegistrar::register(ComponentRegistrar::MODULE, 'Culqi_Pago', __DIR__);
