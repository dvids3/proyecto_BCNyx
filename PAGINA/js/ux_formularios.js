const ubicacionesColombia = {
  "Antioquia": ["Medellín", "Bello", "Envigado", "Itagüí", "Rionegro"],
  "Atlántico": ["Barranquilla", "Soledad", "Malambo", "Puerto Colombia"],
  "Bogotá D.C.": ["Bogotá"],
  "Bolívar": ["Cartagena", "Magangué", "Turbaco"],
  "Boyacá": ["Tunja", "Duitama", "Sogamoso"],
  "Caldas": ["Manizales", "Chinchiná", "La Dorada"],
  "Cauca": ["Popayán", "Santander de Quilichao"],
  "Cesar": ["Valledupar", "Aguachica"],
  "Córdoba": ["Montería", "Cereté", "Lorica"],
  "Cundinamarca": ["Soacha", "Chía", "Zipaquirá", "Facatativá", "Mosquera"],
  "Huila": ["Neiva", "Pitalito"],
  "La Guajira": ["Riohacha", "Maicao"],
  "Magdalena": ["Santa Marta", "Ciénaga"],
  "Meta": ["Villavicencio", "Acacías"],
  "Nariño": ["Pasto", "Ipiales", "Tumaco"],
  "Norte de Santander": ["Cúcuta", "Ocaña", "Villa del Rosario"],
  "Quindío": ["Armenia", "Calarcá", "Montenegro"],
  "Risaralda": ["Pereira", "Dosquebradas", "Santa Rosa de Cabal"],
  "Santander": ["Bucaramanga", "Floridablanca", "Girón", "Barrancabermeja"],
  "Sucre": ["Sincelejo", "Corozal"],
  "Tolima": ["Ibagué", "Espinal", "Honda"],
  "Valle del Cauca": ["Cali", "Palmira", "Buenaventura", "Tuluá", "Buga"]
};

function enlazarDatalist(input, id, valores) {
  if (!input) return;
  let lista = document.getElementById(id);
  if (!lista) {
    lista = document.createElement('datalist');
    lista.id = id;
    document.body.appendChild(lista);
  }
  lista.replaceChildren(...valores.map(valor => {
    const option = document.createElement('option');
    option.value = valor;
    return option;
  }));
  input.setAttribute('list', id);
}

document.addEventListener('DOMContentLoaded', () => {
  const departamento = document.querySelector('[name="departamento"]');
  const ciudad = document.querySelector('[name="ciudad"]');
  enlazarDatalist(departamento, 'departamentos-colombia', Object.keys(ubicacionesColombia));
  const actualizarCiudades = () => {
    const sugeridas = ubicacionesColombia[departamento?.value] || Object.values(ubicacionesColombia).flat();
    enlazarDatalist(ciudad, 'ciudades-colombia', sugeridas);
  };
  departamento?.addEventListener('change', actualizarCiudades);
  departamento?.addEventListener('input', actualizarCiudades);
  actualizarCiudades();

  document.querySelectorAll('form[data-ux-form]').forEach(form => {
    form.addEventListener('submit', event => {
      if (!form.checkValidity()) {
        event.preventDefault();
        form.reportValidity();
        form.querySelector(':invalid')?.focus();
        return;
      }
      const boton = form.querySelector('[type="submit"]');
      if (boton) {
        boton.disabled = true;
        boton.dataset.textoAnterior = boton.textContent || boton.value;
        if (boton.tagName === 'INPUT') boton.value = 'Procesando…';
        else boton.textContent = 'Procesando…';
      }
    });
  });

  document.querySelectorAll('[data-contador]').forEach(campo => {
    const salida = document.querySelector(campo.dataset.contador);
    const actualizar = () => { if (salida) salida.textContent = `${campo.value.length}/${campo.maxLength}`; };
    campo.addEventListener('input', actualizar);
    actualizar();
  });
});
