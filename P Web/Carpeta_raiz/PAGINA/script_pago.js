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
      } else {
        alert('Error al eliminar producto.');
      }
    });
  });
});

