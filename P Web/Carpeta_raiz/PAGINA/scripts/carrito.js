export function inicializarCarrito() {
  const lista = document.querySelector("#lista-carrito tbody");
  const vaciarCarritoBtn = document.getElementById("vaciar-carrito");

  document.getElementById("lista-1").addEventListener("click", comprarElemento);
  document.getElementById("lista-2").addEventListener("click", comprarElemento);
  document.getElementById("carrito-modal").addEventListener("click", eliminarElemento);
  vaciarCarritoBtn.addEventListener("click", vaciarCarrito);

  function comprarElemento(e) {
    e.preventDefault();
    if (e.target.classList.contains("agregar-carrito")) {
      const elemento = e.target.closest(".producto");
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

  function insertarCarrito(elemento) {
    const row = document.createElement("tr");
    row.innerHTML = `
      <td><img src="${elemento.imagen}" width=100></td>
      <td>${elemento.titulo}</td>
      <td>${elemento.precio}</td>
      <td><a href="#" class="borrar" data-id="${elemento.id}">X</a></td>
    `;
    lista.appendChild(row);
    actualizarContador();
  }

  function eliminarElemento(e) {
    if (e.target.classList.contains("borrar")) {
      const row = e.target.closest("tr");
      const id = e.target.getAttribute("data-id");
      row.remove();
      fetch("eliminar_del_carrito.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `id_producto=${id}`,
      });
      actualizarContador();
    }
  }

  function vaciarCarrito() {
    lista.innerHTML = "";
    actualizarContador();
  }

  function actualizarContador() {
    const contador = document.getElementById("contador-carrito");
    const totalItems = lista.querySelectorAll("tr").length;
    contador.textContent = totalItems;
    contador.style.display = totalItems > 0 ? "inline-block" : "none";
  }
}
