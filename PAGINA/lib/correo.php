<?php
declare(strict_types=1);

function enviarCorreo(string $destinatario, string $asunto, string $cuerpo): bool
{
    $transporte = getenv('BCNYX_MAIL_TRANSPORT') ?: 'log';
    if ($transporte === 'log') {
        $ruta = getenv('BCNYX_MAIL_LOG') ?: dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . '.local' . DIRECTORY_SEPARATOR . 'mail.log';
        $directorio = dirname($ruta);
        if (!is_dir($directorio) && !mkdir($directorio, 0700, true) && !is_dir($directorio)) return false;
        return file_put_contents($ruta, "\n--- " . date('c') . " ---\nPara: " . $destinatario . "\nAsunto: " . $asunto . "\n" . $cuerpo . "\n", FILE_APPEND | LOCK_EX) !== false;
    }
    if ($transporte === 'mail') {
        return mail($destinatario, $asunto, $cuerpo, "Content-Type: text/plain; charset=UTF-8\r\n");
    }
    if ($transporte === 'smtp') {
        require_once __DIR__ . '/../vendor/autoload.php';
        $host = getenv('BCNYX_SMTP_HOST');
        $usuario = getenv('BCNYX_SMTP_USER');
        $clave = getenv('BCNYX_SMTP_PASSWORD');
        $remitente = getenv('BCNYX_MAIL_FROM') ?: $usuario;
        if (!$host || !$usuario || !$clave || !$remitente) {
            throw new RuntimeException('SMTP incompleto');
        }
        $correo = new PHPMailer\PHPMailer\PHPMailer(true);
        $correo->isSMTP();
        $correo->Host = $host;
        $correo->Port = (int)(getenv('BCNYX_SMTP_PORT') ?: 587);
        $correo->SMTPAuth = true;
        $correo->Username = $usuario;
        $correo->Password = $clave;
        $correo->SMTPSecure = $correo->Port === 465 ? PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS : PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $correo->CharSet = 'UTF-8';
        $correo->setFrom($remitente, 'BCNyx');
        $correo->addAddress($destinatario);
        $correo->Subject = $asunto;
        $correo->Body = $cuerpo;
        return $correo->send();
    }
    throw new RuntimeException('Transporte de correo no configurado');
}
