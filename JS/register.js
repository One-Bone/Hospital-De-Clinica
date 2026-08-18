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
 
        // Usamos FormData directo (sin convertir a JSON), porque PHP
        // llena $_POST a partir de este formato, no de JSON crudo.
        const formData = new FormData(form);
 
        try {
 
            const backendURL = 'php/procesar_registro.php';
 
            const respuesta = await fetch(backendURL, {
                method: 'POST',
                body: formData
                // No seteamos 'Content-Type' a mano: el navegador arma
                // automaticamente el header correcto (multipart/form-data)
                // junto con el boundary que necesita PHP para leerlo bien.
            });
 
            if (respuesta.ok) {
                // si el registro es exitoso
                alert('¡Registro exitoso!');
                window.location.href = 'php/index.php'; // Redirige al login
            } else {
                // si detecta un error en el registro
                alert('Error al registrar. Por favor, intenta de nuevo.');
            }
        } catch (error) {
            // Si cae el server tira error 
            console.error('Error de conexión:', error);
            alert('Hubo un problema al conectar con el servidor.');
        }
    });
});
 