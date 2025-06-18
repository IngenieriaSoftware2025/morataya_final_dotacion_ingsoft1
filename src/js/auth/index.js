import Swal from "sweetalert2";

document.addEventListener('DOMContentLoaded', function() {
    const formulario = document.getElementById('FormLogin');
    
    if(formulario) {
        formulario.addEventListener('submit', function(e) {
            e.preventDefault();
            procesarLogin();
        });
    }
});

async function procesarLogin() {
    const codigo = document.querySelector('input[name="codigo"]').value;
    const password = document.querySelector('input[name="password"]').value;
    
    if(!codigo || !password) {
        Swal.fire('Error', 'Todos los campos son obligatorios', 'error');
        return;
    }
    
    try {
        Swal.fire({
            title: 'Iniciando sesión...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        const datos = new FormData();
        datos.append('codigo', codigo);
        datos.append('password', password);
        
        const respuesta = await fetch('/login', {
            method: 'POST',
            body: datos
        });
        
        if(respuesta.redirected) {
            window.location.href = respuesta.url;
        } else {
            Swal.fire('Error', 'Credenciales incorrectas', 'error');
        }
    } catch (error) {
        Swal.fire('Error', 'Error de conexión', 'error');
    }
}
