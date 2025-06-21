import Swal from "sweetalert2";

let tablaPersonal;

// Función para esperar a que jQuery esté disponible
function esperarJQuery(callback) {
    if (typeof $ !== 'undefined' && $.fn.DataTable) {
        callback();
    } else {
        setTimeout(() => esperarJQuery(callback), 100);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    cargarPersonal();
    configurarFormularios();
});

async function cargarPersonal() {
    try {
        const respuesta = await fetch('/morataya_final_dotacion_ingsoft1/personal/obtenerAPI');
        const personal = await respuesta.json();
        
        // Destruir tabla existente si existe
        if(tablaPersonal && typeof $ !== 'undefined' && $.fn.DataTable && $.fn.DataTable.isDataTable('#TablaPersonal')) {
            tablaPersonal.destroy();
        }
        
        mostrarPersonal(personal);
        
        // Esperar a que jQuery esté disponible antes de inicializar DataTable
        esperarJQuery(() => {
            setTimeout(() => {
                inicializarDataTable();
            }, 200);
        });
        
    } catch (error) {
        console.error('Error al cargar personal:', error);
        Swal.fire('Error', 'No se pudo cargar el personal', 'error');
    }
}

function mostrarPersonal(personal) {
    const tbody = document.querySelector('#TablaPersonal tbody');
    if (!tbody) {
        console.error('No se encontró el tbody de la tabla');
        return;
    }
    
    tbody.innerHTML = '';
    
    if (!Array.isArray(personal)) {
        console.error('Los datos del personal no son un array:', personal);
        return;
    }
    
    personal.forEach(persona => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${persona.personal_nombre || ''}</td>
            <td>${persona.personal_cui || ''}</td>
            <td>${persona.personal_puesto || '-'}</td>
            <td>${persona.personal_fecha_ingreso || ''}</td>
            <td>
                <span class="badge bg-${persona.personal_situacion == 1 ? 'success' : 'danger'}">
                    ${persona.personal_situacion == 1 ? 'Activo' : 'Inactivo'}
                </span>
            </td>
            <td>
                <button class="btn btn-sm btn-outline-primary me-1" onclick="editarPersonal(${persona.personal_id})">
                    <i class="bi bi-pencil"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger" onclick="eliminarPersonal(${persona.personal_id})">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function configurarFormularios() {
    // Formulario crear
    const formCrear = document.getElementById('FormPersonal');
    if(formCrear) {
        formCrear.addEventListener('submit', function(e) {
            e.preventDefault();
            guardarPersonal();
        });
    }
    
    // Formulario editar
    const formEditar = document.getElementById('FormEditarPersonal');
    if(formEditar) {
        formEditar.addEventListener('submit', function(e) {
            e.preventDefault();
            actualizarPersonal();
        });
    }
    
    // Limpiar formularios al cerrar modales
    const modalCrear = document.getElementById('ModalPersonal');
    if(modalCrear) {
        modalCrear.addEventListener('hidden.bs.modal', function() {
            limpiarFormulario('FormPersonal');
        });
    }
    
    const modalEditar = document.getElementById('ModalEditarPersonal');
    if(modalEditar) {
        modalEditar.addEventListener('hidden.bs.modal', function() {
            limpiarFormulario('FormEditarPersonal');
        });
    }
    
    // Validación CUI en tiempo real
    configurarValidacionCUI();
    
    // Establecer fecha actual en nuevo personal
    const fechaInput = document.getElementById('personal_fecha_ingreso');
    if(fechaInput) {
        fechaInput.value = new Date().toISOString().split('T')[0];
    }
}

function configurarValidacionCUI() {
    const cuiInputs = ['personal_cui', 'edit_personal_cui'];
    
    cuiInputs.forEach(inputId => {
        const input = document.getElementById(inputId);
        if(input) {
            input.addEventListener('input', function() {
                this.value = this.value.replace(/\D/g, '').substring(0, 13);
            });
        }
    });
}

function limpiarFormulario(formId) {
    const form = document.getElementById(formId);
    if(form) {
        form.reset();
        if(formId === 'FormPersonal') {
            document.getElementById('personal_fecha_ingreso').value = new Date().toISOString().split('T')[0];
            document.getElementById('tituloModal').textContent = 'Nuevo Personal';
        }
    }
}

async function guardarPersonal() {
    try {
        Swal.fire({
            title: 'Guardando...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        const datos = new FormData(document.getElementById('FormPersonal'));
        
        const respuesta = await fetch('/morataya_final_dotacion_ingsoft1/personal/guardarAPI', {
            method: 'POST',
            body: datos
        });
        
        const resultado = await respuesta.json();
        
        if(resultado.resultado) {
            Swal.fire('Éxito', resultado.mensaje, 'success');
            bootstrap.Modal.getInstance(document.getElementById('ModalPersonal')).hide();
            cargarPersonal();
        } else {
            if(resultado.errores && Array.isArray(resultado.errores)) {
                Swal.fire('Error', resultado.errores.join('\n'), 'error');
            } else {
                Swal.fire('Error', resultado.mensaje, 'error');
            }
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire('Error', 'Error al guardar personal', 'error');
    }
}

async function actualizarPersonal() {
    try {
        Swal.fire({
            title: 'Actualizando...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        const datos = new FormData(document.getElementById('FormEditarPersonal'));
        
        const respuesta = await fetch('/morataya_final_dotacion_ingsoft1/personal/guardarAPI', {
            method: 'POST',
            body: datos
        });
        
        const resultado = await respuesta.json();
        
        if(resultado.resultado) {
            Swal.fire('Éxito', resultado.mensaje, 'success');
            bootstrap.Modal.getInstance(document.getElementById('ModalEditarPersonal')).hide();
            cargarPersonal();
        } else {
            if(resultado.errores && Array.isArray(resultado.errores)) {
                Swal.fire('Error', resultado.errores.join('\n'), 'error');
            } else {
                Swal.fire('Error', resultado.mensaje, 'error');
            }
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire('Error', 'Error al actualizar personal', 'error');
    }
}

window.editarPersonal = async function(id) {
    try {
        const respuesta = await fetch(`/morataya_final_dotacion_ingsoft1/personal/obtenerPorIdAPI?personal_id=${id}`);
        const resultado = await respuesta.json();
        
        if(resultado.resultado && resultado.personal) {
            const persona = resultado.personal;
            
            document.getElementById('edit_personal_id').value = persona.personal_id;
            document.getElementById('edit_personal_nombre').value = persona.personal_nombre;
            document.getElementById('edit_personal_cui').value = persona.personal_cui;
            document.getElementById('edit_personal_puesto').value = persona.personal_puesto;
            document.getElementById('edit_personal_fecha_ingreso').value = persona.personal_fecha_ingreso;
            
            const modal = new bootstrap.Modal(document.getElementById('ModalEditarPersonal'));
            modal.show();
        } else {
            Swal.fire('Error', 'No se pudieron cargar los datos', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire('Error', 'Error al cargar datos para edición', 'error');
    }
};

window.eliminarPersonal = async function(id) {
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
        
        const respuesta = await fetch(`/morataya_final_dotacion_ingsoft1/personal/eliminarAPI?personal_id=${id}`);
        const resultado = await respuesta.json();
        
        if(resultado.resultado) {
            Swal.fire('Eliminado', resultado.mensaje, 'success');
            cargarPersonal();
        } else {
            Swal.fire('Error', resultado.mensaje, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire('Error', 'Error al eliminar personal', 'error');
    }
};

function inicializarDataTable() {
    try {
        // Verificar que jQuery y DataTable estén disponibles
        if (typeof $ === 'undefined') {
            console.error('jQuery no está disponible');
            return;
        }
        
        if (!$.fn.DataTable) {
            console.error('DataTable no está disponible');
            return;
        }
        
        // Verificar que la tabla existe
        const tabla = $('#TablaPersonal');
        if (tabla.length === 0) {
            console.error('No se encontró la tabla #TablaPersonal');
            return;
        }
        
        tablaPersonal = tabla.DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
            },
            responsive: true,
            pageLength: 25,
            order: [[0, 'asc']],
            destroy: true // Permite recrear la tabla si ya existe
        });
        
        console.log('DataTable inicializada correctamente');
        
    } catch (error) {
        console.error('Error al inicializar DataTable:', error);
        // No mostrar SweetAlert aquí para evitar interferir con otras operaciones
    }
}