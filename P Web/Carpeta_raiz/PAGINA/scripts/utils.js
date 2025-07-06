export function copiarTexto(id) {
  const texto = document.getElementById(id).textContent;

  navigator.clipboard.writeText(texto)
    .then(() => {
      alert(`Copiado al portapapeles: ${texto}`);
    })
    .catch((err) => {
      console.error("Error al copiar:", err);
    });
}

export function mostrarPopup(texto, tiempo = 2000) {
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
