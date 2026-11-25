<?php require_once __DIR__ . '/../../PAGINA/lib/http.php'; iniciarSesion(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="style_login.css?v=4">
</head>
<body class="pagina-login">
<main class="login-card">
  <section class="login-panel">
    <form id="form-login" class="form-login" action="login.php" method="POST" data-ux-form>
      <input type="hidden" name="csrf" value="<?= htmlspecialchars(tokenCsrf(), ENT_QUOTES, 'UTF-8') ?>">
      <header class="login-brand"><img src="images_login/luna_login.svg" alt=""><span>BC NYX</span><a href="../../PAGINA/index.php">← Tienda</a></header>
      <div class="login-heading"><p class="eyebrow">QUÉ BUENO VERTE DE NUEVO</p><h1>Iniciar sesión</h1><p>Entra a tu espacio y sigue explorando BC Nyx.</p></div>
      <label class="login-field"><span>Correo electrónico</span><span class="control-icon"><input type="email" name="correo" id="correo" autocomplete="email" placeholder="tu@correo.com" required><i class="fa-solid fa-envelope" aria-hidden="true"></i></span></label>
      <label class="login-field"><span>Contraseña</span><span class="control-icon"><input type="password" name="clave" id="clave" autocomplete="current-password" placeholder="Tu contraseña" required><i class="fa-solid fa-lock" aria-hidden="true"></i></span></label>
      <p class="login-error" id="login-error" role="alert" hidden></p>
      <a class="login-recovery" href="recuperar.php">¿Olvidaste tu contraseña?</a>
      <button class="btn-login" type="submit">Entrar a mi cuenta <span aria-hidden="true">→</span></button>
      <p class="login-register">¿Aún no tienes cuenta? <a href="registro_form.php">Regístrate</a></p>
    </form>
  </section>
  <aside class="login-preview" aria-label="BC Nyx">
    <div class="login-preview-content"><span class="preview-kicker">BC NYX · ESTILO URBANO</span><div class="preview-art"><img src="images_login/login-ton618.jpg" alt="Representación artística de un agujero negro en el universo"></div><h2>Vuelve a tu propio universo.</h2><p>Diseños, prendas y referencias para expresar quién eres.</p><div class="preview-note"><span class="preview-dot" aria-hidden="true"></span><span><strong>Tu espacio BC Nyx</strong><small>Tu cuenta, tus favoritos, tu estilo.</small></span></div></div>
    <small class="preview-foot">Experiencia de demostración</small>
  </aside>
</main>

<script src="login.js"></script>


</body>
</html>
