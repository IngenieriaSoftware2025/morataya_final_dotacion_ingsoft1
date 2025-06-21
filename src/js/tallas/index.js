import Swal from "sweetalert2";

let tablaTallas;
let tallasData = [];

document.addEventListener('DOMContentLoaded', function() {
    cargarTallas();
    configurarEventos();
    inicializarDataTable();
});

function configurarEventos() {
    // Formulario agregar
    const formTalla = document.getElementById('FormTalla');
    if (formTalla) {
        formTalla.addEventListener('submit', function(e) {
            e.preventDefault();
            guardarTalla();
        });
    }
    
    // Formulario editar
    const formEditarTalla = document.getElementById('FormEditarTalla');
    if (formEditarTalla) {
        formEditarTalla.addEventListener('submit', function(e) {
            e.preventDefault();
            actualizarTalla();
        });
    }
}

async function cargarTallas() {
    try {
        const respuesta = await fetch('/morataya_final_dotacion_ingsoft1/tallas/obtenerAPI');
        const tallas = await respuesta.json();
        
        tallasData = tallas;
        mostrarTallas(tallas);
        
        if (tablaTallas) {
            tablaTallas.destroy();
        }
        inicializarDataTable();
        
    } catch (error) {
        console.error('Error al cargar tallas:', error);
        Swal.fire('Error', 'No se pudieron cargar las tallas', 'error');
    }
}

function mostrarTallas(tallas) {
    const tbody = document.getElementById('TablaTallasBody');
    
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    if (!tallas || tallas.length === 0) {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td colspan="3" class="text-center py-4 text-muted">
                <i class="bi bi-inbox me-2"></i>No hay tallas registradas
            </td>
        `;
        tbody.appendChild(tr);
        return;
    }
    
    tallas.forEach((talla) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><strong>${talla.talla_etiqueta}</strong></td>
            <td>
                <span class="badge bg-${talla.talla_situacion == 1 ? 'success' : 'danger'}">
                    ${talla.talla_situacion == 1 ? 'Activo' : 'Inactivo'}
                </span>
            </td>
            <td>
                <button class="btn btn-sm btn-outline-primary me-1" onclick="editarTalla(${talla.talla_id})" title="Editar">
                    <i class="bi bi-pencil"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger" onclick="eliminarTalla(${talla.talla_id})" title="Eliminar">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

async function guardarTalla() {
    try {
        const formData = new FormData(document.getElementById('FormTalla'));
        const etiqueta = formData.get('talla_etiqueta').trim();
        
        if (!etiqueta) {
            Swal.fire('Error', 'Debe ingresar una etiqueta para la talla', 'error');
            return;
        }
        
        // Validación de formato
        const etiquetaUpper = etiqueta.toUpperCase();
        if (!validarFormatoTalla(etiquetaUpper)) {
            Swal.fire({
                icon: 'error',
                title: 'Formato no válido',
                text: 'Use letras (XS, S, M, L, XL, XXL, XXXL) o números (1-60)',
                confirmButtonText: 'Entendido'
            });
            return;
        }
        
        // Mostrar loading
        Swal.fire({
            title: 'Guardando...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        // Actualizar el FormData con la etiqueta validada
        formData.set('talla_etiqueta', etiquetaUpper);
        
        const respuesta = await fetch('/morataya_final_dotacion_ingsoft1/tallas/guardarAPI', {
            method: 'POST',
            body: formData
        });
        
        const resultado = await respuesta.json();
        
        if (resultado.resultado) {
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: resultado.mensaje || 'Talla guardada correctamente',
                timer: 1500,
                showConfirmButton: false
            });
            
            // Cerrar modal y limpiar formulario
            const modal = bootstrap.Modal.getInstance(document.getElementById('ModalTalla'));
            modal.hide();
            document.getElementById('FormTalla').reset();
            
            // Recargar tabla
            cargarTallas();
            
        } else {
            let mensajeError = resultado.mensaje || 'Error al guardar la talla';
            
            if (resultado.errores && Array.isArray(resultado.errores)) {
                mensajeError = resultado.errores.join('\n');
            }
            
            Swal.fire({
                icon: 'error',
                title: 'Error de validación',
                text: mensajeError,
                confirmButtonText: 'Entendido'
            });
        }
        
    } catch (error) {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error de conexión',
            text: 'Error al guardar la talla. Verifique su conexión.',
            confirmButtonText: 'Entendido'
        });
    }
}

async function actualizarTalla() {
    try {
        const formData = new FormData(document.getElementById('FormEditarTalla'));
        const etiqueta = formData.get('talla_etiqueta').trim();
        const tallaId = formData.get('talla_id');
        
        if (!etiqueta) {
            Swal.fire('Error', 'Debe ingresar una etiqueta para la talla', 'error');
            return;
        }
        
        // Validación de formato
        const etiquetaUpper = etiqueta.toUpperCase();
        if (!validarFormatoTalla(etiquetaUpper)) {
            Swal.fire({
                icon: 'error',
                title: 'Formato no válido',
                text: 'Use letras (XS, S, M, L, XL, XXL, XXXL) o números (1-60)',
                confirmButtonText: 'Entendido'
            });
            return;
        }
        
        // Mostrar loading
        Swal.fire({
            title: 'Actualizando...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        // Actualizar el FormData con la etiqueta validada
        formData.set('talla_etiqueta', etiquetaUpper);
        
        const respuesta = await fetch('/morataya_final_dotacion_ingsoft1/tallas/guardarAPI', {
            method: 'POST',
            body: formData
        });
        
        const resultado = await respuesta.json();
        
        if (resultado.resultado) {
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: resultado.mensaje || 'Talla actualizada correctamente',
                timer: 1500,
                showConfirmButton: false
            });
            
            // Cerrar modal y limpiar formulario
            const modal = bootstrap.Modal.getInstance(document.getElementById('ModalEditarTalla'));
            modal.hide();
            document.getElementById('FormEditarTalla').reset();
            
            // Recargar tabla
            cargarTallas();
            
        } else {
            let mensajeError = resultado.mensaje || 'Error al actualizar la talla';
            
            if (resultado.errores && Array.isArray(resultado.errores)) {
                mensajeError = resultado.errores.join('\n');
            }
            
            Swal.fire({
                icon: 'error',
                title: 'Error de validación',
                text: mensajeError,
                confirmButtonText: 'Entendido'
            });
        }
        
    } catch (error) {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error de conexión',
            text: 'Error al actualizar la talla. Verifique su conexión.',
            confirmButtonText: 'Entendido'
        });
    }
}

// Funciones globales para onclick
window.editarTalla = function(id) {
    const talla = tallasData.find(t => t.talla_id == id);
    
    if (talla) {
        // Llenar el formulario de edición
        document.getElementById('edit_talla_id').value = talla.talla_id;
        document.getElementById('edit_talla_etiqueta').value = talla.talla_etiqueta;
        
        // Abrir modal de edición
        const modal = new bootstrap.Modal(document.getElementById('ModalEditarTalla'));
        modal.show();
    } else {
        Swal.fire('Error', 'No se pudo encontrar la talla', 'error');
    }
};

window.eliminarTalla = async function(id) {
    try {
        const confirmacion = await Swal.fire({
            title: '¿Estás seguro?',
            text: "Esta acción no se puede deshacer",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        });
        
        if (!confirmacion.isConfirmed) return;
        
        // Mostrar loading
        Swal.fire({
            title: 'Eliminando...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        const respuesta = await fetch(`/morataya_final_dotacion_ingsoft1/tallas/eliminarAPI?talla_id=${id}`);
        const resultado = await respuesta.json();
        
        if (resultado.resultado) {
            Swal.fire({
                icon: 'success',
                title: 'Eliminado',
                text: resultado.mensaje || 'Talla eliminada correctamente',
                timer: 1500,
                showConfirmButton: false
            });
            
            // Recargar tabla
            cargarTallas();
            
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: resultado.mensaje || 'Error al eliminar la talla',
                confirmButtonText: 'Entendido'
            });
        }
        
    } catch (error) {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error de conexión',
            text: 'Error al eliminar la talla. Verifique su conexión.',
            confirmButtonText: 'Entendido'
        });
    }
};

function validarFormatoTalla(etiqueta) {
    // Validar tallas de letra
    const tallasLetra = ['XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL'];
    if (tallasLetra.includes(etiqueta)) {
        return true;
    }
    
    // Validar tallas numéricas (1-60)
    if (/^\d+$/.test(etiqueta)) {
        const numero = parseInt(etiqueta);
        return numero >= 1 && numero <= 60;
    }
    
    return false;
}

function inicializarDataTable() {
    if (typeof $ !== 'undefined' && $.fn.DataTable) {
        tablaTallas = $('#TablaTallas').DataTable({
            responsive: true,
            pageLength: 25,
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
            },
            order: [[0, 'asc']] // Ordenar por etiqueta
        });
    }
}