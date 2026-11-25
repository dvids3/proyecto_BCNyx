<?php
declare(strict_types=1);

function validarDatosUsuario(array $entrada): array
{
    $limites = [
        'nombre' => 100, 'apellidos' => 100, 'correo' => 100,
        'telefono' => 20, 'tipo_documento' => 20, 'documento' => 30,
        'pais' => 80, 'departamento' => 80, 'ciudad' => 80,
    ];
    $datos = [];
    foreach ($limites as $campo => $limite) {
        $valor = $entrada[$campo] ?? null;
        if (!is_string($valor)) throw new InvalidArgumentException('Completa todos los datos');
        $valor = trim($valor);
        if ($valor === '' || mb_strlen($valor) > $limite) {
            throw new InvalidArgumentException('Revisa el campo ' . $campo);
        }
        $datos[$campo] = $valor;
    }
    if (!filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException('Correo inválido');
    }
    if (!preg_match('/\A[0-9+() .-]{7,20}\z/D', $datos['telefono'])) {
        throw new InvalidArgumentException('Teléfono inválido');
    }
    return $datos;
}
