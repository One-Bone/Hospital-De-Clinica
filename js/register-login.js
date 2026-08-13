document.addEventListener('DOMContentLoaded', () => {
    // Seleccionamos los elementos
    const form = document.getElementById('registerForm');
    const password = document.getElementById('password');
    const confPass = document.getElementById('conf-pass');
    const passwordError = document.getElementById('password-error');
    const matchError = document.getElementById('match-error');

    form.addEventListener('submit', async function(event) {
        // Detenemos el envío tradicional para manejarlo nosotros
        event.preventDefault(); 
        
        let isValid = true;
        
        // Expresión regular: mínimo 8 caracteres y al menos un dígito o símbolo
        const passwordRegex = /^(?=.*[\d\W_]).{8,}$/;

        // 1. Validamos la seguridad de la contraseña
        if (!passwordRegex.test(password.value)) {
            passwordError.style.display = 'block';
            isValid = false;
        } else {
            passwordError.style.display = 'none';
        }

        // 2. Validamos que ambas contraseñas sean idénticas
        if (password.value !== confPass.value) {
            matchError.style.display = 'block';
            isValid = false;
        } else {
            matchError.style.display = 'none';
        }

        // Si hay errores, cancelamos el proceso aquí
        if (!isValid) {
            return;
        }

        // --- PREPARACIÓN Y ENVÍO DE DATOS AL BACKEND ---
        
        // Extraemos los datos de los inputs del formulario
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        try {
            // Reemplaza esto con la URL real de tu backend (ej. 'registro.php' o '/api/registro')
            const backendURL = 'URL_DE_TU_BACKEND_AQUI'; 

            const respuesta = await fetch(backendURL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });

            if (respuesta.ok) {
                // Si el servidor responde con éxito (código 200)
                alert('¡Registro exitoso!');
                window.location.href = 'login.html'; // Redirige al login
            } else {
                // Si el servidor detecta un problema (ej. el usuario ya existe)
                alert('Error al registrar. Por favor, intenta de nuevo.');
            }
        } catch (error) {
            // Si el servidor está caído o hay un error de red
            console.error('Error de conexión:', error);
            alert('Hubo un problema al conectar con el servidor.');
        }
    });
});