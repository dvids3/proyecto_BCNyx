export function inicializarTooltips() {
  configurarTooltip("btn-servicios", "tooltip-servicios");
  configurarTooltip("btn-contacto", "tooltip-contacto");

  function configurarTooltip(btnId, tooltipId) {
    const btn = document.getElementById(btnId);
    const tooltip = document.getElementById(tooltipId);

    btn.addEventListener("click", (e) => {
      e.preventDefault();
      tooltip.classList.toggle("oculto");
    });

    document.addEventListener("click", (e) => {
      if (!btn.contains(e.target) && !tooltip.contains(e.target)) {
        tooltip.classList.add("oculto");
      }
    });
  }
}
