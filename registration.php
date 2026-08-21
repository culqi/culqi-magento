<?php

use Magento\Framework\Component\ComponentRegistrar;

define( 'MPCULQI_PLUGIN_VERSION' , 'v4.0.0');

//By default, we assume that PHP is NOT running on windows.
$isWindows = false;

//If the first three characters PHP_OS are equal to "WIN",
//then PHP is running on a Windows operating system.
if(strcasecmp(substr(PHP_OS, 0, 3), 'WIN') == 0){
    $isWindows = true;
}

define('CULQI_OS', $isWindows);

define( 'CULQI_API_URL' , 'https://ag-online.culqi.com/gateway/' );
define( 'CULQI_CONFIG_URL' , 'https://configonlineplatform.culqi.com' );

define( 'PLUGIN_VERSION', 'v4.1.0');
define( 'PLATFORM' , 'magento' );
define( 'CHECKOUT_VERSION', 'custom_checkout');
define( 'CULQI_3DS', 'culqi_3ds');
define( 'EXPIRATION_TIME' , 15 );

define('USERNAME_WEBHOOK', bin2hex(random_bytes(5)));
define('PASSWORD_WEBHOOK', bin2hex(random_bytes(10)));

ComponentRegistrar::register(ComponentRegistrar::MODULE, 'Culqi_Pago', __DIR__);
