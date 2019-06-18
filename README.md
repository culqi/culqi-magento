# culqi-magento

### Pasos para la integración del Plugin de Culqi

#### 1. Registrarse en Culqi   `<link>` : <https://www.culqi.com/>

Así podrás tener acceso al ambiente de pruebas de Culqi `<link>` : <https://integ-panel.culqi.com/>
donde encontrarás tus llaves `<link>` : <https://integ-panel.culqi.com/#/desarrollo/llaves/> 

`Llave publica: pk_test_xxxxxxxxxxxxxx`

`Llave privada: sk_test_xxxxxxxxxxxxxx`

#### 2. Descargar  el Plugin de Culqi 2.1.0 

`<link>` : <https://github.com/culqi/culqi-magento/releases/tag/v2.1.0> 

##### 2.1
![Imgur](https://i.imgur.com/eoVyTFZ.png)

##### 2.2
![Imgur](https://i.imgur.com/fBBBiwA.png)

#### 3. Instalar el Plugin de Culqi en tu Magento 1.9

##### 3.1 Después de descargar el archivo .zip debemos descomprimir e ingresar a la carpeta "app"

![Imgur](https://i.imgur.com/vCuLES6.png)

![Imgur](https://i.imgur.com/P6N5EZy.png)

![Imgur](https://i.imgur.com/dm7AeHl.png)

##### 3.2 El contenido de la carpeta "/app" es el inicio de la integración tomar mucha atención 
![Imgur](https://i.imgur.com/7FwPNmU.png)

##### 3.3 Seguir los siguientes pasos:

```Markdown 
Paso 1: Subir el plugin a tu Magento
Copiar la carpeta "app\code\community\Culqi" dentro de tu "app\code\community"
```

```Markdown 
Paso 2: Subir los templates a tu theme (.pthmls)
Copiar la carpeta "app\design\frontend\base\default\template\pago" dentro de tu
"app/design/frontend/base/default/template/"
```

```Markdown 
Paso 3: Subir xml del modulo
Copiar el archivo "\app\etc\modules\Culqi_Pago.xml" dentro de tu "app/etc/modules"
```

```Markdown 
Paso 4: Subir xml del layout AJAX
Copiar el archivo "app\design\frontend\base\default\layout\ajaxlayout.xml" dentro de tu
"app/etc/modules"
```

#### 4. Configurar el Plugin de Culqi en tu administrador de Magento 1.9

##### 4.1
![Imgur](https://i.imgur.com/j1ELo4U.png)

##### 4.2
![Imgur](https://i.imgur.com/zCfpcYm.png)

##### 4.3
![Imgur](https://i.imgur.com/sMjrEoy.png)

![Imgur](https://i.imgur.com/GNXxKkq.png)
> Aqui van tus llaves que mencionamos en el paso 1 ( Registrarse en Culqi ).

### Finalmente debes tener a Culqi como pasarela de pago de esta manera:

![Imgur](https://i.imgur.com/obCgQ5R.png)

> Debes usar las tarjetas de prueba que Culqi te ofrece para hacer las pruebas necesarias

`<link>` : <https://culqi.com/docs/#/desarrollo/tarjetas/> 

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
