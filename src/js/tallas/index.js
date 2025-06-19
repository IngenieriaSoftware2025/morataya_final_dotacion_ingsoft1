import Swal from "sweetalert2";

let tablaTallas;

document.addEventListener('DOMContentLoaded', function() {
    cargarTallas();
    configurarFormulario();
});

async function cargarTallas() {
    try {
        const respuesta = await fetch('/tallas/obtenerAPI');
        const tallas = await respuesta.json();
        
        if(tablaTallas) {
            tablaTallas.destroy();
        }
        
        mostrarTallas(tallas);
        inicializarDataTable();
    } catch (error) {
        console.error('Error al cargar tallas:', error);
        Swal.fire('Error', 'No se pudieron cargar las tallas', 'error');
    }
}

function mostrarTallas(tallas) {
    const tbody = document.querySelector('#TablaTallas tbody');
    tbody.innerHTML = '';
    
    tallas.forEach(talla => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><strong>${talla.talla_etiqueta}</strong></td>
            <td>
                <span class="badge bg-${talla.talla_situacion == 1 ? 'success' : 'danger'}">
                    ${talla.talla_situacion == 1 ? 'Activo' : 'Inactivo'}
                </span>
            </td>
            <td>
                <button class="btn btn-sm btn-outline-danger" onclick="eliminarTalla(${talla.talla_id})">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function configurarFormulario() {
    const formulario = document.getElementById('FormTalla');
    
    if(formulario) {
        formulario.addEventListener('submit', function(e) {
            e.preventDefault();
            guardarTalla();
        });
    }
}

async function guardarTalla() {
    try {
        Swal.fire({
            title: 'Guardando...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        const datos = new FormData(document.getElementById('FormTalla'));
        
        const respuesta = await fetch('/tallas/guardarAPI', {
            method: 'POST',
            body: datos
        });
        
        const resultado = await respuesta.json();
        
        if(resultado.resultado) {
            Swal.fire('Éxito', resultado.mensaje, 'success');
            document.getElementById('FormTalla').reset();
            bootstrap.Modal.getInstance(document.getElementById('ModalTalla')).hide();
            cargarTallas();
        } else {
            if(resultado.errores && Array.isArray(resultado.errores)) {
                Swal.fire('Error', resultado.errores.join('\n'), 'error');
            } else {
                Swal.fire('Error', resultado.mensaje, 'error');
            }
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire('Error', 'Error al guardar talla', 'error');
    }
}

window.eliminarTalla = async function(id) {
    try {
        const confirmacion = await Swal.fire({
            title: '¿Estás seguro?',
            text: "Esta acción no se puede deshacer",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        });
        
        if(!confirmacion.isConfirmed) return;
        
        Swal.fire({
            title: 'Eliminando...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        const respuesta = await fetch(`/tallas/eliminarAPI?talla_id=${id}`);
        const resultado = await respuesta.json();
        
        if(resultado.resultado) {
            Swal.fire('Eliminado', resultado.mensaje, 'success');
            cargarTallas();
        } else {
            Swal.fire('Error', resultado.mensaje, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire('Error', 'Error al eliminar talla', 'error');
    }
};

function inicializarDataTable() {
    tablaTallas = $('#TablaTallas').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
        },
        responsive: true,
        pageLength: 25
    });
}