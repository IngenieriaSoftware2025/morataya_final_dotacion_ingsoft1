import Swal from "sweetalert2";

let tablaPersonal;
let modoEdicion = false;

document.addEventListener('DOMContentLoaded', function() {
    cargarPersonal();
    configurarFormulario();
});

async function cargarPersonal() {
    try {
        const respuesta = await fetch('/personal/obtenerAPI');
        const personal = await respuesta.json();
        
        if(tablaPersonal) {
            tablaPersonal.destroy();
        }
        
        mostrarPersonal(personal);
        inicializarDataTable();
    } catch (error) {
        console.error('Error al cargar personal:', error);
        Swal.fire('Error', 'No se pudo cargar el personal', 'error');
    }
}

function mostrarPersonal(personal) {
    const tbody = document.querySelector('#TablaPersonal tbody');
    tbody.innerHTML = '';
    
    personal.forEach(persona => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${persona.personal_nombre}</td>
            <td>${persona.personal_cui}</td>
            <td>${persona.personal_puesto || '-'}</td>
            <td>${persona.personal_fecha_ingreso}</td>
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

function configurarFormulario() {
    const formulario = document.getElementById('FormPersonal');
    
    if(formulario) {
        formulario.addEventListener('submit', function(e) {
            e.preventDefault();
            guardarPersonal();
        });
    }
    
    const modal = document.getElementById('ModalPersonal');
    modal.addEventListener('hidden.bs.modal', function() {
        limpiarFormulario();
    });
}

function limpiarFormulario() {
    document.getElementById('FormPersonal').reset();
    document.getElementById('personal_id').value = '';
    document.getElementById('personal_fecha_ingreso').value = new Date().toISOString().split('T')[0];
    document.getElementById('tituloModal').textContent = 'Nuevo Personal';
    modoEdicion = false;
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
        
        const respuesta = await fetch('/personal/guardarAPI', {
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

window.editarPersonal = async function(id) {
    try {
        const respuesta = await fetch('/personal/obtenerAPI');
        const personal = await respuesta.json();
        const persona = personal.find(p => p.personal_id == id);
        
        if(persona) {
            document.getElementById('personal_id').value = persona.personal_id;
            document.getElementById('personal_nombre').value = persona.personal_nombre;
            document.getElementById('personal_cui').value = persona.personal_cui;
            document.getElementById('personal_puesto').value = persona.personal_puesto;
            document.getElementById('personal_fecha_ingreso').value = persona.personal_fecha_ingreso;
            document.getElementById('tituloModal').textContent = 'Editar Personal';
            modoEdicion = true;
            
            const modal = new bootstrap.Modal(document.getElementById('ModalPersonal'));
            modal.show();
        }
    } catch (error) {
        Swal.fire('Error', 'No se pudieron cargar los datos', 'error');
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
        
        const respuesta = await fetch(`/personal/eliminarAPI?personal_id=${id}`);
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
    tablaPersonal = $('#TablaPersonal').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
        },
        responsive: true,
        pageLength: 25,
        order: [[0, 'asc']]
    });
}