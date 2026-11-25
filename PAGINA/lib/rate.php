<?php
declare(strict_types=1);

function permitirSolicitud(mysqli $db, string $accion, int $limite, int $ventanaSegundos): bool
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'local';
    $clave = hash('sha256', $accion . ':' . $ip);
    $db->begin_transaction();
    try {
        $stmt = $db->prepare('SELECT contador, TIMESTAMPDIFF(SECOND, inicio, NOW()) >= ? AS vencido FROM limites_solicitud WHERE clave = ? FOR UPDATE');
        $stmt->bind_param('is', $ventanaSegundos, $clave);
        $stmt->execute();
        $registro = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$registro || (int)$registro['vencido'] === 1) {
            $stmt = $db->prepare('INSERT INTO limites_solicitud (clave, inicio, contador) VALUES (?, NOW(), 1) ON DUPLICATE KEY UPDATE inicio = NOW(), contador = 1');
            $stmt->bind_param('s', $clave);
            $stmt->execute();
            $stmt->close();
            $db->commit();
            return true;
        }
        if ((int)$registro['contador'] >= $limite) {
            $db->commit();
            return false;
        }
        $stmt = $db->prepare('UPDATE limites_solicitud SET contador = contador + 1 WHERE clave = ?');
        $stmt->bind_param('s', $clave);
        $stmt->execute();
        $stmt->close();
        $db->commit();
        return true;
    } catch (Throwable $e) {
        $db->rollback();
        throw $e;
    }
}
