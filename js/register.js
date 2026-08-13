document.addEventListener('DOMContentLoaded', () => {
    // Seleccionamos los elementos
    const form = document.getElementById('registerForm');
    const password = document.getElementById('password');
    const confPass = document.getElementById('conf-pass');
    const passwordError = document.getElementById('password-error');
    const matchError = document.getElementById('match-error');

    form.addEventListener('submit', async function(event) {
        //detenemos el envio default 
        event.preventDefault(); 
        
        let isValid = true;
        
        // al menos 8 digitos y un numero o simbolo
        const passwordRegex = /^(?=.*[\d\W_]).{8,}$/;

        // validamos la seguridad de las contrseñas
        if (!passwordRegex.test(password.value)) {
            passwordError.style.display = 'block';
            isValid = false;
        } else {
            passwordError.style.display = 'none';
        }

        // validamos si las contraseñas son iguales
        if (password.value !== confPass.value) {
            matchError.style.display = 'block';
            isValid = false;
        } else {
            matchError.style.display = 'none';
        }

        // se canela aca si hay errores
        if (!isValid) {
            return;
        }

        // --- envio de datos al backend
        
        // Extraemos los datos de los inputs del formulario
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        try {

            const backendURL = 'futura url de la base de datos aca'; 

            const respuesta = await fetch(backendURL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });

            if (respuesta.ok) {
                // si el registro esexitoso
                alert('¡Registro exitoso!');
                window.location.href = 'login.html'; // Redirige al login
            } else {
                // si detecta un error en el registro
                alert('Error al registrar. Por favor, intenta de nuevo.');
            }
        } catch (error) {
            // Si ca el server tira error 
            console.error('Error de conexión:', error);
            alert('Hubo un problema al conectar con el servidor.');
        }
    });
});