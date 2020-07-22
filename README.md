# Culqi-magento2

### Pasos para la integración del Metódo de Pago Culqi

#### 1. Registrarse en Culqi   `<link>` : <https://www.culqi.com/>

Así podrás tener acceso al ambiente de pruebas de Culqi `<link>` : <https://integ-panel.culqi.com/>
donde encontrarás tus llaves `<link>` : <https://integ-panel.culqi.com/#/desarrollo/llaves/> 

`Llave publica: pk_test_xxxxxxxxxxxxxx`

`Llave privada: sk_test_xxxxxxxxxxxxxx`

#### 2. Descargar  el Método de Pago de Culqi para Magento 2

`<link>` : <https://github.com/culqi/culqi-magento/releases/tag/v3.0.0> 

##### 2.1
![Imgur](https://i.imgur.com/Bnaxf9W.png)


#### 3. Instalación del Método de Pago de Culqi en tu Magento 2.3

![Imgur](https://i.imgur.com/4iol348.png)

##### 3.1 Después de descargar el archivo .zip debemos descomprimirlo

![Imgur](https://i.imgur.com/gG2QbAp.png)

##### 3.2 Colocar todos estos archivos dentro de las carpetas Culqi\Pago

![imgur](https://i.imgur.com/uvsqBXj.png)

Tendremos entonces la carpeta Culqi que contiene la carpeta Pago el cual contiene nuestros
archivos.

##### 3.3 Seguir los siguientes pasos:

```Markdown 
Paso 1: Subir el nuevo modulo a tu Magento
Copiar la carpeta "Culqi" dentro de tu "app\code\"

De no existir la carpeta \code, crearla.
```

Para los siguientes pasos nos situamos en tu carpeta principal de magento:

![Imgur](https://i.imgur.com/zEKnyGk.png)

```Markdown 
Paso 2: Habilitar el nuevo modulo Culqi (importante verificar el php usado y correrlo correctamente).

$ php bin/magento module:enable Culqi_Pago
```

```Markdown 
Paso 3: Correr el comando setup:upgrade.

$ php bin/magento setup:upgrade
```

```Markdown 
Paso 4: Correr el comando cache:flush
$ php bin/magento cache:flush
```
> Ten cuidado con los permisos de las carpetas y archivos en Magento 2 ! 
`<link>` : <https://devdocs.magento.com/guides/v2.4/install-gde/prereq/file-system-perms.html/> 

#### 4. Configurar el Método de pago de Culqi en tu administrador de Magento 2.3

##### 4.1 Panel principal 

![Imgur](https://i.imgur.com/dADyL3a.png)

##### 4.2 Sales

![Imgur](https://i.imgur.com/zQ6N4HY.png)

##### 4.3 Payment methods

![Imgur](https://i.imgur.com/axDBf3r.png)

##### 4.4 Nos dirigimos al último 

![Imgur](https://i.imgur.com/bmFrxPs.png)

##### 4.5 Configuración de llaves

![Imgur](https://i.imgur.com/uZc8FMk.png)

### Finalmente debes tener a Culqi como pasarela de pago de esta manera:

![Imgur](https://i.imgur.com/1xWAyX3.png)

##### Configura el nombre de tu tienda

Tip para definir el nombre de tu tienda en el checkout de Culqi.

##### 1. Nos dirigimos al panel de administración de Magento

###### 1.1 Ubicamos la sección All Stores.

![Imgur](https://i.imgur.com/1mviyes.png)


###### 1.2 Seleccionamos Magento Commerce en la Seccion Web Store.

![Imgur](https://i.imgur.com/NsyzdDW.png)

###### 1.3 Configuramos el nombre deseado.

![Imgur](https://i.imgur.com/tCVGnYj.png)


### Webhook

Puedes activar un Webhook desde el panel de integración de Culqi para mantener el status de una orden pagada por pagoefectivo.

```Markdown 
Paso 1: Ir al panel de integración de culqi, en la sección Eventos y luego a Webhooks.
```

![Imgur](https://i.imgur.com/yfFo29t.png)


```Markdown 
Paso 2: Configuramos nuestra url con el Webhook order.status.changed 

Importante: la dirección /pago/webhook/event siempre se mantendrá.
```

![Imgur](https://i.imgur.com/Jv0CwEp.png)

Y con esto tenemos activado nuestro Webhook para que nuestras ventas pagadas por pagoefectivo se encuentren monitoreadas y actualizadas en todo momento.

> Recordar que la dirección aqui registrada no puede ser local y debe iniciar con https://

> Si tienes dudas con lo que es un Webhook puedes consultar el siguiente enlace: 
`<link>` : < https://docs.culqi.com/#/desarrollo/webhooks/> 


### Pase a producción:

#### 1. Cumplir con los requisitos técnicos

`<link>` : < https://culqi.com/docs/#/desarrollo/produccion/> 

#### 2. Activar comercio desde tu panel de integración de Culqi

![Imgur](https://i.imgur.com/wVOz6cc.png)

> Si tienes más dudas con respecto al proceso de "Activación de comercio" escríbenos a unete@culqi.com

Cuando te envien los accesos a tu panel de producción de Culqi debes reemplazar
tus llaves de pruebas por tus llaves de producción como en el paso 4.2 

`Llave publica: pk_live_xxxxxxxxxxxxxx`

`Llave privada: sk_live_xxxxxxxxxxxxxx`

> En el ambiente de producción podrás comenzar a usar tarjetas reales.


### Si tienes dudas de integración escríbenos a integrate@culqi.com

### Si tienes dudas comerciales escríbenos a unete@culqi.com
