# BCNyx

Plantilla local de tienda en PHP 8.2 y MariaDB/MySQL. Incluye catálogo, cuentas, carrito, pedidos, reservas de inventario y recuperación de contraseña. **Por defecto no envía mensajes ni procesa pagos externos.**

## Instalación nueva

1. Instala PHP 8.2+ con `mysqli`, `mbstring`, `curl` y Composer; usa MySQL/MariaDB con InnoDB.
2. Crea una base vacía `base_de_datos_bcnyx` e importa [`bd/instalar_limpio.sql`](bd/instalar_limpio.sql). Los productos de ejemplo se crean con stock cero.
3. Ejecuta `composer install` en `PAGINA/`.
4. Configura las variables de entorno del proceso Apache/PHP según [`.env.example`](.env.example). El archivo `.env.example` es una guía; la aplicación **no carga archivos `.env` automáticamente**. En una petición desde `localhost`, la conexión predeterminada usa `root` sin clave y la base `base_de_datos_bcnyx`. Fuera de localhost, la app falla de forma cerrada si no encuentra `BCNYX_DB_USER` y `BCNYX_DB_PASSWORD`; usa una cuenta dedicada y una clave segura, nunca `root`.
5. Ajusta el stock con `php scripts/ajustar_stock.php 2 S 5` (producto 2, talla S, stock físico total 5).
6. Abre `http://localhost/BCNyx/PAGINA/index.php`.

El volcado [`bd/base_de_datos_bcnyx.sql`](bd/base_de_datos_bcnyx.sql) es histórico y contiene datos de muestra. Para una instalación nueva usa `instalar_limpio.sql`. Para actualizar una instalación con el esquema antiguo, aplica primero `bd/recuperaciones_clave.sql` y luego `bd/002_pedidos.sql` **una sola vez**, después de hacer una copia de seguridad. Si la base ya tenía `pedidos`, aplica [`bd/003_datos_envio_reservas.sql`](bd/003_datos_envio_reservas.sql) una sola vez.

## Estados de pedidos e inventario

- Al recibir un pedido por cualquier método: `estado_pago=pendiente`, `estado_pedido=recibido`; la cantidad aumenta en `reservado`, sin descontar el stock físico.
- La disponibilidad es `stock - reservado`. La reserva se realiza dentro de la transacción del pedido.
- Al registrar el cobro: la reserva baja y el stock físico baja una sola vez.
- Una cancelación antes de cobrar libera la reserva.
- Las reservas de tarjeta y PSE vencen a los 30 minutos. El checkout libera reservas vencidas y `php scripts/expirar_reservas.php` permite hacerlo aunque no haya visitas; programa ese comando cada cinco minutos en un servidor. Contraentrega no vence automáticamente.
- Tras entregar y cobrar, una devolución registrada repone el stock una sola vez. El reembolso debe realizarse antes de registrar la devolución.

En local, las transiciones manuales se hacen **solo desde la consola del servidor**:

```text
php scripts/listar_pedidos.php
php scripts/gestionar_pedido.php confirmar ID REFERENCIA_DEL_COBRO
php scripts/gestionar_pedido.php entregar ID
php scripts/gestionar_pedido.php cancelar ID
php scripts/gestionar_pedido.php devolver ID REFERENCIA_DEL_REEMBOLSO
```

El checkout usa una clave de solicitud única para impedir pedidos duplicados al reenviar el formulario. Los pedidos antiguos de `ordenes` quedan como datos históricos; el flujo nuevo usa `pedidos` y `pedido_items`.

## Integraciones futuras

**Pagos de prueba:** `BCNYX_PAYMENT_DRIVER=demo` (predeterminado) habilita tarjeta, PSE y contraentrega simulados **solo si el navegador y el servidor usan localhost**. Tarjeta y PSE muestran una página local para aprobar o rechazar, sin capturar datos bancarios; contraentrega queda pendiente hasta simular su cobro. Desde la confirmación se puede simular cancelación, entrega y devolución. Las acciones actualizan el inventario y registran eventos en la base, pero nunca mueven dinero. Para desactivar el simulador también en local, configura `BCNYX_PAYMENT_DRIVER=disabled`; en ese estado solo queda disponible contraentrega. **No publiques el simulador en una tienda real.**

**Pasarela real futura:** implementa [`PasarelaPago`](PAGINA/lib/pasarela.php), crea el checkout del proveedor y verifica firma, referencia, importe, moneda y estado de sus eventos antes de llamar a `confirmarPago`. También se necesita el flujo real de reembolsos. Cambiar una variable por sí solo no habilita cobros reales.

**WhatsApp:** cada pedido crea una notificación ligada a su ID. `php scripts/procesar_notificaciones.php` genera mensajes en modo local (`BCNYX_WHATSAPP_DRIVER=log`). Para Twilio configura las variables de [seguridad-configuracion.md](docs/seguridad-configuracion.md) y cambia el driver a `twilio`. El trabajador debe ejecutarse por una tarea programada o cron; no se envía durante la transacción del pedido.

**Correo:** la recuperación escribe el enlace en `.local/mail.log` por defecto, protegido de HTTP e ignorado por Git. Para Gmail SMTP configura `BCNYX_MAIL_TRANSPORT=smtp`, `BCNYX_SMTP_HOST=smtp.gmail.com`, puerto `587`, usuario, contraseña de aplicación y remitente como variables de entorno. Prueba la entrega antes de publicar. También existe `BCNYX_MAIL_TRANSPORT=mail` si el servidor ya tiene correo saliente configurado.

La limitación de intentos guarda contadores por IP en `limites_solicitud`. Programa `php scripts/limpiar_limites.php` una vez al día para borrar contadores vencidos.

## Seguridad y despliegue

Apache debe permitir `.htaccess` (`AllowOverride All`) o se deben trasladar sus reglas al virtual host. Las carpetas `bd`, `docs`, `scripts`, `PAGINA/lib`, `PAGINA/vendor` y `.local` no deben ser accesibles por HTTP. Usa HTTPS, desactiva `display_errors`, configura cookies seguras y crea un usuario de BD con permisos solo sobre la base de BCNyx. Guarda copias de seguridad fuera del directorio público. Rota las credenciales antiguas de Twilio si alguna vez se usaron.

## Pruebas

Separa las pruebas de dominio en una base cuyo nombre empiece por `bcnyx_test_`:

```text
$env:BCNYX_DB_NAME='bcnyx_test_release_20261119'
php tests/integration_pedidos.php
Remove-Item Env:BCNYX_DB_NAME
php tests/smoke_http.php
php tests/smoke_auth.php
php tests/security_http.php
```

`smoke_http.php`, `smoke_auth.php` y `security_http.php` consultan el Apache local y por defecto usan la base configurada para él. Crean y limpian datos temporales. Puedes cambiar la URL local con `BCNYX_TEST_URL`. Las pruebas de dominio requieren que el producto 2 tenga al menos una unidad en talla S y que el producto 1 talla S esté agotado.
