const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
const lista = document.querySelector("#lista-carrito tbody");
const vaciarCarritoBtn = document.getElementById("vaciar-carrito");
const newsletterForm = document.getElementById("newsletter-form");

newsletterForm?.addEventListener("submit", event => {
  event.preventDefault();
  const mensaje = document.getElementById("newsletter-mensaje");
  if (mensaje) {
    mensaje.textContent = "Suscripción de prueba completada. No se guardó el correo ni se enviaron mensajes.";
    mensaje.hidden = false;
  }
  newsletterForm.reset();
});

if (vaciarCarritoBtn) {
  vaciarCarritoBtn.addEventListener("click", vaciarCarrito);
}

const btnComprar = document.getElementById("btn-comprar");
if (btnComprar) {
  btnComprar.addEventListener("click", (e) => {
    e.preventDefault();
    const productos = document.querySelectorAll("#lista-carrito tbody tr");
    if (productos.length === 0) {
      mostrarPopup("Tu carrito está vacío");
      return;
    }
    window.location.href = "pago.php";
  });
}

function actualizarContador() {
  const contador = document.getElementById("contador-carrito");
  if (!contador) return;
  const totalItems = document.querySelectorAll("#lista-carrito tbody tr").length;

  if (totalItems > 0) {
    contador.textContent = totalItems;
    contador.style.display = "inline-block";
  } else {
    contador.style.display = "none";
  }
}

function actualizarContadoresGlobal() {
  actualizarContador();
  actualizarContadorSticky();
  actualizarContadorTarjeta();
}

function actualizarContadorTarjeta() {
  const listaCuerpo = document.querySelector("#lista-carrito tbody");
  const contadorTarjeta = document.getElementById("contador-tarjeta");

  if (!listaCuerpo || !contadorTarjeta) return;

  const totalItems = listaCuerpo.querySelectorAll("tr").length;

  if (totalItems > 0) {
    contadorTarjeta.textContent = totalItems;
    contadorTarjeta.style.display = "inline-block";
  } else {
    contadorTarjeta.style.display = "none";
  }
}

function eliminarElemento(e) {
  e.preventDefault();
  if (!e.target.classList.contains("borrar")) return;

  const fila = e.target.closest("tr");
  const productoId = e.target.getAttribute("data-id");

  fetch('eliminar_del_carrito.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-Token': csrfToken },
    body: `id_producto=${productoId}`
  })
  .then(res => res.text())
  .then(res => {
    if (res.trim() === "ok") {
      fila.remove();
      actualizarContadoresGlobal();
    } else {
      console.error("Error al eliminar:", res);
    }
  });
}


// Activar el clic en la "X" para borrar un producto
const tablaCarrito = document.querySelector("#lista-carrito");
if (tablaCarrito) {
  tablaCarrito.addEventListener("click", eliminarElemento);
}


function vaciarCarrito(e) {
  if (e) e.preventDefault();

  fetch('vaciar_carrito.php', { method: 'POST', headers: { 'X-CSRF-Token': csrfToken } })
  .then(res => res.text())
  .then(res => {
    if (res.trim() === "ok") {
      const listaCuerpo = document.querySelector("#lista-carrito tbody");
      if (listaCuerpo) listaCuerpo.innerHTML = "";
      localStorage.removeItem("carrito"); // Eliminamos cualquier residuo antiguo del navegador
      actualizarContadoresGlobal();
    } else {
      console.error("Error al vaciar desde BD:", res);
    }
  });
}


document.addEventListener('DOMContentLoaded', () => {
    const btnServicios = document.getElementById('btn-servicios');
    const tooltip = document.getElementById('tooltip-servicios');

    if (btnServicios && tooltip) {
        btnServicios.addEventListener('click', (e) => {
            e.preventDefault();
            tooltip.classList.toggle('oculto');
        });

        document.addEventListener('click', (e) => {
            if (!btnServicios.contains(e.target) && !tooltip.contains(e.target)) {
                tooltip.classList.add('oculto');
            }
        });
    }
});

const btnContacto = document.getElementById('btn-contacto');
const tooltipContacto = document.getElementById('tooltip-contacto');

if (btnContacto && tooltipContacto) {
    btnContacto.addEventListener('click', (e) => {
        e.preventDefault();
        tooltipContacto.classList.toggle('oculto');
    });

    document.addEventListener('click', (e) => {
        if (!btnContacto.contains(e.target) && !tooltipContacto.contains(e.target)) {
            tooltipContacto.classList.add('oculto');
        }
    });
}

function copiarTexto(id) {
    const elemento = document.getElementById(id);
    if (!elemento) return;
    const texto = elemento.textContent;
    navigator.clipboard.writeText(texto)
        .then(() => alert('Copiado al portapapeles: ' + texto))
        .catch(err => console.error('Error al copiar:', err));
}

const logoSticky = document.getElementById("logoSticky");

function actualizarLogoSticky() {
  const modalInfo = document.getElementById('info-modal');
  const modalInfoAbierto = modalInfo ? !modalInfo.classList.contains('oculto') : false;
  if (logoSticky) {
      if (window.scrollY > 100 && !modalInfoAbierto) {
        logoSticky.classList.add("visible");
      } else {
        logoSticky.classList.remove("visible");
      }
  }
}

window.addEventListener("scroll", actualizarLogoSticky);


// Apertura del modal del carrito desde el icono superior
document.addEventListener("DOMContentLoaded", () => {
  const carritoIcon = document.getElementById("img-carrito");
  const modal = document.getElementById("carrito-modal");
  const cerrarModal = document.getElementById("cerrar-modal");

  if (carritoIcon && modal) {
      carritoIcon.addEventListener("click", (e) => {
        e.preventDefault();
        const listaCuerpo = document.querySelector("#lista-carrito tbody");

        if (listaCuerpo) listaCuerpo.innerHTML = ""; // Limpiamos para evitar duplicados visuales

        fetch("mostrar_carrito.php")
          .then(res => res.text())
          .then(html => {
            if (listaCuerpo) listaCuerpo.innerHTML = html;
            modal.classList.remove("oculto");
            actualizarContadoresGlobal();
          });
      });
  }

  if (cerrarModal && modal) {
      cerrarModal.addEventListener("click", () => {
        modal.classList.add("oculto");
      });
  }

  window.addEventListener("click", (e) => {
    if (e.target === modal) {
      modal.classList.add("oculto");
    }
  });
});


// Modal de informacion y swipe para celular
document.addEventListener("DOMContentLoaded", () => {
  const botonesInfo = document.querySelectorAll('.abrir-info');
  const modalInfo = document.getElementById('info-modal');
  const cerrar = document.getElementById('cerrar-info-modal');
  const infoItems = document.querySelectorAll('.info-item');
  const btnPrev = document.getElementById("prev-info");
  const btnNext = document.getElementById("next-info");
  const logoSticky = document.getElementById("logoSticky");

  let indiceInfoActual = 0;

  function mostrarInfoActual() {
    infoItems.forEach((item, i) => {
      item.classList.toggle("activo", i === indiceInfoActual);
    });
  }

  botonesInfo.forEach(boton => {
    boton.addEventListener('click', e => {
      e.preventDefault();
      if (modalInfo) modalInfo.classList.remove('oculto');
      document.body.classList.add('modal-abierto');
      if (logoSticky) logoSticky.classList.remove("visible");
      indiceInfoActual = 0;
      mostrarInfoActual();
    });
  });

  if (cerrar) {
      cerrar.addEventListener('click', () => {
        if (modalInfo) modalInfo.classList.add('oculto');
        document.body.classList.remove('modal-abierto');
        if (typeof actualizarLogoSticky === "function") actualizarLogoSticky();
      });
  }

  if (btnNext) {
    btnNext.addEventListener("click", () => {
      indiceInfoActual = (indiceInfoActual + 1) % infoItems.length;
      mostrarInfoActual();
    });
  }

  if (btnPrev) {
    btnPrev.addEventListener("click", () => {
      indiceInfoActual = (indiceInfoActual - 1 + infoItems.length) % infoItems.length;
      mostrarInfoActual();
    });
  }

  let touchStartX = 0;
  let touchEndX = 0;

  if (modalInfo) {
    modalInfo.addEventListener("touchstart", (e) => {
      touchStartX = e.changedTouches[0].screenX;
    });

    modalInfo.addEventListener("touchend", (e) => {
      touchEndX = e.changedTouches[0].screenX;
      manejarSwipe();
    });
  }

  function manejarSwipe() {
    const diferencia = touchStartX - touchEndX;
    if (Math.abs(diferencia) > 50) {
      if (diferencia > 0) {
        btnNext?.click();
      } else {
        btnPrev?.click();
      }
    }
  }
});


// Modales Varios
const configurarModal = (btnId, modalId, cerrarClass) => {
    const btn = document.getElementById(btnId);
    const modal = document.getElementById(modalId);
    const cerrarBtn = document.querySelector(cerrarClass);

    if (btn && modal && cerrarBtn) {
        btn.addEventListener("click", (e) => {
            e.preventDefault();
            modal.style.display = "block";
        });
        cerrarBtn.addEventListener("click", () => {
            modal.style.display = "none";
        });
        window.addEventListener("click", (e) => {
            if (e.target === modal) modal.style.display = "none";
        });
    }
};

configurarModal("mostrar-pregunta", "modal-pregunta-box", ".cerrar-pregunta");
configurarModal("mostrar-sugerencias", "modal-sugerencias-box", ".cerrar-sugerencias");
configurarModal("mostrar-somos", "modal-somos-box", ".cerrar-somos");
configurarModal("abrir-privacidad", "modal-privacidad", ".cerrar-privacidad");


// Carrito Sticky
document.addEventListener("DOMContentLoaded", () => {
  const carritoSticky = document.getElementById("img-carrito-sticky");
  if (carritoSticky) {
    carritoSticky.addEventListener("click", (e) => {
      e.preventDefault();
      const modal = document.getElementById("carrito-modal");
      if (modal) modal.classList.remove("oculto");
    });
  }

  const observer = new MutationObserver(actualizarContadorSticky);
  const listaCuerpo = document.querySelector("#lista-carrito tbody");
  if (listaCuerpo) {
    observer.observe(listaCuerpo, { childList: true });
  }
  actualizarContadorSticky();
});

function actualizarContadorSticky() {
  const listaCuerpo = document.querySelector("#lista-carrito tbody");
  const contadorSticky = document.getElementById("contador-carrito-sticky");

  if (!listaCuerpo || !contadorSticky) return;

  const totalItems = listaCuerpo.querySelectorAll("tr").length;
  if (totalItems > 0) {
    contadorSticky.textContent = totalItems;
    contadorSticky.style.display = "inline-block";
  } else {
    contadorSticky.style.display = "none";
  }
}

function mostrarPopup(texto, tiempo = 2000) {
  const popup = document.getElementById("popup-mensaje");
  const textoPopup = document.getElementById("popup-texto");
  if (!popup || !textoPopup) return;

  textoPopup.textContent = texto;
  popup.classList.remove("popup-oculto");
  popup.classList.add("popup-mostrar");

  setTimeout(() => {
    popup.classList.remove("popup-mostrar");
    popup.classList.add("popup-oculto");
  }, tiempo);
}


// Saludo en la nube
document.addEventListener("DOMContentLoaded", () => {
  const nube = document.getElementById("saludo-flotante");
  if (!nube) return;

  let isDragging = false;
  let offsetX, offsetY;

  nube.addEventListener("mousedown", (e) => {
    isDragging = true;
    offsetX = e.clientX - nube.getBoundingClientRect().left;
    offsetY = e.clientY - nube.getBoundingClientRect().top;
    nube.style.transition = "none";
  });

  document.addEventListener("mousemove", (e) => {
    if (isDragging) {
      nube.style.left = `${e.clientX - offsetX}px`;
      nube.style.top = `${e.clientY - offsetY}px`;
      nube.style.transform = "none";
    }
  });

  document.addEventListener("mouseup", () => isDragging = false);

  nube.addEventListener("touchstart", (e) => {
    isDragging = true;
    const touch = e.touches[0];
    offsetX = touch.clientX - nube.getBoundingClientRect().left;
    offsetY = touch.clientY - nube.getBoundingClientRect().top;
    nube.style.transition = "none";
  });

  document.addEventListener("touchmove", (e) => {
    if (isDragging) {
      const touch = e.touches[0];
      nube.style.left = `${touch.clientX - offsetX}px`;
      nube.style.top = `${touch.clientY - offsetY}px`;
      nube.style.transform = "none";
    }
  });

  document.addEventListener("touchend", () => isDragging = false);
});


// Ventanas de sesión
function cerrarModalSesion() {
  const modalSesion = document.getElementById("modal-sesion");
  if (modalSesion) modalSesion.style.display = "none";
}

function irARegistro() {
  window.location.href = "../Login/registro/registro_form.php";
}


// Agregar al carrito (Global)
document.addEventListener('click', function(e) {
  if (e.target.classList.contains('agregar-carrito')) {
    e.preventDefault();
    const boton = e.target;

    const id_producto = boton.getAttribute('data-id');

    fetch('agregar_al_carrito.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-Token': csrfToken },
      body: `id_producto=${encodeURIComponent(id_producto)}`
    })
    .then(res => res.text())
    .then(res => {
      if (res && res.includes("Producto agregado")) {
        mostrarToast("Producto añadido al carrito");

        fetch("mostrar_carrito.php")
          .then(r => r.text())
          .then(html => {
            const listaCuerpo = document.querySelector("#lista-carrito tbody");
            if (listaCuerpo) listaCuerpo.innerHTML = html;
            actualizarContadoresGlobal();
          });
      }
    });
  }
});


// Botón de comprar carrito duplicado (por si tienes un id comprar-carrito distinto)
const btnComprarCarrito = document.getElementById('comprar-carrito');
if (btnComprarCarrito) {
    btnComprarCarrito.addEventListener('click', function(e) {
      e.preventDefault();
      window.location.href = 'pago.php';
    });
}


function mostrarToast(mensaje, duracion = 2500) {
  const toast = document.getElementById('toast');
  if (!toast) return;
  toast.textContent = mensaje;
  toast.style.display = 'block';
  toast.style.opacity = 1;

  setTimeout(() => {
    toast.style.opacity = 0;
    setTimeout(() => {
      toast.style.display = 'none';
    }, 300);
  }, duracion);
}


// ===== Carrusel automático =====
let indice = 0;
function moverCarrusel() {
  const carrusel = document.querySelector(".carrusel-imagenes");
  if (!carrusel) return;
  const total = carrusel.children.length;
  indice = (indice + 1) % total;
  carrusel.style.transform = `translateX(-${indice * 100}%)`;
}
setInterval(moverCarrusel, 4000);


// Carga automática del carrito al entrar o refrescar la página
document.addEventListener("DOMContentLoaded", () => {
  const listaCuerpo = document.querySelector("#lista-carrito tbody");
  if (!listaCuerpo) return;

  fetch("mostrar_carrito.php")
    .then(res => res.text())
    .then(html => {
      listaCuerpo.innerHTML = html;
      actualizarContadoresGlobal();
    })
    .catch(err => console.error("Error cargando carrito:", err));
});
