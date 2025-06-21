import Swal from "sweetalert2";
import DataTable from "datatables.net-bs5";
import { lenguaje } from "../lenguaje";

let tablaUsuarios = null;
let modoEdicion = false;

window.editarUsuario = function(id) {
    editarUsuario(id);
};

window.eliminarUsuario = function(id) {
    eliminarUsuario(id);
};

window.limpiarFormulario = function() {
    limpiarFormulario();
};

window.nuevoUsuario = function() {
    nuevoUsuario();
};

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded - inicializando módulo usuarios');
    inicializar();
});

function inicializar() {
    try {
        cargarUsuarios();
        cargarRoles();
        configurarFormulario();
        console.log('Módulo usuarios inicializado correctamente');
    } catch (error) {
        console.error('Error al inicializar:', error);
    }
}

async function cargarUsuarios() {
    try {
        console.log('Cargando usuarios...');
        const respuesta = await fetch('/morataya_final_dotacion_ingsoft1/usuarios/obtenerAPI');
        
        if (!respuesta.ok) {
            throw new Error(`HTTP error! status: ${respuesta.status}`);
        }
        
        const usuarios = await respuesta.json();
        console.log('Usuarios cargados:', usuarios.length);
        
        if (tablaUsuarios) {
            try {
                tablaUsuarios.destroy();
                tablaUsuarios = null;
            } catch (e) {
                console.warn('Error al destruir tabla:', e);
            }
        }
        
        mostrarUsuarios(usuarios);
        inicializarDataTable();
    } catch (error) {
        console.error('Error al cargar usuarios:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se pudieron cargar los usuarios: ' + error.message
        });
    }
}

function mostrarUsuarios(usuarios) {
    const tbody = document.querySelector('#TablaUsuarios tbody');
    if (!tbody) {
        console.error('No se encontró el tbody de la tabla');
        return;
    }
    
    tbody.innerHTML = '';
    
    if (!Array.isArray(usuarios)) {
        console.warn('Los datos no son un array válido:', usuarios);
        return;
    }
    
    usuarios.forEach(usuario => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>
                ${usuario.usu_fotografia ? 
                    `<img src="/morataya_final_dotacion_ingsoft1/storage/fotosUsuarios/${usuario.usu_fotografia}" 
                          class="rounded-circle" width="40" height="40" alt="Foto" 
                          onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
                     <i class="bi bi-person-circle fs-2 text-muted" style="display:none;"></i>` :
                    `<i class="bi bi-person-circle fs-2 text-muted"></i>`
                }
            </td>
            <td>${escapeHtml(usuario.usu_nombre || '')}</td>
            <td>${escapeHtml(usuario.usu_codigo || '')}</td>
            <td>${escapeHtml(usuario.usu_correo || '-')}</td>
            <td><small class="text-muted">${escapeHtml(usuario.roles_nombres || 'Sin roles')}</small></td>
            <td>
                <span class="badge bg-${usuario.usu_situacion == 1 ? 'success' : 'danger'}">
                    ${usuario.usu_situacion == 1 ? 'Activo' : 'Inactivo'}
                </span>
            </td>
            <td>
                <button class="btn btn-sm btn-outline-primary me-1" onclick="editarUsuario(${usuario.usu_id})" title="Editar">
                    <i class="bi bi-pencil"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger" onclick="eliminarUsuario(${usuario.usu_id})" title="Eliminar">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
}

async function cargarRoles() {
    try {
        const respuesta = await fetch('/morataya_final_dotacion_ingsoft1/roles/obtenerAPI');
        
        if (!respuesta.ok) {
            throw new Error(`HTTP error! status: ${respuesta.status}`);
        }
        
        const roles = await respuesta.json();
        
        const container = document.getElementById('RolesContainer');
        if(!container) {
            console.warn('Container de roles no encontrado');
            return;
        }
        
        container.innerHTML = '';
        
        if (!Array.isArray(roles)) {
            console.warn('Los roles no son un array válido:', roles);
            return;
        }
        
        roles.forEach(rol => {
            const div = document.createElement('div');
            div.className = 'form-check';
            div.innerHTML = `
                <input class="form-check-input" type="checkbox" name="roles[]" 
                       value="${rol.rol_id}" id="rol_${rol.rol_id}">
                <label class="form-check-label" for="rol_${rol.rol_id}">
                    ${escapeHtml(rol.rol_nombre)}
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
        formulario.removeEventListener('submit', manejarSubmit);
        formulario.addEventListener('submit', manejarSubmit);
    }
    
    const inputFoto = document.getElementById('usu_fotografia');
    if(inputFoto) {
        inputFoto.removeEventListener('change', manejarCambioFoto);
        inputFoto.addEventListener('change', manejarCambioFoto);
    }
    
    const modal = document.getElementById('ModalUsuario');
    if(modal) {
        modal.removeEventListener('hidden.bs.modal', limpiarFormulario);
        modal.addEventListener('hidden.bs.modal', limpiarFormulario);
    }
    
    const btnNuevoUsuario = document.querySelector('[data-bs-target="#ModalUsuario"]');
    if(btnNuevoUsuario) {
        btnNuevoUsuario.removeEventListener('click', nuevoUsuario);
        btnNuevoUsuario.addEventListener('click', nuevoUsuario);
    }
}

function manejarSubmit(e) {
    e.preventDefault();
    guardarUsuario();
}

function manejarCambioFoto(e) {
    previewFotografia(e.target.files[0]);
}

function limpiarFormulario() {
    try {
        const form = document.getElementById('FormUsuario');
        if(form) form.reset();
        
        const campos = [
            { id: 'usu_id', valor: '' },
            { id: 'tituloModal', texto: 'Nuevo Usuario' },
            { id: 'passwordHelp', texto: 'Mínimo 6 caracteres' }
        ];
        
        campos.forEach(campo => {
            const elemento = document.getElementById(campo.id);
            if(elemento) {
                if(campo.valor !== undefined) elemento.value = campo.valor;
                if(campo.texto !== undefined) elemento.textContent = campo.texto;
            }
        });
        
        const passwordRequired = document.getElementById('passwordRequired');
        if(passwordRequired) passwordRequired.style.display = 'inline';
        
        const password = document.getElementById('usu_password');
        if(password) password.required = true;
        
        const preview = document.getElementById('previewFoto');
        if(preview) preview.innerHTML = '';
        
        const checkboxes = document.querySelectorAll('input[name="roles[]"]');
        checkboxes.forEach(cb => cb.checked = false);
        
        modoEdicion = false;
    } catch (error) {
        console.error('Error al limpiar formulario:', error);
    }
}

function previewFotografia(file) {
    const preview = document.getElementById('previewFoto');
    
    if(file && preview) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `<img src="${e.target.result}" class="img-thumbnail" style="max-width: 100px; max-height: 100px;" alt="Preview">`;
        };
        reader.readAsDataURL(file);
    } else if(preview) {
        preview.innerHTML = '';
    }
}

async function guardarUsuario() {
    try {
        Swal.fire({
            title: 'Guardando...',
            text: 'Por favor espere',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        const form = document.getElementById('FormUsuario');
        if (!form) {
            throw new Error('Formulario no encontrado');
        }
        
        const datos = new FormData(form);
        
        const respuesta = await fetch('/morataya_final_dotacion_ingsoft1/usuarios/guardarAPI', {
            method: 'POST',
            body: datos
        });
        
        if (!respuesta.ok) {
            throw new Error(`HTTP error! status: ${respuesta.status}`);
        }
        
        const resultado = await respuesta.json();
        
        if(resultado.resultado) {
            await Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: resultado.mensaje
            });
            
            cerrarModal();
            await cargarUsuarios();
        } else {
            const mensajeError = resultado.errores && Array.isArray(resultado.errores) 
                ? resultado.errores.join('\n') 
                : resultado.mensaje || 'Error desconocido';
                
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: mensajeError
            });
        }
    } catch (error) {
        console.error('Error al guardar:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error al guardar usuario: ' + error.message
        });
    }
}

function cerrarModal() {
    const modal = document.getElementById('ModalUsuario');
    if (!modal) return;
    
    try {
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const bsModal = bootstrap.Modal.getInstance(modal);
            if (bsModal) {
                bsModal.hide();
            } else {
                const newModal = new bootstrap.Modal(modal);
                newModal.hide();
            }
        } 
        else if (typeof $ !== 'undefined' && $.fn.modal) {
            $(modal).modal('hide');
        } 
        else {
            console.log('Cerrando modal manualmente');
            
            modal.style.display = 'none';
            modal.classList.remove('show');
            modal.removeAttribute('aria-modal');
            modal.removeAttribute('role');
            
            const backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) {
                backdrop.remove();
            }
            
            document.body.classList.remove('modal-open');
            
            limpiarFormulario();
        }
    } catch (error) {
        console.error('Error al cerrar modal:', error);
        
        modal.style.display = 'none';
        modal.classList.remove('show');
        document.body.classList.remove('modal-open');
        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) backdrop.remove();
    }
}

function inicializarDataTable() {
    const tabla = document.getElementById('TablaUsuarios');
    if (!tabla) {
        console.error('Tabla no encontrada');
        return;
    }
    
    try {
        tablaUsuarios = new DataTable('#TablaUsuarios', {
            language: lenguaje,
            responsive: true,
            pageLength: 10,
            order: [[1, 'asc']],
            columnDefs: [
                { orderable: false, targets: [0, 6] }
            ],
            destroy: true
        });
        console.log('DataTable inicializada correctamente');
    } catch (error) {
        console.error('Error al inicializar DataTable:', error);
    }
}

async function editarUsuario(id) {
    try {
        if (!id) {
            throw new Error('ID de usuario no proporcionado');
        }
        
        const respuesta = await fetch(`/morataya_final_dotacion_ingsoft1/usuarios/obtenerPorIdAPI?id=${id}`);
        
        if (!respuesta.ok) {
            throw new Error(`HTTP error! status: ${respuesta.status}`);
        }
        
        const data = await respuesta.json();
        
        if(data.resultado && data.usuario) {
            const usuario = data.usuario;
            
            const elementos = ['usu_id', 'usu_nombre', 'usu_codigo', 'usu_correo', 'usu_password'];
            const elementosExisten = elementos.every(id => {
                const elemento = document.getElementById(id);
                if (!elemento) {
                    console.error(`Elemento ${id} no encontrado`);
                    return false;
                }
                return true;
            });
            
            if (!elementosExisten) {
                throw new Error('Algunos elementos del formulario no se encontraron');
            }
            
            document.getElementById('usu_id').value = usuario.usu_id;
            document.getElementById('usu_nombre').value = usuario.usu_nombre;
            document.getElementById('usu_codigo').value = usuario.usu_codigo;
            document.getElementById('usu_correo').value = usuario.usu_correo || '';
            document.getElementById('usu_password').value = '';
            
            document.getElementById('tituloModal').textContent = 'Editar Usuario';
            document.getElementById('passwordRequired').style.display = 'none';
            document.getElementById('usu_password').required = false;
            document.getElementById('passwordHelp').textContent = 'Dejar vacío para mantener la contraseña actual';
            
            const checkboxes = document.querySelectorAll('input[name="roles[]"]');
            checkboxes.forEach(cb => cb.checked = false);
            
            if(usuario.roles && Array.isArray(usuario.roles)) {
                usuario.roles.forEach(rolId => {
                    const checkbox = document.getElementById(`rol_${rolId}`);
                    if(checkbox) {
                        checkbox.checked = true;
                    }
                });
            }
            
            const preview = document.getElementById('previewFoto');
            if(usuario.usu_fotografia && preview) {
                preview.innerHTML = 
                    `<img src="/morataya_final_dotacion_ingsoft1/storage/fotosUsuarios/${usuario.usu_fotografia}" class="img-thumbnail" style="max-width: 100px; max-height: 100px;" alt="Foto actual">`;
            }
            
            modoEdicion = true;
            
            const modal = document.getElementById('ModalUsuario');
            
            if (!modal) {
                throw new Error('Modal no encontrado en el DOM');
            }
            
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                const bsModal = new bootstrap.Modal(modal);
                bsModal.show();
            } 
            else if (typeof $ !== 'undefined' && $.fn.modal) {
                $(modal).modal('show');
            } 
            else {
                const backdrop = document.createElement('div');
                backdrop.className = 'modal-backdrop fade show';
                document.body.appendChild(backdrop);
                
                modal.style.display = 'block';
                modal.classList.add('show');
                modal.setAttribute('aria-modal', 'true');
                modal.setAttribute('role', 'dialog');
                document.body.classList.add('modal-open');
                
                const cerrarConEsc = (e) => {
                    if (e.key === 'Escape') {
                        cerrarModal();
                        document.removeEventListener('keydown', cerrarConEsc);
                    }
                };
                document.addEventListener('keydown', cerrarConEsc);
                
                backdrop.addEventListener('click', cerrarModal);
                
                const btnCerrar = modal.querySelector('[data-bs-dismiss="modal"], .btn-close');
                if (btnCerrar) {
                    btnCerrar.addEventListener('click', cerrarModal);
                }
            }
        } else {
            throw new Error(data.mensaje || 'Usuario no encontrado');
        }
    } catch (error) {
        console.error('Error al editar usuario:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error al cargar usuario: ' + error.message
        });
    }
}

async function eliminarUsuario(id) {
    try {
        if (!id) {
            throw new Error('ID de usuario no proporcionado');
        }
        
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
            Swal.fire({
                title: 'Eliminando...',
                text: 'Por favor espere',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            const respuesta = await fetch(`/morataya_final_dotacion_ingsoft1/usuarios/eliminarAPI?usu_id=${id}`);
            
            if (!respuesta.ok) {
                throw new Error(`HTTP error! status: ${respuesta.status}`);
            }
            
            const data = await respuesta.json();
            
            if(data.resultado) {
                await Swal.fire({
                    icon: 'success',
                    title: 'Eliminado',
                    text: data.mensaje
                });
                
                await cargarUsuarios();
            } else {
                throw new Error(data.mensaje || 'Error al eliminar usuario');
            }
        }
    } catch (error) {
        console.error('Error al eliminar usuario:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error al eliminar usuario: ' + error.message
        });
    }
}

function nuevoUsuario() {
    try {
        limpiarFormulario();
        
        modoEdicion = false;
        
        document.getElementById('tituloModal').textContent = 'Nuevo Usuario';
        document.getElementById('passwordRequired').style.display = 'inline';
        document.getElementById('usu_password').required = true;
        document.getElementById('passwordHelp').textContent = 'Mínimo 6 caracteres';
        
        const preview = document.getElementById('previewFoto');
        if(preview) preview.innerHTML = '';
        
        const checkboxes = document.querySelectorAll('input[name="roles[]"]');
        checkboxes.forEach(cb => cb.checked = false);
        
        const modal = document.getElementById('ModalUsuario');
        if (!modal) {
            throw new Error('Modal no encontrado');
        }
        
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
        } 
        else if (typeof $ !== 'undefined' && $.fn.modal) {
            $(modal).modal('show');
        } 
        else {
            const backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            document.body.appendChild(backdrop);
            
            modal.style.display = 'block';
            modal.classList.add('show');
            modal.setAttribute('aria-modal', 'true');
            modal.setAttribute('role', 'dialog');
            document.body.classList.add('modal-open');
            
            const cerrarConEsc = (e) => {
                if (e.key === 'Escape') {
                    cerrarModal();
                    document.removeEventListener('keydown', cerrarConEsc);
                }
            };
            document.addEventListener('keydown', cerrarConEsc);
            
            backdrop.addEventListener('click', cerrarModal);
            
            const btnCerrar = modal.querySelector('[data-bs-dismiss="modal"], .btn-close');
            if (btnCerrar) {
                btnCerrar.addEventListener('click', cerrarModal);
            }
        }
        
    } catch (error) {
        console.error('Error al abrir modal nuevo usuario:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error al abrir formulario de nuevo usuario: ' + error.message
        });
    }
}