<?php
declare(strict_types=1);

/** Adaptador a implementar para tarjeta/PSE al elegir proveedor. Nunca confirmar con datos del navegador. */
interface PasarelaPago
{
    /** Devuelve la URL segura del checkout creado en el proveedor para este pedido. */
    public function crearCheckout(array $pedido, array $items): string;

    /** Verifica firma, importe, moneda, referencia y estado del evento recibido. */
    public function verificarEvento(string $cuerpo, array $cabeceras): array;

    /** Solicita un reembolso; la devolución se registra tras confirmarlo. */
    public function reembolsar(array $pedido, string $claveIdempotencia): string;
}
