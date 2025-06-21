let tipos = [];
let editMode = false;
let editId = null;
const BASE = '/morataya_final_dotacion_ingsoft1';

document.addEventListener('DOMContentLoaded', () => {
    cargarTipos();
    configurarFormulario();
});

async function cargarTipos() {
    try {
        const resp = await fetch(`${BASE}/tipos/obtenerAPI`);
        tipos = await resp.json();
        mostrarTipos();
    } catch (e) {
        console.error('Error:', e);
        alerta('Error al cargar tipos', 'error');
    }
}

function mostrarTipos() {
    const tbody = document.querySelector('#TablaTipos tbody');
    tbody.innerHTML = '';
    
    if (!tipos?.length) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center">No hay tipos registrados</td></tr>';
        document.getElementById('infoRegistros').textContent = 'Mostrando 0 a 0 de 0 registros';
        return;
    }
    
    tipos.forEach((tipo, index) => {
        const activo = tipo.tipo_situacion == 1;
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${index + 1}</td>
            <td>${tipo.tipo_nombre || 'N/A'}</td>
            <td>${tipo.tipo_descripcion || 'Sin descripción'}</td>
            <td>
                <span class="badge bg-${activo ? 'success' : 'danger'}">
                    ${activo ? 'ACTIVO' : 'INACTIVO'}
                </span>
            </td>
            <td>
                <button class="btn btn-sm btn-warning me-1" onclick="editar(${tipo.tipo_id})" title="Modificar">
                    <i class="bi bi-pencil"></i>
                </button>
                <button class="btn btn-sm btn-danger" onclick="eliminar(${tipo.tipo_id})" title="Eliminar">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
    
    document.getElementById('infoRegistros').textContent = `Mostrando 1 a ${tipos.length} de ${tipos.length} registros`;
}

function configurarFormulario() {
    const form = document.getElementById('FormTipo');
    if (form) {
        form.addEventListener('submit', e => {
            e.preventDefault();
            if (validar()) guardar();
        });
    }
    
    const btnLimpiar = document.getElementById('btnLimpiar');
    if (btnLimpiar) {
        btnLimpiar.addEventListener('click', limpiarFormulario);
    }
    
    // Validación en tiempo real
    const nombreInput = document.getElementById('tipo_nombre');
    if (nombreInput) {
        nombreInput.addEventListener('input', function() {
            const valor = this.value.trim();
            if (valor.length < 3) {
                this.classList.remove('is-valid');
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            }
        });
    }
}

function validar() {
    const nombre = document.getElementById('tipo_nombre').value.trim();
    const descripcion = document.getElementById('tipo_descripcion').value.trim();
    const errors = [];
    
    if (nombre.length < 3) errors.push('El nombre debe tener al menos 3 caracteres');
    if (nombre.length > 50) errors.push('El nombre no puede exceder 50 caracteres');
    if (descripcion.length > 100) errors.push('La descripción no puede exceder 100 caracteres');
    
    // Verificar nombre único
    const nombreExiste = tipos.some(tipo => 
        tipo.tipo_nombre.toLowerCase() === nombre.toLowerCase() && 
        tipo.tipo_id != editId
    );
    
    if (nombreExiste) errors.push('Ya existe un tipo con ese nombre');
    
    if (errors.length) {
        alerta(errors.join('\n'), 'error');
        return false;
    }
    return true;
}

async function guardar() {
    try {
        const formData = new FormData(document.getElementById('FormTipo'));
        
        const resp = await fetch(`${BASE}/tipos/guardarAPI`, {
            method: 'POST',
            body: formData
        });
        
        const result = await resp.json();
        
        if (result.resultado) {
            alerta(result.mensaje || (editMode ? 'Actualizado correctamente' : 'Guardado correctamente'), 'success');
            limpiarFormulario();
            cargarTipos();
        } else {
            alerta(result.errores?.join('\n') || result.mensaje, 'error');
        }
    } catch (e) {
        console.error('Error:', e);
        alerta('Error al guardar', 'error');
    }
}

function limpiarFormulario() {
    document.getElementById('FormTipo').reset();
    document.getElementById('tipo_id').value = '';
    
    const nombreInput = document.getElementById('tipo_nombre');
    const descripcionInput = document.getElementById('tipo_descripcion');
    
    if (nombreInput) {
        nombreInput.classList.remove('is-valid', 'is-invalid');
    }
    if (descripcionInput) {
        descripcionInput.classList.remove('is-valid', 'is-invalid');
    }
    
    editMode = false;
    editId = null;
    
    const btnGuardar = document.getElementById('btnGuardar');
    if (btnGuardar) {
        btnGuardar.innerHTML = '<i class="bi bi-save me-1"></i>Guardar';
        btnGuardar.classList.remove('btn-warning');
        btnGuardar.classList.add('btn-success');
    }
}

// Funciones globales para botones
window.editar = function(id) {
    const tipo = tipos.find(t => t.tipo_id == id);
    if (!tipo) return alerta('Tipo no encontrado', 'error');
    
    editMode = true;
    editId = id;
    
    document.getElementById('tipo_id').value = tipo.tipo_id;
    document.getElementById('tipo_nombre').value = tipo.tipo_nombre;
    document.getElementById('tipo_descripcion').value = tipo.tipo_descripcion || '';
    
    const btnGuardar = document.getElementById('btnGuardar');
    if (btnGuardar) {
        btnGuardar.innerHTML = '<i class="bi bi-arrow-up me-1"></i>Actualizar';
        btnGuardar.classList.remove('btn-success');
        btnGuardar.classList.add('btn-warning');
    }
    
    // Scroll al formulario
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

window.eliminar = async function(id) {
    const tipo = tipos.find(t => t.tipo_id == id);
    if (!tipo) return alerta('Tipo no encontrado', 'error');
    
    const confirm = await Swal.fire({
        title: '¿Eliminar tipo?',
        text: `Se eliminará "${tipo.tipo_nombre}". Esta acción no se puede deshacer.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#d33'
    });
    
    if (!confirm.isConfirmed) return;
    
    try {
        const resp = await fetch(`${BASE}/tipos/eliminarAPI?tipo_id=${id}`);
        const result = await resp.json();
        
        if (result.resultado) {
            alerta(result.mensaje || 'Eliminado correctamente', 'success');
            cargarTipos();
        } else {
            alerta(result.mensaje, 'error');
        }
    } catch (e) {
        console.error('Error:', e);
        alerta('Error al eliminar', 'error');
    }
}

function alerta(msg, tipo) {
    const icons = { error: 'error', success: 'success', warning: 'warning', info: 'info' };
    Swal.fire({
        title: tipo === 'error' ? 'Error' : 'Información',
        text: msg,
        icon: icons[tipo] || 'info',
        confirmButtonText: 'Entendido'
    });
}