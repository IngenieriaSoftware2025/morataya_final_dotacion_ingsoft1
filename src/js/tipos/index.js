import Swal from "sweetalert2";

let tablaTipos;
let modoEdicion = false;

document.addEventListener('DOMContentLoaded', function() {
    cargarTipos();
    configurarFormulario();
});

async function cargarTipos() {
    try {
        const respuesta = await fetch('/tipos/obtenerAPI');
        const tipos = await respuesta.json();
        
        if(tablaTipos) {
            tablaTipos.destroy();
        }
        
        mostrarTipos(tipos);
        inicializarDataTable();
    } catch (error) {
        console.error('Error al cargar tipos:', error);
        Swal.fire('Error', 'No se pudieron cargar los tipos de dotación', 'error');
    }
}

function mostrarTipos(tipos) {
    const tbody = document.querySelector('#TablaTipos tbody');
    tbody.innerHTML = '';
    
    tipos.forEach(tipo => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${tipo.tipo_nombre}</td>
            <td>${tipo.tipo_descripcion || '-'}</td>
            <td>
                <span class="badge bg-${tipo.tipo_situacion == 1 ? 'success' : 'danger'}">
                    ${tipo.tipo_situacion == 1 ? 'Activo' : 'Inactivo'}
                </span>
            </td>
            <td>
                <button class="btn btn-sm btn-outline-primary me-1" onclick="editarTipo(${tipo.tipo_id})">
                    <i class="bi bi-pencil"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger" onclick="eliminarTipo(${tipo.tipo_id})">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function configurarFormulario() {
    const formulario = document.getElementById('FormTipo');
    
    if(formulario) {
        formulario.addEventListener('submit', function(e) {
            e.preventDefault();
            guardarTipo();
        });
    }
    
    const modal = document.getElementById('ModalTipo');
    modal.addEventListener('hidden.bs.modal', function() {
        limpiarFormulario();
    });
}

function limpiarFormulario() {
    document.getElementById('FormTipo').reset();
    document.getElementById('tipo_id').value = '';
    document.getElementById('tituloModal').textContent = 'Nuevo Tipo de Dotación';
    modoEdicion = false;
}

async function guardarTipo() {
    try {
        Swal.fire({
            title: 'Guardando...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        const datos = new FormData(document.getElementById('FormTipo'));
        
        const respuesta = await fetch('/tipos/guardarAPI', {
            method: 'POST',
            body: datos
        });
        
        const resultado = await respuesta.json();
        
        if(resultado.resultado) {
            Swal.fire('Éxito', resultado.mensaje, 'success');
            bootstrap.Modal.getInstance(document.getElementById('ModalTipo')).hide();
            cargarTipos();
        } else {
            if(resultado.errores && Array.isArray(resultado.errores)) {
                Swal.fire('Error', resultado.errores.join('\n'), 'error');
            } else {
                Swal.fire('Error', resultado.mensaje, 'error');
            }
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire('Error', 'Error al guardar tipo', 'error');
    }
}

window.editarTipo = async function(id) {
    try {
        const respuesta = await fetch('/tipos/obtenerAPI');
        const tipos = await respuesta.json();
        const tipo = tipos.find(t => t.tipo_id == id);
        
        if(tipo) {
            document.getElementById('tipo_id').value = tipo.tipo_id;
            document.getElementById('tipo_nombre').value = tipo.tipo_nombre;
            document.getElementById('tipo_descripcion').value = tipo.tipo_descripcion || '';
            document.getElementById('tituloModal').textContent = 'Editar Tipo de Dotación';
            modoEdicion = true;
            
            const modal = new bootstrap.Modal(document.getElementById('ModalTipo'));
            modal.show();
        }
    } catch (error) {
        Swal.fire('Error', 'No se pudieron cargar los datos', 'error');
    }
};

window.eliminarTipo = async function(id) {
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
        
        const respuesta = await fetch(`/tipos/eliminarAPI?tipo_id=${id}`);
        const resultado = await respuesta.json();
        
        if(resultado.resultado) {
            Swal.fire('Eliminado', resultado.mensaje, 'success');
            cargarTipos();
        } else {
            Swal.fire('Error', resultado.mensaje, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire('Error', 'Error al eliminar tipo', 'error');
    }
};

function inicializarDataTable() {
    tablaTipos = $('#TablaTipos').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
        },
        responsive: true,
        pageLength: 25
    });
}