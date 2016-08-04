# Culqi Magento Extension


## Instalación

Dentro de la carpeta /app es el punto de partida.

###Paso 1: Subir el plugin a tu magento
Copiar la carpeta "app\code\community\Culqi" dentro de tu "app\code\community"

###Paso 2: Subir los templates a tu theme (.pthmls)

Copiar la carpeta "app\design\frontend\base\default\template\pago" dentro de tu "app\design\frontend\base\default\template\"

###Paso 3: Subir xml del modulo

Subir el archivo "\app\etc\modules\Culqi_Pago.xml" dentro de tu "app\etc\modules"

## Configuración

Luego de intalado. Para poder configurar el plugin de Magento debes ingresar a System > Configuration

Una vez ahi, en los menos laterales, ir a ADVANCED > Advanced.

Dentro podrás ubicar un acordeón disponible para Culqi, no te olvides guardar los cambios.



## Guía de Uso de Magento e Instalación de Culqi (old)

###Paso 1: Crear Categorías de tus Productos

En la pestaña Catalog, hacer clic en Manage Categories

Ahí hacer clic en Add Root Category y luego rellenar los siguientes datos.

En General Information:

* Name: Nombre de una categoría de tus productos
* Is Active: (Yes) Disponibilidad de la categoría
* Description: Descripción de la categoría
* Image: Sube la imagen del producto desde tu PC
* Page Title: Título de la página de dicha categoría cuando el cliente le de click
* Meta Key Words: (Omitir, en desuso ya que los motores no usan los keywords actualmente) Palabras clave para indexar tus productos en motores de búsqueda.
* Meta Description: Descripción que mostrarán los motores de búsqueda.
* Include In Navigation Menu: (Yes) Visibilidad de la categoría en tu tienda

En Display Settings:

* Display Mode: (Products Only) Qué contenido verá el cliente como resumen de tu producto, dejarlo en Products Only lo hará más amigable para el usuario.
* CMS Block: (Omitir) Muestra un bloque estático en la parte superior de la página de dicha categoría.
* Is Anchor: (Yes) Filtros por atributos
* Available Products Listing Sort By: (Use config settings) Personaliza la opción de clasificación.
* Default Product Listing Sort By: (Use config settings) Define la opción Sort By.

En Custom Design:

* Use Parent Category Settings: (No) Incluir o heredar características de otra categoría
* Apply To Products: (No) Aplicar configuraciones personalizadas a todos los productos en esta categoría.
* Custom Design: (No) Especifica un tema personalizado para ser usado por cierto tiempo.
* Active From: (Omitir) Determina la fecha de inicio cuando un tema está activo.
* Active To: (Omitir) Determina la fecha de finalización cuando un tema está activo.
* Page Layout: (Omitir) Especifica una plantilla diferente que se usará para la página de dicha categoría
* Custom Layout Update: (Omitir) Puedes personalizar aun más el tema con HTML.

Click en Save Category, ahora tendrás una nueva categoría.

De la misma forma puedes ir agregando las categorías que necesites.


### Pase 2: Agregar Nuevos Productos

En la pestaña Catalog, hacer clic en Manage Products.

Ahí hacer clic en Add Product y luego rellenar los siguientes datos:

* Attribute Set: (Default) Determina que atributo usar para el nuevo producto.
* Product Type: (Simple product) Determina qué tipo de producto es. Existe varios tipos de producto.
* Simple Product: Producto físico simple que es vendido en la tienda. Cada producto tiene su unidad de mantenimiento, precio, inventario.
* Grouped Product: Es un conjunto de productos simples. Suelen ser usados para propósitos promocionales.
* Configurable Product: Por ejemplo, una maleta puede venir en 4 colores y 2 tamaños diferentes. Será un solo producto configurable con dos opciones: color y tamaño, teniendo en total 8 productos simples distintos. Sus inventarios, precios y otros atributos serán manejados como parte del mismo producto.
* Virtual Product: Representa los servicios que pueden ser ofrecidos en la tienda.
* Bundle Product: Permite a tus clientes armar los productos que ellos quieran. Por ejemplo, tomemos una computadora. Con este tipo de producto, tus clientes pueden elegir entre varios monitores, capacidades de disco duro, memoria ram que ellos requieran, la velocidad de procesamiento, y así calcular el precio final del producto.
* Downloadable Product: Productos que pueden ser descargados, como por ejemplo el software, archivos de música, películas, libros en pdf, revistas, etc. pueden ser vendidos.

Hacer clic en Continue y rellenar los siguientes datos.

En la pestaña General:

* Name: Nombre del producto, será visible para tus clientes.
* Description: Descripción larga, solo será visible cuando tus clientes vean la página del producto para ver más información.
* Short Description: Descripción corta visible al momento de añadirlos al carrito de compras.
* SKU: Store Keeping Unit, es el Identificador que tú mismo le darás a dicho producto. Por ejemplo, si vendes zapatillas marca Nike, de color blanco y de talla 38, un SKU que podrías usar sería ZNBT38 por ser zapatillas nike blancas de talla 38.
* Weight: El peso de cada producto. Debes especificarlo en números, Magento utilizará la unidad por defecto que escogiste en la instalación.
* Set Product as New from Date: Fecha desde que se agregó el producto que verá tu usuario.
* Set Product as New to Date: Fecha hasta la que verá el producto como nuevo.
* Status: (Enabled) Disponibilidad del producto.
* Delivery: (EXPRESS) Tipo de entrega
* URL Key: URL definida por ti que verá el usuario en la barra de direcciones de su navegador
* Visibility: (Catalog, Search) Determina si es visible para tu usuario en el catálogo o para su búsqueda, es necesario para que pueda mostrarse en la tienda.
* Country of Manufacture: (Omitir) País en el que fue hecho.

Luego hacer clic en la pestaña Prices y rellenar los siguientes datos.

En la pestaña Prices:

* Price: El precio al que venderás dicho producto. Por ejemplo, 49.00. Solo el número, no necesitas especificar la moneda, ya que se usará la moneda por defecto que escogiste al instalar Magento.
* Group Price: Grupos de precios para un grupo de usuarios específico.
* Special Price: Precio especial al que venderás en situaciones determinadas por ofertas, días festivos, o lo que tú mismo escojas.
* Special Price from Date: Fecha desde la que estará vigente el precio especial.
* Special Price to Date: Fecha hasta la que estará vigente el precio especial.
* Tier price: Precio rebajado al que podrás vender tus productos, puedes configurarlo para que tu usuario pueda ver el precio normal tachado y este precio en rebaja como precio de venta.
* Apply Map: (Use config)
* Display Actual Price: (Use config) Precio que se mostrará al usuario.
* Manufacturer’s Suggested Retail Price: Precio de venta sugerido por el fabricante
* Tax Class: Categoría de tributación al que pertenece el producto. Puede ser None (Ninguno), Taxable Goods (Bienes gravados) o Shipping (Envío).

Después hacer clic en la pestaña Images.

Ahí hacer clic en Browse Files y seleccionas la imagen de tu PC.

Después haces clic en Upload Files para subirla y que sea visible como producto en tu tienda.

Luego hacer clic en la pestaña Inventory y rellenar los siguientes datos.

En la pestaña Inventory:

* Manage Stock: (Yes o Use Config Settings) Si deseas que Magento automáticamente realice el inventario de tus productos.
* Qty: Cantidad que habrá en el almacén de dicho producto, debe ser mayor que cero para que Magento permita la venta de dicho producto.
* Qty for Item’s status to Become Out of Stock: (Use Config Settings) Cantidad en el que se considerará que el producto está fuera de stock.
* Minimum Qty Allowed in Shopping Cart: (1 ó Use config settings) Cantidad mínima permitida en el carrito de compras de tu usuario.
* Maximum Qty Allowed in Shopping Cart: (Use config settings) Cantidad máxima permitida en el carrito de compras de tu usuario.
* Qty Uses Decimals: Dice si admite tener decimales del producto. Dependiendo del tipo de producto, puedes permitir cantidad decimales o solo enteros. Por ejemplo, puedes tener 2.5 kg de arroz pero no 2.5 mochilas, este último no debe usar decimales. Aquí debes usar tu propio criterio para determinar qué productos pueden tener cantidades decimales y aquellos que no.
* Can Be Divided into Multiple Boxes for Shipping: Puede ser dividido en múltiples cajas para envío.
* Backorders: (No Backorders o Use config settings) Pedido pendiente
* Notify for Quantity Below: Mostrar notificaciones cuando la cantidad de este producto es baja. Aquí determinas la cantidad con la que llegará notificaciones si el stock llega a dicha cantidad.
* Enable Qty Increments: (No o Use config settings) Permitir el incremento de la cantidad de este producto.
* Stock Availability: (In Stock) Le dice a Magento si el producto está disponible en stock o no. De poner Out of Stock, Magento no lo mostrará en la tienda.

Hacer clic en la pestaña Categories

Selecciona la(s) categoría(s) a las que quieres que pertenezca el producto.

Por último le das clic a Save en la parte superior derecha, o a Save and Continue Edit si deseas seguir modificando más opciones.

Con esto ya tendrás un nuevo producto en el inventario de tu tienda.


### Paso 3: Mostrar las productos en Magento

Aunque tengas creado los productos, Magento no los mostrará a menos que lo especifiques en la página correcta.

Para esto en el Panel de Magento, en la pestaña CMS, le damos clic a Pages.

Aquí veremos las páginas que tenemos. Magento incluye en su instalación las páginas About Us, Customer Service, Home Page, 404 Not Found y Privacy Policy.

Podemos crear nuevas páginas haciendo clic en Add New Page; pero para este ejemplo usaremos la página principal Home Page, ahí mostraremos todos los productos que tenemos.

Le damos clic a Home Page

Ahí hacemos clic en la pestaña Content de la izquierda.

Aparecerá un editor de texto de la página que seleccionamos. A su vez veremos varias opciones o mini botones de distintas funciones. Uno de los primeros tiene la imagen del logo de Magento con un parche encima llamado Insert Widgets.

Para agregar los productos hacemos clic en el mini botón Insert Widgets. Te aparecerá una ventana de configuración.

Ahí escoges el tipo de widget a usar. Para mostrar productos escogemos la opción que dice Catalog New Products List. Aparecerá más opciones y escogeremos las siguientes.
* Display Type: Escogemos qué productos mostrar. Si escogemos “All products”, se mostrará todos los productos empezando por los recién agregados. En cambio, si escogemos “New products”, solo se mostrará aquellos productos que marcados como nuevos. Escogeremos “All products”.
* Display Page Control: (No) Muestra página de control.
* Number of Products to Display: Cantidad de productos que desees que se muestre por cada página. Si pones 10, se verán los últimos 10, y para ver los 10 siguientes el usuario tendrá que dar clic en Siguiente. Si son demasiados podría hacer hacer que la página demore más en cargar.
* Template: (New Products Grid Template) Escoges en qué forma se mostrará los productos, como lista o como rejillas.
* Cache Lifetime: Duración de la caché

Luego haces clic en Insert Widget.

Podrás ver que efectivamente se agregó el widget. Ahora para guardar los cambios, le das clic en Save Page.

Ahora solo tienes que ir a tu página de inicio y ver tus productos.

Ya estás listo para integrarte con Culqi.


### Paso 4: Instalación de Culqi para Magento

Una vez descargado el archivo .zip de culqi, lo descomprimimos. Tendremos una carpeta llamada “Magento Culqi”.

Dentro habrá otra carpeta llamada “Magento Culqi”, dentro de esta carpeta estará otra carpeta llamada “Culqi”, y dentro está la carpeta “Pago”.

La carpeta Pago es la extensión de Culqi para Magento. Debes copiarla dentro de la siguiente ruta en la carpeta de Magento.

app/code/community/

Dentro de la carpeta community debes copiar la carpeta Pago. Una vez que la carpeta Pago esté dentro de la carpeta community, la extensión de Culqi estará habilitada para procesar pagos.

A diferencia de otras plataformas, el checkout de Culqi será un formulario en la misma página, no una ventana.

Para corroborar que Culqi está integrado exitosamente en Magento, realizaremos una compra de prueba.

Agregamos el producto al carrito de compras al dar clic a ADD TO CART.

Luego hacemos clic en PROCEED TO CHECKOUT

Escogemos la opción Checkout as Guest (como invitado) y damos clic a CONTINUE.

Ahora veremos un formulario de datos. Luego de rellenar los datos, y dar clic en CONTINUE. Nuevamente en CONTINUE, llegaremos al paso 5.

Aquí escogeremos la opción Credit Card (save) y podremos ver el formulario de pago de Culqi.

Llenamos los datos de la tarjetacorrectamente, le damos a CONTINUE y podremos ver un resumen de tu compra antes de confirmar.

Para confirmar la orden damos clic en PLACE ORDER y podremos ver un mensaje de compra realizada con éxito.

Ahora ya estás integrado con Culqi y puedes empezar a dominar el mundo.
