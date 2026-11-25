document.addEventListener("DOMContentLoaded", function () {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get("error") === "credenciales") {
        const mensaje = document.getElementById("login-error");
        if (mensaje) {
            mensaje.textContent = "Correo o contraseña incorrectos.";
            mensaje.hidden = false;
        }
    }
});
