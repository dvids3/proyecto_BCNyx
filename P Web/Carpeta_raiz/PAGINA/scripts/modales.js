export function inicializarModales() {
  configurarModal("mostrar-pregunta", "modal-pregunta-box");
  configurarModal("mostrar-sugerencias", "modal-sugerencias-box");
  configurarModal("mostrar-somos", "modal-somos-box");
  configurarModal("abrir-privacidad", "modal-privacidad");
  configurarInfoModal();
}

function configurarModal(btnId, modalId) {
  const btn = document.getElementById(btnId);
  const modal = document.getElementById(modalId);
  const cerrar = modal.querySelector(".cerrar");

  btn.addEventListener("click", (e) => {
    e.preventDefault();
    modal.style.display = "block";
  });

  cerrar.addEventListener("click", () => {
    modal.style.display = "none";
  });

  window.addEventListener("click", (e) => {
    if (e.target === modal) {
      modal.style.display = "none";
    }
  });
}

function configurarInfoModal() {
  const botonesInfo = document.querySelectorAll(".abrir-info");
  const modalInfo = document.getElementById("info-modal");
  const texto = document.getElementById("info-texto");
  const cerrar = document.getElementById("cerrar-info-modal");

  botonesInfo.forEach((boton) => {
    boton.addEventListener("click", (e) => {
      e.preventDefault();
      const textoBoton = boton.getAttribute("data-texto");
      texto.innerHTML = textoBoton;
      modalInfo.classList.remove("oculto");

      const logoSticky = document.getElementById("logoSticky");
      logoSticky.classList.remove("visible");
    });
  });

  cerrar.addEventListener("click", () => {
    modalInfo.classList.add("oculto");

    const logoSticky = document.getElementById("logoSticky");
    logoSticky.classList.remove("visible"); // Si quieres que desaparezca siempre
    window.dispatchEvent(new Event("scroll")); // Si quieres que se actualice el sticky al cerrar
  });
}
