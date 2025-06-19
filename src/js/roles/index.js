import Swal from "sweetalert2";

let tablaRoles;
let modoEdicion = false;

document.addEventListener('DOMContentLoaded', function() {
    cargarRoles();
    configurarFormulario();
});

async function cargarRoles() {
    try {
        const respuesta = await fetch('/roles/obtenerAPI');
        const roles = await respuesta.json();
        
        if(tablaRoles) {
            tablaRoles.destroy();
        }
        
        mostrarRoles(roles);
        inicializarDataTable();
    } catch (error) {
        console.error('Error al cargar roles:', error);
        Swal.fire('Error', 'No se pudieron cargar los roles', 'error');
    }
}

function mostrarRoles(roles) {
    const tbody = document.querySelector('#TablaRoles tbody');
    tbody.innerHTML = '';
    
    roles.forEach(rol => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${rol.rol_nombre}</td>
            <td>${rol.rol_nombre_ct || '-'}</td>
            <td>
                <span class="badge bg-${rol.rol_situacion == 1 ? 'success' : 'danger'}">
                    ${rol.rol_situacion == 1 ? 'Activo' : 'Inactivo'}
                </span>
            </td>
            <td>
                <button class="btn btn-sm btn-outline-primary me-1" onclick="editarRol(${rol.rol_id})">
                    <i class="bi bi-pencil"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger" onclick="eliminarRol(${rol.rol_id})">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function configurarFormulario() {
    const formulario = document.getElementById('FormRol');
    
    if(formulario) {
        formulario.addEventListener('submit', function(e) {
            e.preventDefault();
            guardarRol();
        });
    }
    
    const modal = document.getElementById('ModalRol');
    modal.addEventListener('hidden.bs.modal', function() {
        limpiarFormulario();
    });
}

function limpiarFormulario() {
    document.getElementById('FormRol').reset();
    document.getElementById('rol_id').value = '';
    document.getElementById('tituloModal').textContent = 'Nuevo Rol';
    modoEdicion = false;
}

async function guardarRol() {
    try {
        Swal.fire({
            title: 'Guardando...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        const datos = new FormData(document.getElementById('FormRol'));
        
        const respuesta = await fetch('/roles/guardarAPI', {
            method: 'POST',
            body: datos
        });
        
        const resultado = await respuesta.json();
        
        if(resultado.resultado) {
            Swal.fire('Éxito', resultado.mensaje, 'success');
            bootstrap.Modal.getInstance(document.getElementById('ModalRol')).hide();
            cargarRoles();
        } else {
            if(resultado.errores && Array.isArray(resultado.errores)) {
                Swal.fire('Error', resultado.errores.join('\n'), 'error');
            } else {
                Swal.fire('Error', resultado.mensaje, 'error');
            }
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire('Error', 'Error al guardar rol', 'error');
    }
}

window.editarRol = async function(id) {
    try {
        const respuesta = await fetch('/roles/obtenerAPI');
        const roles = await respuesta.json();
        const rol = roles.find(r => r.rol_id == id);
        
        if(rol) {
            document.getElementById('rol_id').value = rol.rol_id;
            document.getElementById('rol_nombre').value = rol.rol_nombre;
            document.getElementById('rol_nombre_ct').value = rol.rol_nombre_ct || '';
            document.getElementById('tituloModal').textContent = 'Editar Rol';
            modoEdicion = true;
            
            const modal = new bootstrap.Modal(document.getElementById('ModalRol'));
            modal.show();
        }
    } catch (error) {
        Swal.fire('Error', 'No se pudieron cargar los datos', 'error');
    }
};

window.eliminarRol = async function(id) {
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
        
        const respuesta = await fetch(`/roles/eliminarAPI?rol_id=${id}`);
        const resultado = await respuesta.json();
        
        if(resultado.resultado) {
            Swal.fire('Eliminado', resultado.mensaje, 'success');
            cargarRoles();
        } else {
            Swal.fire('Error', resultado.mensaje, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire('Error', 'Error al eliminar rol', 'error');
    }
};

function inicializarDataTable() {
    tablaRoles = $('#TablaRoles').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
        },
        responsive: true,
        pageLength: 25
    });
}