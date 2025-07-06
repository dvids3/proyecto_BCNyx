document.querySelectorAll('.eliminar-producto').forEach(btn => {
  btn.addEventListener('click', function(e) {
    e.preventDefault();
    const id = this.getAttribute('data-id');
    console.log("Eliminando producto con ID:", id);

    fetch('eliminar_de_orden.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'id_producto=' + encodeURIComponent(id)
    })
    .then(res => res.text())
    .then(res => {
      if (res.trim() === 'ok') {
        // Elimina la fila del DOM
        this.closest('tr').remove();
        // Recalcula el total
        recalcularTotal();
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
