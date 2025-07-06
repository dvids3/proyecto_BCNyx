export function inicializarSticky() {
  const logoSticky = document.getElementById("logoSticky");
  const carritoSticky = document.getElementById("img-carrito-sticky");
  const contadorSticky = document.getElementById("contador-carrito-sticky");
  const lista = document.querySelector("#lista-carrito tbody");

  window.addEventListener("scroll", () => {
    const modalInfoAbierto = !document.getElementById("info-modal").classList.contains("oculto");
    logoSticky.classList.toggle("visible", window.scrollY > 100 && !modalInfoAbierto);
  });

  if (carritoSticky) {
    carritoSticky.addEventListener("click", (e) => {
      e.preventDefault();
      document.getElementById("carrito-modal").classList.remove("oculto");
    });
  }

  const observer = new MutationObserver(() => {
    const totalItems = lista.querySelectorAll("tr").length;
    contadorSticky.textContent = totalItems;
    contadorSticky.style.display = totalItems > 0 ? "inline-block" : "none";
  });

  observer.observe(lista, { childList: true });
}
