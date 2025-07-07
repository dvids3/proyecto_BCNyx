document.addEventListener("DOMContentLoaded", function () {
const form = document.getElementById("form-login");

if (form) {
    form.addEventListener("submit", function (e) {
        limpiarErrores();

        const nombre = document.querySelector('[name="nombre"]');
        const password = document.querySelector('[name="clave"]');
        const correo = document.querySelector('[name="correo"]');
        const telefono = document.querySelector('[name="telefono"]');

        let valido = true;

        if (nombre && nombre.value.trim() === "") {
            mostrarError(nombre, "El nombre es obligatorio.");
            valido = false;
        }

        if (password && password.value.length <= 5) {
            mostrarError(password, "La contraseña debe tener más de 5 caracteres.");
            valido = false;
        }

        const regexCorreo = /^[a-zA-Z0-9._%+-]+@gmail\.com$/;
        if (correo && !regexCorreo.test(correo.value)) {
            mostrarError(correo, "El correo debe ser una cuenta de Gmail.");
            valido = false;
        }

        const regexTelefono = /^3\d{9}$/;
        if (telefono && !regexTelefono.test(telefono.value)) {
            mostrarError(telefono, "El número debe tener 10 dígitos y comenzar con 3.");
            valido = false;
        }

        if (!valido) {
            e.preventDefault();
        }
    });
}

    const urlParams = new URLSearchParams(window.location.search);
    const error = urlParams.get("error");

    if (error === "clave") {
        const password = document.querySelector('[name="clave"]');
        if (password) mostrarErrorLogin(password, "⚠️ Contraseña incorrecta.");
    } else if (error === "usuario") {
        const correo = document.querySelector('[name="correo"]');
        if (correo) mostrarErrorLogin(correo, "⚠️ Usuario no encontrado.");
    }

    function mostrarErrorLogin(input, mensaje) {
        const contenedor = input.parentElement;

        if (contenedor.querySelector(".error-login")) return;

        const error = document.createElement("span");
        error.className = "error-login";
        error.innerText = mensaje;
        contenedor.appendChild(error);
    }

    function mostrarError(input, mensaje) {
        const contenedor = input.parentElement;
        const error = document.createElement("span");
        error.className = "error";
        error.innerText = mensaje;
        contenedor.appendChild(error);
    }

    function limpiarErrores() {
        document.querySelectorAll(".error, .error-login").forEach(e => e.remove());
    }
});
