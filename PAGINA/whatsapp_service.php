<?php
require __DIR__ . '/vendor/autoload.php'; // ✅ ruta corregida (sube un nivel)
use Twilio\Rest\Client;

/**
 * Enviar mensaje de WhatsApp con Twilio
 *
 * @param string $numeroDestino Número del cliente en formato internacional (+57…)
 * @param string $mensaje Texto del mensaje
 * @return array Resultado ['ok'=>bool, 'sid'=>string] o ['ok'=>false, 'error'=>string]
 */
function enviarWhatsapp($numeroDestino, $mensaje) {
    // ⚠️ REEMPLAZAR por variables de entorno (mejor práctica)
    $sid = getenv("TWILIO_ACCOUNT_SID") ?: "";
    $token = getenv("TWILIO_AUTH_TOKEN") ?: "";
    $from = getenv('TWILIO_WHATSAPP_FROM');
    if (!$sid || !$token || !$from) {
        return ['ok' => false, 'error' => 'Servicio de WhatsApp no configurado'];
    }

    try {
        $twilio = new Client($sid, $token);

        $message = $twilio->messages->create(
            "whatsapp:" . $numeroDestino, // ☑️ Cliente
            [
                "from" => $from,
                "body" => $mensaje
            ]
        );

        return ["ok" => true, "sid" => $message->sid];

    } catch (\Throwable $e) {
        // ⛔ Maneja cualquier error (incluye Exception y Error)
        return ["ok" => false, "error" => $e->getMessage()];
    }
}
