document.querySelectorAll('.eliminar-producto').forEach(btn => {
  btn.addEventListener('click', function(e) {
    e.preventDefault();
    const id = this.getAttribute('data-id');
    console.log("Eliminando producto con ID:", id);

    fetch('eliminar_del_carrito.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-Token': document.querySelector('input[name="csrf"]')?.value || '' },
      body: 'id_producto=' + encodeURIComponent(id)
    })
    .then(res => res.text())
    .then(res => {
      if (res.trim() === 'ok') {
        // Elimina la fila del DOM
        this.closest('tr').remove();
        // Recalcula el total
        recalcularTotal();
        if (document.querySelectorAll("#lista-carrito tbody tr").length === 0) {
          const formulario = document.querySelector(".checkout-shell form");
          const scrollBtn = document.getElementById("scrollBtn");
          formulario?.setAttribute("hidden", "");
          scrollBtn?.setAttribute("hidden", "");

          if (formulario && !document.querySelector(".checkout-shell .estado-vacio")) {
            const estadoVacio = document.createElement("section");
            estadoVacio.className = "estado-vacio";
            const titulo = document.createElement("h2");
            titulo.textContent = "Tu carrito está vacío";
            const descripcion = document.createElement("p");
            descripcion.textContent = "Agrega una prenda para comenzar una compra.";
            const enlace = document.createElement("a");
            enlace.href = "index.php";
            enlace.textContent = "Ver colección";
            estadoVacio.append(titulo, descripcion, enlace);
            formulario.insertAdjacentElement("afterend", estadoVacio);
          }
        }
      } else {
        alert('Error al eliminar producto.');
      }
    });
  });
});

function recalcularTotal() {
  let total = 0;

  const filas = document.querySelectorAll("tbody tr");
  filas.forEach((fila) => {
    const subtotalCell = fila.querySelector("td:nth-child(5)");
    if (subtotalCell) {
      const subtotalTexto = subtotalCell.textContent.replace(/[^\d]/g, "");
      const subtotal = parseInt(subtotalTexto) || 0;
      total += subtotal;
    }
  });

  const totalCell = document.querySelector("tfoot td:last-child strong");
  if (totalCell) {
    totalCell.textContent = "$" + total.toLocaleString("es-CO");
  }
}

document.addEventListener("DOMContentLoaded", () => {
  const scrollBtn = document.getElementById("scrollBtn");
  if (scrollBtn) {
    scrollBtn.addEventListener("click", () => {
      document.getElementById('datos-pago')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  }
});

const metodoPago = document.getElementById('metodo_pago');
const detalleMetodo = document.getElementById('detalle-metodo');
const mensajesMetodo = {
  contraentrega: 'Pagarás al recibir. En esta demo podrás simular el cobro y la entrega.',
  tarjeta: 'Serás llevado al simulador local de tarjeta. No debes ingresar datos bancarios reales.',
  pse: 'Serás llevado al simulador local de PSE. No existe conexión con un banco real.'
};
metodoPago?.addEventListener('change', () => {
  if (detalleMetodo) detalleMetodo.textContent = mensajesMetodo[metodoPago.value] || 'Selecciona un método para ver cómo continúa el proceso.';
});
