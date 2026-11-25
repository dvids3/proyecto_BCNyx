# Configuración de credenciales

El servicio de WhatsApp lee `TWILIO_ACCOUNT_SID`, `TWILIO_AUTH_TOKEN` y `TWILIO_WHATSAPP_FROM` del entorno del proceso PHP/Apache. Configúralas fuera del directorio público y reinicia Apache.

Las credenciales que estuvieron en `PAGINA/whatsapp_service.php` deben revocarse en Twilio y reemplazarse por otras nuevas. Retirarlas del archivo actual no elimina las copias existentes en el historial de Git ni invalida la clave antigua.

Si faltan variables, la compra puede completarse y se omite la notificación de WhatsApp. El error se registra en el log del servidor sin exponer secretos ni datos de clientes en una ruta pública.
