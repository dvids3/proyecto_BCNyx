# Recuperación de contraseña

La instalación nueva crea la tabla `recuperaciones_clave` como parte de `bd/instalar_limpio.sql`. El archivo de instalación debe importarse en una base vacía. Si la base existente no tiene esa tabla, se requiere migrar el esquema antes de usar la recuperación de contraseña; no importes el instalador sobre una base con datos.

En local, el enlace se escribe en `.local/mail.log` sin enviar correos externos. Para usar Gmail SMTP, configura las variables `BCNYX_MAIL_TRANSPORT=smtp`, `BCNYX_SMTP_HOST`, `BCNYX_SMTP_PORT`, `BCNYX_SMTP_USER`, `BCNYX_SMTP_PASSWORD` y `BCNYX_MAIL_FROM` en el entorno de Apache. Define `BCNYX_RECOVERY_URL` con la URL pública completa de `recuperar.php` cuando la aplicación no se sirva en `http://localhost/BCNyx/`. Usa HTTPS en producción.

El enlace dura 30 minutos, solo puede usarse una vez y una solicitud nueva invalida el enlace anterior. El correo no se expone en la respuesta, exista o no una cuenta. Hay un límite de solicitudes por IP.
