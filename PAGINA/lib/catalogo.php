<?php
declare(strict_types=1);

function tarjetaProducto(array $producto): void
{
    $id = (int)$producto['id_producto'];
    $nombre = htmlspecialchars($producto['nombre_producto'], ENT_QUOTES, 'UTF-8');
    $imagen = htmlspecialchars($producto['imagen'], ENT_QUOTES, 'UTF-8');
    $precio = number_format((float)$producto['precio'], 0, ',', '.');
    $disponible = max(0, (int)($producto['disponible'] ?? 0));
    $codigo = preg_match('~/B([0-9]+)-frente\.png\z~', $producto['imagen'], $m) ? 'B' . $m[1] : null;
    $onclick = $codigo ? ' onclick="abrirModalProducto(\'' . $codigo . '\')"' : '';
    echo '<div class="ofert-1' . ($disponible === 0 ? ' locked' : '') . '">';
    echo '<img src="' . $imagen . '" class="Camisa" alt="' . $nombre . '"' . $onclick . '>';
    echo '<div class="product-txt"><h3>' . $nombre . '</h3><p>mejor calidad</p><p class="precio">$' . $precio . '</p>';
    if ($disponible > 0) {
        echo '<a href="#" class="agregar-carrito btn-3" data-id="' . $id . '">Agregar</a>';
    } else {
        echo '<span class="agregar-carrito btn-3" aria-disabled="true">Agotado</span>';
    }
    echo '</div></div>';
}
