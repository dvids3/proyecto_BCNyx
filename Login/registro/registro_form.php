<?php require_once __DIR__ . '/../../PAGINA/lib/http.php'; iniciarSesion(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta charset="UTF-8">
  <meta name="theme-color" content="#f2f5ff">
  <title>Registro</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="style_registro.css?v=3">
</head>
<body class="pagina-registro">
  <main class="registro-card">
    <section class="registro-panel">
      <form action="registro.php" method="POST" data-ux-form class="form-registro">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars(tokenCsrf(), ENT_QUOTES, 'UTF-8') ?>">
        <header class="registro-brand"><img src="images_login/luna_login.svg" alt=""><span>BC NYX</span><a href="../../PAGINA/index.php">← Tienda</a></header>
        <div class="registro-heading"><p class="eyebrow">ÚNETE A LA COMUNIDAD</p><h1>Crear cuenta</h1><p>Regístrate para guardar tus datos y disfrutar la experiencia BC Nyx.</p></div>
        <div class="registro-grid">
          <label class="input-container"><span>Nombre</span><input type="text" name="nombre" autocomplete="given-name" maxlength="100" placeholder="Ej. Laura" required></label>
          <label class="input-container"><span>Apellidos</span><input type="text" name="apellidos" autocomplete="family-name" maxlength="100" placeholder="Ej. Gómez" required></label>
          <label class="input-container"><span>Correo electrónico</span><input type="email" name="correo" autocomplete="email" maxlength="100" placeholder="tu@correo.com" required></label>
          <label class="input-container"><span>Contraseña</span><input type="password" name="clave" placeholder="Mínimo 12 caracteres" minlength="12" autocomplete="new-password" required></label>
          <label class="input-container"><span>Teléfono</span><input type="tel" name="telefono" autocomplete="tel" inputmode="tel" maxlength="20" placeholder="300 123 4567" required></label>
          <label class="input-container"><span>Tipo de documento</span><select name="tipo_documento" required><option value="">Selecciona una opción</option><option value="Cédula">Cédula</option><option value="Tarjeta de identidad">Tarjeta de identidad</option><option value="Otro">Otro</option></select></label>
          <label class="input-container"><span>Número de documento</span><input type="text" name="documento" inputmode="numeric" maxlength="30" placeholder="Sin puntos ni espacios" required></label>
          <label class="input-container"><span>País</span><input type="text" name="pais" value="Colombia" autocomplete="country-name" readonly required></label>
          <label class="input-container"><span>Departamento</span><input type="text" name="departamento" autocomplete="address-level1" maxlength="80" placeholder="Escribe para buscar" required></label>
          <label class="input-container"><span>Ciudad</span><input type="text" name="ciudad" autocomplete="address-level2" maxlength="80" placeholder="Escribe para buscar" required></label>
        </div>
        <footer class="registro-footer"><p>¿Ya tienes cuenta? <a href="login_form.php">Inicia sesión</a></p><button class="btn-registro" type="submit">Crear mi cuenta <span aria-hidden="true">→</span></button></footer>
      </form>
    </section>
    <aside class="registro-preview" aria-label="Vista previa de la marca BC Nyx">
      <div class="preview-content"><span class="preview-kicker">BC NYX · ESTILO URBANO</span><div class="preview-art" aria-hidden="true"><img src="images_login/registro-universo.jpg" alt=""></div><h2>Una comunidad con identidad propia.</h2><p>Descubre prendas y diseños hechos para expresarte.</p><div class="preview-note"><span class="preview-dot"></span><span><strong>Tu espacio BC Nyx</strong><small>Tu cuenta, tus favoritos, tu estilo.</small></span></div></div>
      <small class="preview-foot">Experiencia de demostración</small>
    </aside>
  </main>

<script type="module" src="../../PAGINA/js/ux_formularios.js"></script>

</body>
</html>
