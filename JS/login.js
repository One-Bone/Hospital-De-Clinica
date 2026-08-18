// Archivo: js/login.js

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('loginForm');

    form.addEventListener('submit', async function(event) {
        event.preventDefault(); 

        //toma los datos del login
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        try {
            const backendURL = 'url de la futura base de datos aca '; 
            const respuesta = await fetch(backendURL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            if (respuesta.ok) {
                alert('¡Inicio de sesión exitoso!');
                //si el login es correcto redirige a al pagina principal
                window.location.href = 'index.html'; 
            } else {
                alert('Usuario o contraseña incorrectos.');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Hubo un problema al conectar con el servidor.');
        }
    });
});