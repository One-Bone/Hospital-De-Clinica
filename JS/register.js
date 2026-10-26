document.addEventListener('DOMContentLoaded', () => {
    // Seleccionamos los elementos
    const form = document.getElementById('registerForm');
    const password = document.getElementById('password');
    const confPass = document.getElementById('conf-pass');
    const passwordError = document.getElementById('password-error');
    const matchError = document.getElementById('match-error');

    form.addEventListener('submit', async function(event) {
        event.preventDefault();

        let isValid = true;

        if (password.value.length < 8 || password.value.length > 128 || !/(?=.*[\d\W_])/.test(password.value)) {
            passwordError.style.display = 'block';
            isValid = false;
        } else {
            passwordError.style.display = 'none';
        }

        if (password.value !== confPass.value) {
            matchError.style.display = 'block';
            isValid = false;
        } else {
            matchError.style.display = 'none';
        }

        if (!isValid) {
            return;
        }

        const formData = new FormData(form);

        try {
            const backendURL = 'php/procesar_registro.php';

            const respuesta = await fetch(backendURL, {
                method: 'POST',
                body: formData
            });

            
            const textoBackend = await respuesta.text();

            
            if (respuesta.ok && textoBackend.trim() === "Exito") {
                alert(window.getSiteTranslation?.('registration_success') || '¡Registro exitoso!');
                window.location.href = 'php/login.php';
            } else {
            
                console.error("Error del servidor:", textoBackend);
                alert(textoBackend || 'Error al registrar. Verifica los datos.');
            }
        } catch (error) {
            console.error('Error de conexión:', error);
            alert(window.getSiteTranslation?.('connection_error') || 'Hubo un problema al conectar con el servidor.');
        }
    });
});