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

  if (typeof sesionActiva !== "undefined" && !sesionActiva) {
    document.getElementById("modal-sesion").style.display = "flex";
    return;
  }

  if (e.target.classList.contains("agregar-carrito")) {
    const elemento = e.target.parentElement.parentElement;
    leerDatosElemento(elemento);
  }
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

function insertarCarrito(Elemento) {
    const row = document.createElement("tr");
    row.innerHTML = `
        <td><img src="${Elemento.imagen}" width=100></td>
        <td>${Elemento.titulo}</td>
        <td>${Elemento.precio}</td>
        <td><a href="#" class="borrar" data-id="${Elemento.id}">X</a></td>
    `;
    lista.appendChild(row);
    actualizarContador();
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
  

function eliminarElemento(e) {
  e.preventDefault();
  let elemento, elementoId;

  if (e.target.classList.contains("borrar")) {
    elemento = e.target.parentElement.parentElement;
    elementoId = elemento.querySelector("a").getAttribute("data-id");

    // Elimina del DOM
    elemento.remove();

    // Elimina de la base de datos (nuevo código)
    fetch('eliminar_del_carrito.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `id_producto=${elementoId}`
    })
    .then(res => res.text())
    .then(res => {
      console.log("Servidor: ", res); // opcional
    });

    // Actualiza el contador
    actualizarContador();
  }
}


function vaciarCarrito() {
    while (lista.firstChild) {
        lista.removeChild(lista.firstChild);
    }
    actualizarContador();
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

    // Carga el contenido desde PHP
    fetch("mostrar_carrito.php")
      .then(res => res.text())
      .then(html => {
        lista.innerHTML = html; // Inserta las filas en la tabla
        modal.classList.remove("oculto"); // Muestra el modal
      });
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

const botonesInfo = document.querySelectorAll('.abrir-info');
const modalInfo = document.getElementById('info-modal');
const texto = document.getElementById('info-texto');
const cerrar = document.getElementById('cerrar-info-modal');

botonesInfo.forEach(boton => {
    boton.addEventListener('click', e => {
    e.preventDefault();
    const textoBoton = boton.getAttribute('data-texto');
    texto.innerHTML = textoBoton;

    modalInfo.classList.remove('oculto');
    logoSticky.classList.remove("visible");

    });

  const botonesInfo = document.querySelectorAll('.abrir-info');
  const modalInfo = document.getElementById('info-modal');
  const textoInfo = document.getElementById('info-texto');
  const cerrarInfo = document.getElementById('cerrar-info-modal');
  
  botonesInfo.forEach(boton => {
    boton.addEventListener('click', e => {
      e.preventDefault();
      const textoBoton = boton.getAttribute('data-texto');
      textoInfo.textContent = textoBoton;
      modalInfo.classList.remove('oculto');
      logoSticky.style.display = 'none'; 
    });

  });
});


cerrar.addEventListener('click', () => {
  modalInfo.classList.add('oculto');
  actualizarLogoSticky(); 
});
  

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

  const actualizarContadorSticky = () => {
    const lista = document.querySelector("#lista-carrito tbody");
    const totalItems = lista.querySelectorAll("tr").length;
    if (totalItems > 0) {
      contadorSticky.textContent = totalItems;
      contadorSticky.style.display = "inline-block";
    } else {
      contadorSticky.style.display = "none";
    }
  };

  const observer = new MutationObserver(actualizarContadorSticky);
  const lista = document.querySelector("#lista-carrito tbody");
  if (lista) {
    observer.observe(lista, { childList: true });
  }

  actualizarContadorSticky();
});

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
