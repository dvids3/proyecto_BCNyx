const elemetos1 = document.getElementById("lista-1");
const elemetos2 = document.getElementById("lista-2");
const lista = document.querySelector("#lista-carrito tbody");
const vaciarCarritoBtn = document.getElementById("vaciar-carrito");

cargareventListeners();

function cargareventListeners() {
  elemetos1.addEventListener("click", comprarElemento);
  elemetos2.addEventListener("click", comprarElemento);
  document.getElementById("carrito-modal").addEventListener("click", eliminarElemento);
  vaciarCarritoBtn.addEventListener("click", vaciarCarrito);
}

function comprarElemento(e) {
  e.preventDefault();

  if (e.target.classList.contains("agregar-carrito")) {
    const elemento = e.target.parentElement.parentElement;
    leerDatosElemento(elemento);
  }
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



function leerDatosElemento(elemento) {
    const infoElemento = {
        imagen: elemento.querySelector("img").src,
        titulo: elemento.querySelector("h3").textContent,
        precio: elemento.querySelector(".precio").textContent,
        id: elemento.querySelector("a").getAttribute("data-id"),
    };
    insertarCarrito(infoElemento);
    }

function insertarCarrito(elemento) {
  // Agrega visualmente al DOM
  const row = document.createElement("tr");
  row.innerHTML = `
    <td><img src="${elemento.imagen}" width=100></td>
    <td>${elemento.titulo}</td>
    <td>${elemento.precio}</td>
    <td><a href="#" class="borrar" data-id="${elemento.id}">X</a></td>
  `;
  lista.appendChild(row);

  // Si el usuario NO está logueado, guarda en localStorage
  if (!USUARIO_LOGUEADO) {
    let carrito = JSON.parse(localStorage.getItem("carrito")) || [];

    // Evita duplicados por ID
    const yaExiste = carrito.some(item => item.id === elemento.id);
    if (!yaExiste) {
      carrito.push(elemento);
      localStorage.setItem("carrito", JSON.stringify(carrito));
    }
  }

  actualizarContador();
  actualizarContadorSticky();
}


function actualizarContador() {
    const contador = document.getElementById("contador-carrito");
    const totalItems = lista.querySelectorAll("tr").length;
  
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
}




function eliminarElemento(e) {
  e.preventDefault();

  if (!e.target.classList.contains("borrar")) return;

  const fila = e.target.closest("tr");
  const productoId = e.target.getAttribute("data-id");

  if (typeof USUARIO_LOGUEADO !== 'undefined' && USUARIO_LOGUEADO) {
    // 🔐 Usuario logueado: eliminar de la base de datos
    fetch('eliminar_del_carrito.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `id_producto=${productoId}`
    })
    .then(res => res.text())
    .then(res => {
      if (res.trim() === "ok") {
        fila.remove(); // ✅ Solo si la base lo confirma
        actualizarContador();
        actualizarContadorSticky();
      } else {
        console.error("Error al eliminar:", res);
      }
    });
  } else {
    // 👤 Usuario no logueado: eliminar desde localStorage
    let carrito = JSON.parse(localStorage.getItem("carrito")) || [];
    carrito = carrito.filter(item => item.id !== productoId);
    localStorage.setItem("carrito", JSON.stringify(carrito));
    fila.remove(); // Eliminar del DOM
    actualizarContador();
    actualizarContadorSticky();
  }
}


function vaciarCarrito() {
  if (typeof USUARIO_LOGUEADO !== 'undefined' && USUARIO_LOGUEADO) {
    // 🔐 Usuario logueado: vacía desde la base de datos
    fetch('vaciar_carrito.php', {
      method: 'POST'
    })
    .then(res => res.text())
    .then(res => {
      if (res.trim() === "ok") {
        lista.innerHTML = "";
        actualizarContador();
        actualizarContadorSticky();
      } else {
        console.error("Error al vaciar desde BD:", res);
      }
    });
  } else {
    // 👤 Usuario no logueado: vacía desde localStorage
    localStorage.removeItem("carrito");
    lista.innerHTML = "";
    actualizarContador();
    actualizarContadorSticky();
  }
}


document.addEventListener('DOMContentLoaded', () => {
    const btnServicios = document.getElementById('btn-servicios');
    const tooltip = document.getElementById('tooltip-servicios');

    btnServicios.addEventListener('click', (e) => {
        e.preventDefault();
        tooltip.classList.toggle('oculto');
    });

    document.addEventListener('click', (e) => {
        if (!btnServicios.contains(e.target) && !tooltip.contains(e.target)) {
            tooltip.classList.add('oculto');
        }
    });
});

const btnContacto = document.getElementById('btn-contacto');
const tooltipContacto = document.getElementById('tooltip-contacto');

btnContacto.addEventListener('click', (e) => {
    e.preventDefault();
    tooltipContacto.classList.toggle('oculto');
});

document.addEventListener('click', (e) => {
    if (!btnContacto.contains(e.target) && !tooltipContacto.contains(e.target)) {
        tooltipContacto.classList.add('oculto');
    }
});

function copiarTexto(id) {
    const texto = document.getElementById(id).textContent;
    navigator.clipboard.writeText(texto)
        .then(() => {
            alert('Copiado al portapapeles: ' + texto);
        })
        .catch(err => {
            console.error('Error al copiar:', err);
        });
}

const logoSticky = document.getElementById("logoSticky");

function actualizarLogoSticky() {
  const modalInfoAbierto = !document.getElementById('info-modal').classList.contains('oculto');
  if (window.scrollY > 100 && !modalInfoAbierto) {
    logoSticky.classList.add("visible");
  } else {
    logoSticky.classList.remove("visible");
  }
}

window.addEventListener("scroll", actualizarLogoSticky);

  document.addEventListener("DOMContentLoaded", () => {
    const carritoIcon = document.getElementById("img-carrito");
    const modal = document.getElementById("carrito-modal");
    const cerrarModal = document.getElementById("cerrar-modal");
  
    carritoIcon.addEventListener("click", (e) => {
      e.preventDefault();

      const lista = document.querySelector("#lista-carrito tbody");

      // Limpia el contenido actual del carrito
      lista.innerHTML = "";

      if (typeof USUARIO_LOGUEADO !== "undefined" && USUARIO_LOGUEADO) {
        // Si el usuario está logueado, cargar desde PHP
        fetch("mostrar_carrito.php")
          .then(res => res.text())
          .then(html => {
            lista.innerHTML = html;
            modal.classList.remove("oculto");
            actualizarContador();
            actualizarContadorSticky();
          });
      } else {
        // Si NO está logueado, cargar desde localStorage
        const carrito = JSON.parse(localStorage.getItem("carrito")) || [];

        carrito.forEach(el => {
          const row = document.createElement("tr");
          row.innerHTML = `
            <td><img src="${el.imagen}" width=100></td>
            <td>${el.titulo}</td>
            <td>${el.precio}</td>
            <td><a href="#" class="borrar" data-id="${el.id}">X</a></td>
          `;
          lista.appendChild(row);
        });

        modal.classList.remove("oculto");
        actualizarContador();
        actualizarContadorSticky();
      }
    });

    cerrarModal.addEventListener("click", () => {
      modal.classList.add("oculto");
    });
  
    window.addEventListener("click", (e) => {
      if (e.target === modal) {
        modal.classList.add("oculto");
      }
    });
  });





//sol de informacion y swipe para celular 
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

  // ✅ Abrir modal, ocultar sticky y bloquear scroll
  botonesInfo.forEach(boton => {
    boton.addEventListener('click', e => {
      e.preventDefault();
      modalInfo.classList.remove('oculto');
      document.body.classList.add('modal-abierto');
      if (logoSticky) logoSticky.classList.remove("visible");
      indiceInfoActual = 0;
      mostrarInfoActual();
    });
  });

  // ✅ Cerrar modal, restaurar scroll y validar sticky
  cerrar.addEventListener('click', () => {
    modalInfo.classList.add('oculto');
    document.body.classList.remove('modal-abierto');
    if (typeof actualizarLogoSticky === "function") {
      actualizarLogoSticky();
    }
  });

  // ✅ Botones para cambiar imagen
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

  // ✅ Deslizar en celular
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






// Modales para preguntas, sugerencias y nosotros
  const btnPregunta = document.getElementById("mostrar-pregunta");
  const modalPregunta = document.getElementById("modal-pregunta-box");
  const cerrarPregunta = document.querySelector(".cerrar-pregunta");

  btnPregunta.addEventListener("click", function(e) {
    e.preventDefault();
    modalPregunta.style.display = "block";
  });

  cerrarPregunta.addEventListener("click", function() {
    modalPregunta.style.display = "none";
  });

  window.addEventListener("click", function(e) {
    if (e.target === modalPregunta) {
      modalPregunta.style.display = "none";
    }
  });





  // Modales para sugerencias y nosotros
  const btnSugerencias = document.getElementById("mostrar-sugerencias");
  const modalSugerencias = document.getElementById("modal-sugerencias-box");
  const cerrarSugerencias = document.querySelector(".cerrar-sugerencias");

  btnSugerencias.addEventListener("click", function(e) {
    e.preventDefault();
    modalSugerencias.style.display = "block";
  });

  cerrarSugerencias.addEventListener("click", function() {
    modalSugerencias.style.display = "none";
  });

  window.addEventListener("click", function(e) {
    if (e.target === modalSugerencias) {
      modalSugerencias.style.display = "none";
    }
  });

  



// Modales para boton nosotros  
  const btnSomos = document.getElementById("mostrar-somos");
  const modalSomos = document.getElementById("modal-somos-box");
  const cerrarSomos = document.querySelector(".cerrar-somos");

    btnSomos.addEventListener("click", function(e) {
      e.preventDefault();
      modalSomos.style.display = "block";
    });

    cerrarSomos.addEventListener("click", function() {
      modalSomos.style.display = "none";
    });

    window.addEventListener("click", function(e) {
      if (e.target === modalSomos) {
        modalSomos.style.display = "none";
      }
    });
  




// Modales para politicas de privacidad
  const abrirPrivacidad = document.getElementById("abrir-privacidad");
  const modalPrivacidad = document.getElementById("modal-privacidad");
  const cerrarPrivacidad = document.querySelector(".cerrar-privacidad");

  abrirPrivacidad.addEventListener("click", function(e) {
    e.preventDefault();
    modalPrivacidad.style.display = "block";
  });

  cerrarPrivacidad.addEventListener("click", function() {
    modalPrivacidad.style.display = "none";
  });

  window.addEventListener("click", function(e) {
    if (e.target === modalPrivacidad) {
      modalPrivacidad.style.display = "none";
    }
  });





//carrito 
document.addEventListener("DOMContentLoaded", () => {
  const carritoSticky = document.getElementById("img-carrito-sticky");
  const contadorSticky = document.getElementById("contador-carrito-sticky");

  if (carritoSticky) {
    carritoSticky.addEventListener("click", (e) => {
      e.preventDefault();
      const modal = document.getElementById("carrito-modal");
      modal.classList.remove("oculto");
    });
  }


  const observer = new MutationObserver(actualizarContadorSticky);
  const lista = document.querySelector("#lista-carrito tbody");
  if (lista) {
    observer.observe(lista, { childList: true });
  }

  actualizarContadorSticky();
});


//actualiza el carrito del sticky
function actualizarContadorSticky() {
  const lista = document.querySelector("#lista-carrito tbody");
  const contadorSticky = document.getElementById("contador-carrito-sticky");

  if (!lista || !contadorSticky) return;

  const totalItems = lista.querySelectorAll("tr").length;

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

  textoPopup.textContent = texto;
  popup.classList.remove("popup-oculto");
  popup.classList.add("popup-mostrar");

  setTimeout(() => {
    popup.classList.remove("popup-mostrar");
    popup.classList.add("popup-oculto");
  }, tiempo);
}









//saludo en la nube
  document.addEventListener("DOMContentLoaded", () => {
    const nube = document.getElementById("saludo-flotante");
    if (!nube) return; // Si no hay saludo, no hace nada

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

    document.addEventListener("mouseup", () => {
      isDragging = false;
    });

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

    document.addEventListener("touchend", () => {
      isDragging = false;
    });
  });







//ventana de cerrar sesion
function cerrarModalSesion() {
  document.getElementById("modal-sesion").style.display = "none";
}

function irARegistro() {
  window.location.href = "../Login/registro/registro.htm";
}







//ventana flotante del carrito
document.addEventListener('click', function(e) {
  if (e.target.classList.contains('agregar-carrito')) {
    const boton = e.target;

    // Obtener datos
    const id_producto = boton.getAttribute('data-id');
    const nombre_producto = boton.getAttribute('data-nombre');
    const precio = boton.getAttribute('data-precio');
    const imagen = boton.getAttribute('data-imagen');

    fetch('agregar_al_carrito.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `id_producto=${id_producto}&nombre_producto=${encodeURIComponent(nombre_producto)}&precio=${precio}&imagen=${encodeURIComponent(imagen)}`
    })
    .then(res => res.text())
    .then(res => {
      console.log("Respuesta servidor:", res); // Debug

      if (res && res.includes("Producto agregado")) {
        mostrarToast("Producto añadido al carrito");

        // Recargar lista del carrito
        fetch("mostrar_carrito.php")
          .then(r => r.text())
          .then(html => {
            const lista = document.querySelector("#lista-carrito tbody");
            lista.innerHTML = html;

            // ✅ actualizar ambos contadores desde base de datos         
            actualizarContadoresGlobal();
          });
      }
    });
  }
});





//comprar
document.getElementById('comprar-carrito').addEventListener('click', function(e) {
  e.preventDefault();
  window.location.href = 'comprar_carrito.php';
});





//toast de agregado
function mostrarToast(mensaje, duracion = 2500) {
  const toast = document.getElementById('toast');
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



