<?php
require_once __DIR__ . '/lib/http.php';
iniciarSesion();
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/lib/catalogo.php';
$catalogo = $conn->query('SELECT p.id_producto, p.nombre_producto, p.precio, p.imagen, COALESCE((SELECT SUM(i.stock - i.reservado) FROM inventario_tallas i WHERE i.id_producto = p.id_producto), 0) AS disponible FROM productos p ORDER BY p.id_producto')->fetch_all(MYSQLI_ASSOC);
?>

<html lang="es">
<head>
    <meta name="csrf-token" content="<?= htmlspecialchars(tokenCsrf(), ENT_QUOTES, 'UTF-8') ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BCNyx | Tienda</title>
    <link rel="stylesheet" href="../PAGINA/estilo/style_de_index.css">
</head>

<body id="top">
    <div id="logoSticky" class="logo-sticky">
        <div class="sticky-left">
            <a href="#top" class="inicio-btn">INICIO</a>
        </div>
        <div class="sticky-center">
            <div class="logo-text">BC NYX</div>
        </div>
        <div class="sticky-right">
            <div class="carrito-wrapper">
                <img src="images/carr.svg" id="img-carrito-sticky" alt="carrito">
                <span id="contador-carrito-sticky" class="contador">0</span>
            </div>
        </div>
    </div>

    <div id="info-modal" class="info-modal oculto">
        <div class="info-modal-content">
            <span id="cerrar-info-modal" class="cerrar-info">X</span>

            <div class="info-item">
            <img src="images/Inicio.png" alt="imagen 3">
            </div>


            <div class="info-item activo">
            <img src="images/Periódico_b.png" alt="imagen 1">           
            </div>

            <div class="info-item">
            <img src="images/Periódico_x.png" alt="imagen 2">
            </div>

            <!-- ✅ Nuevo contenedor para flechas -->
            <div class="botones-navegacion">
            <button id="prev-info" class="nav-info">&lt;</button>
            <button id="next-info" class="nav-info">&gt;</button>
            </div>
        </div>
    </div>

 
      
    
    <header class="header">                  
        <div class="menu container">    
            <a href="#" class="logo">BC Nyx</a>
            <input type="checkbox" id="menu" aria-label="Abrir menu de navegacion">
            <label for="menu">
                <img src="images/menu.png" alt="menu">          
            </label>
            <nav class="navbar">
                <ul>
                    <li class="submenu">
                        <a href="#" id="btn-servicios">Servicios</a>
                        <div id="tooltip-servicios" class="tooltip oculto">
                            <p>Ofrecemos envíos rápidos, personalización de productos y atención al cliente 24/7.</p>
                            <a href="#" class="abrir-info">Ver más</a>
                        </div>
                    </li>                     
                    <li class="submenu">
                        <a href="#" id="btn-contacto">Contacto</a>
                            <div id="tooltip-contacto" class="tooltip oculto">
                                <p><strong>Teléfono:</strong> <span id="telefono">324 503 1920</span>
                                    <button onclick="copiarTexto('telefono')">📋</button>
                                </p>
                                <p><strong>Correo:</strong> <span id="correo">contacto@ejemplo.com</span>
                                    <button onclick="copiarTexto('correo')">📋</button>
                                </p>
                            </div>
                        <?php if (isset($_SESSION['id_usuario'])): ?>
                            <li><a href="#" onclick="confirmarCerrarSesion()" class="cerrar-sesion-link">Cerrar sesión</a></li>
                        <?php else: ?>
                            <li><a href="../Login/registro/registro_form.php">Registrarse</a></li>
                        <?php endif; ?>
                    </li>                     
                </ul>
            </nav>
                <?php if (isset($_SESSION['usuario'])): ?>
                    <div class="nube-con-saludo" id="saludo-flotante">
                        <img src="images/nube.png" alt="nube" class="nube-img">
                        <span class="saludo-en-nube">Hola, <?= htmlspecialchars($_SESSION['usuario']) ?> Que se dice</span>
                    </div>
                <?php endif; ?>
                <div id="modal-cerrar-sesion" class="modal-overlay">
                    <div class="modal-contenido">
                        <p>¿Seguro que quieres cerrar sesión?</p>
                        <div class="botones">
                        <button onclick="cerrarSesion()" class="btn-si">Sí</button>
                        <button onclick="cerrarModal()" class="btn-no">Cancelar</button>
                        </div>
                    </div>
                </div>
            <div>
                <ul>
                    <li class="submenu">
                        <div class="carrito-wrapper">
                            <a href="pago.php">
                                <img src="images/tarjeta.svg" alt="pago">
                                <span id="contador-tarjeta" class="contador" style="display: none;">0</span>
                            </a>

                                <?php if (isset($_SESSION['id_usuario'])): ?>
                                    <!-- 🔐 Usuario logueado: va a su perfil -->
                                    <a href="actualizar_perfil.php">
                                        <img src="images/perfil.svg" alt="Perfil">
                                    </a>
                                <?php else: ?>
                                    <!-- 👤 Usuario anónimo: va al registro -->
                                    <a href="../Login/registro/registro_form.php">
                                        <img src="images/perfil.svg" alt="Perfil">
                                    </a>
                                <?php endif; ?>

                            <img src="images/carr.svg" id="img-carrito" alt="carrito">
                            <span id="contador-carrito" class="contador">0</span>
                                                 
                        </div>
                        <div id="carrito-modal" class="modal oculto">
                            <div class="modal-content">
                                <span class="cerrar-modal" id="cerrar-modal">x</span>
                                <h2>Carrito de Compras</h2>
                                <table id="lista-carrito">
                                    <thead>
                                        <tr>
                                            <th>Imagen</th>
                                            <th>Nombre</th>
                                            <th>Precio</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                                <a href="#" id="vaciar-carrito" class="btn-2">Vaciar Carrito</a>
                                <a href="#" id="comprar-carrito" class="btn-2">Comprar</a>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
        <div class="header-content container">
            <div class="header-txt">
                <p class="subtitulo">¡Novedades de temporada!</p>
                <h1>Ofertas Especiales</h1>
                <p>
                Estrena los mejores buzos de la temporada, con un 20% de descuento en tu primera compra.
                </p>
                <a href="#lista-1" class="btn-1">Ver Productos</a>
            </div>
            <div class="header-img">
                <img src="Buzos/buzo.png_image.png" alt="buzo" class="main-img">
                <img src="images/cinta.com.png" alt="Cinta de peligro" class="danger-tape">
              </div>              
        </div>    
    </header>


    <!-- Carrusel -->
    <div class="carrusel">
    <div class="carrusel-imagenes">
        <img src="../PAGINA/images/carrucel.jpg" alt="Imagen 1">
        <img src="../PAGINA/images/carrucel2.jpg" alt="Imagen 2">
        <img src="images/img3.jpg" alt="Imagen 3">
    </div>
    </div>

    <section class="oferts container" id="lista-1">
    <h2>Nuestros productos principales</h2>
    <p>Our main products</p>
    <div class="oferts-content">
        <?php foreach (array_slice($catalogo, 0, 4) as $producto) tarjetaProducto($producto); ?>
    </div>
</section>
<section class="promotion">
        <div class="promotion-content container">
            <div class="promotion-txt">
                <h2>Las Mejores Promociones</h2>
                <p>
                    productos
                </p>
                <a href="#" class="btn-1 abrir-info">informacion</a>
            </div>
            <div class="promotion-img">
                    <img src="images/404.jpg" alt="Imagen">
            </div>
        </div>
    </section>


    <main class="product container" id="lista-2">
    <h2>Productos</h2>
    <div class="product-content">
        <?php foreach (array_slice($catalogo, 4) as $producto) tarjetaProducto($producto); ?>
    </div>
</main>


    <section class="bloog container">
        <h2>Noticias</h2>
        <div class="bloog-content">

            <div class="bloog-1">
                <video src="videos/mini_video1.mp4" autoplay muted loop playsinline></video>
                <div class="bloog-txt">
                    <p>Preguntas Frecuentes</p>
                    <a href="#" class="btn-4" id="mostrar-pregunta">Preguntar</a>
                </div>
            </div>

            <div id="modal-pregunta-box" class="modal-pregunta">
                <div class="modal-pregunta-content">
                    <span class="cerrar-pregunta">X</span>
                    <form action="guardar_pregunta.php" method="POST">
                        <input type="hidden" name="csrf" value="<?= htmlspecialchars(tokenCsrf(), ENT_QUOTES, 'UTF-8') ?>">
                        <textarea name="pregunta" rows="4" placeholder="¿cuál es tu pregunta?" required></textarea>
                        <button class="btn-4" type="submit" style="margin-top: 10px;">Enviar</button>
                    </form>
                </div>
            </div>


            <div class="bloog-1">
            <video src="videos/mini_video2.mp4" autoplay muted loop playsinline></video>
                <div class="bloog-txt">
                    <p>¿Algúna idea?</p>
                    <a href="#" class="btn-4" id="mostrar-sugerencias">Dinoslo</a>
                </div>
            </div>

            <div id="modal-sugerencias-box" class="modal-sugerencias">
                <div class="modal-sugerencias-content">
                    <span class="cerrar-sugerencias">X</span>
                    <form action="guardar_idea.php" method="POST">
                        <input type="hidden" name="csrf" value="<?= htmlspecialchars(tokenCsrf(), ENT_QUOTES, 'UTF-8') ?>">
                        <textarea name="idea" rows="4" placeholder="¿Cuál es tu idea?" required></textarea>
                        <button class="btn-4" type="submit" style="margin-top: 10px;">Enviar</button>
                    </form>
                </div>
            </div>


    
            <div class="bloog-1">
            <video src="videos/mini_video3.mp4" autoplay muted loop playsinline></video>
                <div class="bloog-txt">
                    <p>¿Quiénes somos?</p>
                    <a href="#" class="btn-4" id="mostrar-somos">Somos...</a>
                </div>  
            </div>

            <div id="modal-somos-box" class="modal-somos">
                <div class="modal-somos-content">
                    <span class="cerrar-somos">X</span>
                    <h3>¿Quiénes somos?</h3>
                    <p>
                    Somos BC Nyx, una marca nacida con pasión por el diseño y la identidad urbana.
                    </p>
                    <p>
                    Nuestra misión es ofrecer prendas únicas, con estilo propio y materiales de alta calidad.
                    Inspirados por la cultura, el arte y la autenticidad. Gracias por formar parte de nuestra comunidad.
                    </p>
                </div>
            </div>
        </div>

    </section>

    <footer class="footer-bottom">
        <div class="footer-section">

            <div class="footer-box">
                <h4>REGÍSTRESE PARA RECIBIR LAS NOVEDADES DE BC Nyx</h4>
                <p>
                    Al introducir su dirección de correo electrónico aquí abajo, acepta recibir nuestra newsletter con las últimas novedades sobre nuestras colecciones, eventos e iniciativas. Puede encontrar más información en nuestra 
                    <a href="#" id="abrir-privacidad">Política de privacidad</a>
                </p>
                <form id="newsletter-form" class="newsletter-form">
                    <label class="sr-only" for="newsletter-correo">Correo electrónico</label>
                    <input id="newsletter-correo" type="email" placeholder="Correo electrónico" autocomplete="email" required>
                    <button class="btn-3" type="submit">Probar suscripción</button>
                    <p id="newsletter-mensaje" role="status" aria-live="polite" hidden></p>
                </form>
            </div>
        </div>

        <div class="footer-copy">
            <p>© 2024 - 2025 BC Nyx. Todos los derechos reservados. BC Nyx Studio. NIT 123456789-0. Registro de marca en proceso. <br>LICENCIA SIAE N. 2294/I/1936 y 5647/I/1936</p>
        </div>


        <div class="logo-footer">
            <h1>BC Nyx</h1>
        </div>
    </footer>

    <div id="modal-privacidad" class="modal-privacidad">
        <div class="modal-privacidad-content">
            <span class="cerrar-privacidad">X</span>
            <h3>Política de Privacidad</h3>
            <p>
            Esta es una política de privacidad ficticia para tu marca. Aquí puedes explicar cómo recopilas, usas y proteges los datos de tus usuarios. Por ejemplo:
            </p>
            <ul style="text-align: left;">
                <li>No compartimos tu información personal con terceros.</li>
                <li>Los correos electrónicos se usan solo para enviarte noticias o promociones si aceptas.</li>
                <li>Puedes solicitar la eliminación de tus datos en cualquier momento.</li>
            </ul>
            <p style="margin-top: 10px;">Para más información, contáctanos.</p>
        </div>
    </div>

    <div id="toast"></div>

    <!-- Modal de Producto -->
    <div id="modal-producto" class="modal-producto oculto">
    <div class="contenido-modal">
        <span class="cerrar-modal-zoom" onclick="cerrarModalProducto()">X</span>
        <img id="imagen-modal" src="" alt="Producto ampliado">
        
        <div class="botones-modal">
        <button onclick="cambiarImagen()">Ver Reverso</button>
        <button onclick="zoomImagen()">Zoom</button>
        </div>
    </div>
    </div>

<script type="module" src="../PAGINA/js/script.js?v=2"></script>




<script>
const USUARIO_LOGUEADO = <?= isset($_SESSION['id_usuario']) ? 'true' : 'false' ?>;
</script>


<script>
//zoom a las imagenes de los productos
  let productoActual = "";
  let imagenActual = "frente";
  let zoomActivo = false;

  function abrirModalProducto(nombreProducto) {
    productoActual = nombreProducto;
    imagenActual = "frente";
    zoomActivo = false;

    const modal = document.getElementById("modal-producto");
    const imagen = document.getElementById("imagen-modal");

    imagen.src = `Buzos/${productoActual}-frente.png`;
    imagen.style.transform = "scale(1)";
    imagen.style.cursor = "zoom-in";
    modal.classList.remove("oculto");
  }

  function cerrarModalProducto() {
    const modal = document.getElementById("modal-producto");
    const imagen = document.getElementById("imagen-modal");
    zoomActivo = false;
    imagen.style.transform = "scale(1)";
    imagen.style.cursor = "zoom-in";
    modal.classList.add("oculto");
  }

  function cambiarImagen() {
    const imagen = document.getElementById("imagen-modal");

    if (imagenActual === "frente") {
      imagen.src = `Buzos/${productoActual}-reves.png`;
      imagenActual = "reves";
    } else {
      imagen.src = `Buzos/${productoActual}-frente.png`;
      imagenActual = "frente";
    }

    zoomActivo = false;
    imagen.style.transform = "scale(1)";
    imagen.style.cursor = "zoom-in";
  }

  function zoomImagen() {
    const imagen = document.getElementById("imagen-modal");

    if (!zoomActivo) {
      imagen.style.transform = "scale(2)";
      imagen.style.cursor = "zoom-out";
      zoomActivo = true;
    } else {
      imagen.style.transform = "scale(1)";
      imagen.style.cursor = "zoom-in";
      zoomActivo = false;
    }
  }

  // Permitir zoom también al hacer clic directamente en la imagen
  document.addEventListener("DOMContentLoaded", () => {
    const imagen = document.getElementById("imagen-modal");
    if (imagen) {
      imagen.addEventListener("click", zoomImagen);
    }
  });
</script>


<script>
//cierra la sesion
function confirmarCerrarSesion() {
  document.getElementById("modal-cerrar-sesion").style.display = "flex";
}

function cerrarModal() {
  document.getElementById("modal-cerrar-sesion").style.display = "none";
}

function cerrarSesion() {
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = '../Login/registro/logout.php';
  const csrf = document.createElement('input');
  csrf.type = 'hidden';
  csrf.name = 'csrf';
  csrf.value = document.querySelector('meta[name="csrf-token"]')?.content || '';
  form.appendChild(csrf);
  document.body.appendChild(form);
  form.submit();
}
</script>



</body>
</html>
