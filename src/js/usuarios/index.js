import Swal from "sweetalert2";

let tablaUsuarios;
let modoEdicion = false;

document.addEventListener('DOMContentLoaded', function() {
    cargarUsuarios();
    cargarRoles();
    configurarFormulario();
});

async function cargarUsuarios() {
    try {
        const respuesta = await fetch('/morataya_final_dotacion_ingsoft1/usuarios/obtenerAPI');
        const usuarios = await respuesta.json();
        
        if(tablaUsuarios) {
            tablaUsuarios.destroy();
        }
        
        mostrarUsuarios(usuarios);
        inicializarDataTable();
    } catch (error) {
        console.error('Error al cargar usuarios:', error);
        Swal.fire('Error', 'No se pudieron cargar los usuarios', 'error');
    }
}

function mostrarUsuarios(usuarios) {
    const tbody = document.querySelector('#TablaUsuarios tbody');
    tbody.innerHTML = '';
    
    usuarios.forEach(usuario => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>
                ${usuario.usu_fotografia ? 
                    `<img src="/morataya_final_dotacion_ingsoft1/storage/fotosUsuarios/${usuario.usu_fotografia}" class="rounded-circle" width="40" height="40" alt="Foto">` :
                    `<i class="bi bi-person-circle fs-2 text-muted"></i>`
                }
            </td>
            <td>${usuario.usu_nombre}</td>
            <td>${usuario.usu_codigo}</td>
            <td>${usuario.usu_correo || '-'}</td>
            <td><small class="text-muted">${usuario.roles_nombres || 'Sin roles'}</small></td>
            <td>
                <span class="badge bg-${usuario.usu_situacion == 1 ? 'success' : 'danger'}">
                    ${usuario.usu_situacion == 1 ? 'Activo' : 'Inactivo'}
                </span>
            </td>
            <td>
                <button class="btn btn-sm btn-outline-primary me-1" onclick="editarUsuario(${usuario.usu_id})">
                    <i class="bi bi-pencil"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger" onclick="eliminarUsuario(${usuario.usu_id})">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

async function cargarRoles() {
    try {
        const respuesta = await fetch('/morataya_final_dotacion_ingsoft1/roles/obtenerAPI');
        const roles = await respuesta.json();
        
        const container = document.getElementById('RolesContainer');
        if(!container) return;
        
        container.innerHTML = '';
        
        roles.forEach(rol => {
            const div = document.createElement('div');
            div.className = 'form-check';
            div.innerHTML = `
                <input class="form-check-input" type="checkbox" name="roles[]" 
                       value="${rol.rol_id}" id="rol_${rol.rol_id}">
                <label class="form-check-label" for="rol_${rol.rol_id}">
                    ${rol.rol_nombre}
                </label>
            `;
            container.appendChild(div);
        });
    } catch (error) {
        console.error('Error al cargar roles:', error);
    }
}

function configurarFormulario() {
    const formulario = document.getElementById('FormUsuario');
    
    if(formulario) {
        formulario.addEventListener('submit', function(e) {
            e.preventDefault();
            guardarUsuario();
        });
    }
    
    const inputFoto = document.getElementById('usu_fotografia');
    if(inputFoto) {
        inputFoto.addEventListener('change', function(e) {
            previewFotografia(e.target.files[0]);
        });
    }
    
    const modal = document.getElementById('ModalUsuario');
    if(modal) {
        modal.addEventListener('hidden.bs.modal', function() {
            limpiarFormulario();
        });
    }
}

function limpiarFormulario() {
    document.getElementById('FormUsuario').reset();
    document.getElementById('usu_id').value = '';
    document.getElementById('tituloModal').textContent = 'Nuevo Usuario';
    document.getElementById('passwordRequired').style.display = 'inline';
    document.getElementById('usu_password').required = true;
    document.getElementById('passwordHelp').textContent = 'Mínimo 6 caracteres';
    document.getElementById('previewFoto').innerHTML = '';
    
    const checkboxes = document.querySelectorAll('input[name="roles[]"]');
    checkboxes.forEach(cb => cb.checked = false);
    
    modoEdicion = false;
}

function previewFotografia(file) {
    const preview = document.getElementById('previewFoto');
    
    if(file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `<img src="${e.target.result}" class="img-thumbnail" style="max-width: 100px; max-height: 100px;">`;
        };
        reader.readAsDataURL(file);
    } else {
        preview.innerHTML = '';
    }
}

async function guardarUsuario() {
    try {
        Swal.fire({
            title: 'Guardando...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        const datos = new FormData(document.getElementById('FormUsuario'));
        
        const respuesta = await fetch('/morataya_final_dotacion_ingsoft1/usuarios/guardarAPI', {
            method: 'POST',
            body: datos
        });
        
        const resultado = await respuesta.json();
        
        if(resultado.resultado) {
            Swal.fire('Éxito', resultado.mensaje, 'success');
            bootstrap.Modal.getInstance(document.getElementById('ModalUsuario')).hide();
            cargarUsuarios();
        } else {
            if(resultado.errores && Array.isArray(resultado.errores)) {
                Swal.fire('Error', resultado.errores.join('\n'), 'error');
            } else {
                Swal.fire('Error', resultado.mensaje, 'error');
            }
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire('Error', 'Error al guardar usuario', 'error');
    }
}

function inicializarDataTable() {
    if ($.fn.DataTable.isDataTable('#TablaUsuarios')) {
        $('#TablaUsuarios').DataTable().destroy();
    }
    
    tablaUsuarios = $('#TablaUsuarios').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
        },
        responsive: true,
        pageLength: 10,
        order: [[1, 'asc']]
    });
}

window.editarUsuario = async function(id) {
    try {
        const respuesta = await fetch(`/morataya_final_dotacion_ingsoft1/usuarios/obtenerPorIdAPI?id=${id}`);
        const data = await respuesta.json();
        
        if(data.resultado && data.usuario) {
            const usuario = data.usuario;
            
            document.getElementById('usu_id').value = usuario.usu_id;
            document.getElementById('usu_nombre').value = usuario.usu_nombre;
            document.getElementById('usu_codigo').value = usuario.usu_codigo;
            document.getElementById('usu_correo').value = usuario.usu_correo || '';
            document.getElementById('usu_password').value = '';
            document.getElementById('tituloModal').textContent = 'Editar Usuario';
            document.getElementById('passwordRequired').style.display = 'none';
            document.getElementById('usu_password').required = false;
            document.getElementById('passwordHelp').textContent = 'Dejar vacío para mantener la contraseña actual';
            
            if(data.roles && Array.isArray(data.roles)) {
                data.roles.forEach(rolId => {
                    const checkbox = document.getElementById(`rol_${rolId}`);
                    if(checkbox) checkbox.checked = true;
                });
            }
            
            if(usuario.usu_fotografia) {
                document.getElementById('previewFoto').innerHTML = 
                    `<img src="/morataya_final_dotacion_ingsoft1/storage/fotosUsuarios/${usuario.usu_fotografia}" class="img-thumbnail" style="max-width: 100px; max-height: 100px;">`;
            }
            
            modoEdicion = true;
            
            const modal = new bootstrap.Modal(document.getElementById('ModalUsuario'));
            modal.show();
        } else {
            Swal.fire('Error', data.mensaje || 'Usuario no encontrado', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire('Error', 'Error al cargar usuario', 'error');
    }
}

window.eliminarUsuario = async function(id) {
    try {
        const resultado = await Swal.fire({
            title: '¿Estás seguro?',
            text: 'Esta acción no se puede deshacer',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        });
        
        if(resultado.isConfirmed) {
            const respuesta = await fetch(`/morataya_final_dotacion_ingsoft1/usuarios/eliminarAPI?id=${id}`);
            const data = await respuesta.json();
            
            if(data.resultado) {
                Swal.fire('Eliminado', data.mensaje, 'success');
                cargarUsuarios();
            } else {
                Swal.fire('Error', data.mensaje, 'error');
            }
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire('Error', 'Error al eliminar usuario', 'error');
    }
}