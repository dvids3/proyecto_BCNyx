// ==== carrito.js ====
const lista = document.querySelector("#lista-carrito tbody");
const vaciarCarritoBtn = document.getElementById("vaciar-carrito");
const elemetos1 = document.getElementById("lista-1");
const elemetos2 = document.getElementById("lista-2");

function actualizarContador() {
  const contador = document.getElementById("contador-carrito");
  const totalItems = lista.querySelectorAll("tr").length;
  contador.textContent = totalItems;
  contador.style.display = totalItems > 0 ? "inline-block" : "none";
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

function leerDatosElemento(elemento) {
  const infoElemento = {
    imagen: elemento.querySelector("img").src,
    titulo: elemento.querySelector("h3").textContent,
    precio: elemento.querySelector(".precio").textContent,
    id: elemento.querySelector("a").getAttribute("data-id"),
  };
  insertarCarrito(infoElemento);
}

function comprarElemento(e) {
  e.preventDefault();
  if (e.target.classList.contains("agregar-carrito")) {
    const elemento = e.target.closest(".producto");
    leerDatosElemento(elemento);
  }
}

function eliminarElemento(e) {
  e.preventDefault();
  if (e.target.classList.contains("borrar")) {
    const elemento = e.target.closest("tr");
    const id = e.target.getAttribute("data-id");
    elemento.remove();
    fetch('eliminar_del_carrito.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `id_producto=${id}`
    });
    actualizarContador();
  }
}

function vaciarCarrito() {
  lista.innerHTML = "";
  actualizarContador();
}

function cargareventListeners() {
  elemetos1?.addEventListener("click", comprarElemento);
  elemetos2?.addEventListener("click", comprarElemento);
  document.getElementById("carrito-modal")?.addEventListener("click", eliminarElemento);
  vaciarCarritoBtn?.addEventListener("click", vaciarCarrito);
}
