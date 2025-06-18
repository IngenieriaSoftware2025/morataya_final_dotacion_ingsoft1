import Swal from "sweetalert2";

let tablaUsuarios;
let modoCrear = false;

document.addEventListener('DOMContentLoaded', function() {
    const urlPath = window.location.pathname;
    
    if(urlPath.includes('/crear')) {
        modoCrear = true;
        cargarRoles();
        configurarFormularioCrear();
    } else {
        cargarUsuarios();
    }
});

// FUNCIONES PARA LISTADO
async function cargarUsuarios() {
    try {
        const respuesta = await fetch('/usuarios/obtenerAPI');
        const usuarios = await respuesta.json();
        
        if(tablaUsuarios) {
            tablaUsuarios.destroy();
        }
        
        mostrarUsuarios(usuarios);
        inicializarDataTable();
    } catch (error) {
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
                    `<img src="/storage/fotosUsuarios/${usuario.usu_fotografia}" class="rounded-circle" width="40" height="40" alt="Foto">` :
                    `<i class="bi bi-person-circle fs-2 text-muted"></i>`
                }
            </td>
            <td>${usuario.usu_nombre}</td>
            <td>${usuario.usu_codigo}</td>
            <td>${usuario.usu_correo || '-'}</td>
            <td>
                <span class="badge bg-${usuario.usu_situacion == 1 ? 'success' : 'danger'}">
                    ${usuario.usu_situacion == 1 ? 'Activo' : 'Inactivo'}
                </span>
            </td>
            <td>
                <button class="btn btn-sm btn-outline-primary" onclick="editarUsuario(${usuario.usu_id})">
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

function inicializarDataTable() {
    tablaUsuarios = $('#TablaUsuarios').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
        },
        responsive: true,
        pageLength: 25
    });
}

window.editarUsuario = function(id) {
    window.location.href = `/usuarios/editar?id=${id}`;
};

window.eliminarUsuario = async function(id) {
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

        const respuesta = await fetch(`/usuarios/eliminarAPI?usu_id=${id}`);
        const resultado = await respuesta.json();
        
        if(resultado.resultado) {
            Swal.fire('Eliminado', resultado.mensaje, 'success');
            cargarUsuarios();
        } else {
            Swal.fire('Error', resultado.mensaje, 'error');
        }
    } catch (error) {
        Swal.fire('Error', 'No se pudo eliminar el usuario', 'error');
    }
};

// FUNCIONES PARA CREAR
async function cargarRoles() {
    try {
        const respuesta = await fetch('/roles/obtenerAPI');
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
        Swal.fire('Error', 'No se pudieron cargar los roles', 'error');
    }
}

function configurarFormularioCrear() {
    const formulario = document.getElementById('FormUsuario');
    
    if(formulario) {
        formulario.addEventListener('submit', function(e) {
            e.preventDefault();
            guardarUsuario();
        });
    }
}

async function guardarUsuario() {
    if(!validarFormulario()) return;
    
    try {
        Swal.fire({
            title: 'Guardando...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        const datos = new FormData(document.getElementById('FormUsuario'));
        
        const respuesta = await fetch('/usuarios/guardarAPI', {
            method: 'POST',
            body: datos
        });
        
        const resultado = await respuesta.json();
        
        if(resultado.resultado) {
            Swal.fire('Éxito', resultado.mensaje, 'success').then(() => {
                window.location.href = '/usuarios';
            });
        } else {
            Swal.fire('Error', resultado.mensaje, 'error');
        }
    } catch (error) {
        Swal.fire('Error', 'Error al guardar usuario', 'error');
    }
}

function validarFormulario() {
    const nombre = document.querySelector('input[name="usu_nombre"]').value.trim();
    const codigo = document.querySelector('input[name="usu_codigo"]').value;
    const password = document.querySelector('input[name="usu_password"]').value;
    
    if(!nombre) {
        Swal.fire('Error', 'El nombre es obligatorio', 'error');
        return false;
    }
    
    if(!codigo || codigo < 1000 || codigo > 999999) {
        Swal.fire('Error', 'El código debe ser entre 1000 y 999999', 'error');
        return false;
    }
    
    if(!password || password.length < 6) {
        Swal.fire('Error', 'La contraseña debe tener al menos 6 caracteres', 'error');
        return false;
    }
    
    return true;
}
