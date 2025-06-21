let tablaInventario = null;
let inventarioData = [];
const BASE_URL = '/morataya_final_dotacion_ingsoft1';

document.addEventListener('DOMContentLoaded', function() {
    cargarInventario();
    cargarTipos();
    cargarTallas();
    configurarFormulario();
    configurarFormularioEditar();
});

async function cargarInventario() {
    try {
        const respuesta = await fetch(`${BASE_URL}/dotacion/obtenerInventarioAPI`);
        const inventarios = await respuesta.json();
        inventarioData = inventarios;
        mostrarInventario(inventarios);
        inicializarDataTable();
    } catch (error) {
        console.error('Error:', error);
    }
}

function mostrarInventario(inventarios) {
    const tbody = document.querySelector('#TablaInventario tbody');
    tbody.innerHTML = '';
    
    if (!Array.isArray(inventarios) || inventarios.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center">No hay inventario</td></tr>';
        return;
    }
    
    inventarios.forEach(item => {
        const badgeClass = item.cantidad > 20 ? 'success' : item.cantidad > 5 ? 'warning' : 'danger';
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${item.tipo_nombre || 'N/A'}</td>
            <td><span class="badge bg-secondary">${item.talla_etiqueta || 'N/A'}</span></td>
            <td><span class="badge bg-${badgeClass}">${item.cantidad || 0}</span></td>
            <td>${item.fecha_ingreso || 'N/A'}</td>
            <td>
                <button class="btn btn-sm btn-outline-primary" onclick="editarInventario(${item.inv_id})">
                    <i class="bi bi-pencil"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

async function cargarTipos() {
    try {
        const respuesta = await fetch(`${BASE_URL}/tipos/obtenerAPI`);
        const tipos = await respuesta.json();
        const select = document.querySelector('select[name="tipo_id"]');
        
        if (select) {
            select.innerHTML = '<option value="">Seleccionar...</option>';
            
            const tiposUnicos = [];
            const idsVistos = new Set();
            
            tipos.forEach(tipo => {
                if (!idsVistos.has(tipo.tipo_id)) {
                    idsVistos.add(tipo.tipo_id);
                    tiposUnicos.push(tipo);
                }
            });
            
            tiposUnicos.forEach(tipo => {
                select.innerHTML += `<option value="${tipo.tipo_id}">${tipo.tipo_nombre}</option>`;
            });
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

async function cargarTallas() {
    try {
        const respuesta = await fetch(`${BASE_URL}/tallas/obtenerAPI`);
        const tallas = await respuesta.json();
        const select = document.querySelector('select[name="talla_id"]');
        
        if (select) {
            select.innerHTML = '<option value="">Seleccionar...</option>';
            
            const tallasUnicas = [];
            const idsVistos = new Set();
            
            tallas.forEach(talla => {
                if (!idsVistos.has(talla.talla_id)) {
                    idsVistos.add(talla.talla_id);
                    tallasUnicas.push(talla);
                }
            });
            
            tallasUnicas.forEach(talla => {
                select.innerHTML += `<option value="${talla.talla_id}">${talla.talla_etiqueta}</option>`;
            });
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

function configurarFormulario() {
    const formulario = document.getElementById('FormInventario');
    if (formulario) {
        formulario.addEventListener('submit', function(e) {
            e.preventDefault();
            if (validarFormulario()) {
                guardarInventario();
            }
        });
        
        const inputs = formulario.querySelectorAll('input, select');
        inputs.forEach(input => {
            input.addEventListener('change', validarEnTiempoReal);
            input.addEventListener('input', validarEnTiempoReal);
        });
    }
}

function configurarFormularioEditar() {
    const formulario = document.getElementById('FormEditarInventario');
    if (formulario) {
        formulario.addEventListener('submit', function(e) {
            e.preventDefault();
            actualizarInventario();
        });
    }
}

function validarFormulario() {
    const formData = new FormData(document.getElementById('FormInventario'));
    const errores = [];
    
    const tipoId = formData.get('tipo_id');
    const tallaId = formData.get('talla_id');
    const cantidad = formData.get('cantidad');
    
    if (!tipoId) errores.push('Seleccione un tipo de dotación');
    if (!tallaId) errores.push('Seleccione una talla');
    if (!cantidad) errores.push('Ingrese una cantidad');
    else {
        const cantidadNum = parseInt(cantidad);
        if (isNaN(cantidadNum) || cantidadNum < 1) errores.push('La cantidad debe ser mayor a 0');
        if (cantidadNum > 9999) errores.push('La cantidad no puede ser mayor a 9999');
    }
    
    if (errores.length > 0) {
        mostrarMensaje(errores.join('\n'), 'error');
        return false;
    }
    return true;
}

function validarEnTiempoReal() {
    const formulario = document.getElementById('FormInventario');
    const submitBtn = formulario.querySelector('button[type="submit"]');
    const formData = new FormData(formulario);
    
    const tipoId = formData.get('tipo_id');
    const tallaId = formData.get('talla_id');
    const cantidad = formData.get('cantidad');
    
    const esValido = tipoId && tallaId && cantidad && parseInt(cantidad) > 0 && parseInt(cantidad) <= 9999;
    
    if (submitBtn) {
        submitBtn.disabled = !esValido;
        submitBtn.classList.toggle('btn-primary', esValido);
        submitBtn.classList.toggle('btn-secondary', !esValido);
    }
}

async function guardarInventario() {
    try {
        const formulario = document.getElementById('FormInventario');
        const formData = new FormData(formulario);
        
        mostrarMensaje('Guardando...', 'loading');
        
        const respuesta = await fetch(`${BASE_URL}/dotacion/guardarInventarioAPI`, {
            method: 'POST',
            body: formData
        });
        
        const resultado = await respuesta.json();
        
        if (resultado.resultado) {
            mostrarMensaje(resultado.mensaje || 'Guardado correctamente', 'success');
            formulario.reset();
            cerrarModal();
            cargarInventario();
        } else {
            const mensaje = resultado.errores ? resultado.errores.join('\n') : resultado.mensaje;
            mostrarMensaje(mensaje, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarMensaje('Error al guardar', 'error');
    }
}

async function actualizarInventario() {
    try {
        const formulario = document.getElementById('FormEditarInventario');
        const formData = new FormData(formulario);
        
        mostrarMensaje('Actualizando...', 'loading');
        
        const respuesta = await fetch(`${BASE_URL}/dotacion/actualizarInventarioAPI`, {
            method: 'POST',
            body: formData
        });
        
        const resultado = await respuesta.json();
        
        if (resultado.resultado) {
            mostrarMensaje(resultado.mensaje || 'Actualizado correctamente', 'success');
            cerrarModalEditar();
            cargarInventario();
        } else {
            const mensaje = resultado.errores ? resultado.errores.join('\n') : resultado.mensaje;
            mostrarMensaje(mensaje, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarMensaje('Error al actualizar', 'error');
    }
}

function mostrarMensaje(mensaje, tipo) {
    if (tipo === 'loading') {
        Swal.fire({
            title: mensaje,
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => Swal.showLoading()
        });
    } else {
        const iconos = { error: 'error', success: 'success', warning: 'warning', info: 'info' };
        Swal.fire({
            title: tipo === 'error' ? 'Error' : tipo === 'success' ? 'Éxito' : 'Información',
            text: mensaje,
            icon: iconos[tipo] || 'info',
            confirmButtonText: 'Entendido'
        });
    }
}

function cerrarModal() {
    const modal = document.getElementById('ModalInventario');
    if (modal && typeof bootstrap !== 'undefined') {
        const modalInstance = bootstrap.Modal.getInstance(modal);
        if (modalInstance) modalInstance.hide();
    }
}

function cerrarModalEditar() {
    const modal = document.getElementById('ModalEditarInventario');
    if (modal && typeof bootstrap !== 'undefined') {
        const modalInstance = bootstrap.Modal.getInstance(modal);
        if (modalInstance) modalInstance.hide();
    }
}

function inicializarDataTable() {
    if (typeof $ !== 'undefined' && $.fn.DataTable) {
        if (tablaInventario) tablaInventario.destroy();
        tablaInventario = $('#TablaInventario').DataTable({
            responsive: true,
            pageLength: 25,
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
            }
        });
    }
}

window.editarInventario = function(id) {
    const item = inventarioData.find(inv => inv.inv_id == id);
    if (item) {
        document.getElementById('edit_inv_id').value = item.inv_id;
        document.getElementById('edit_tipo_nombre').value = item.tipo_nombre;
        document.getElementById('edit_talla_etiqueta').value = item.talla_etiqueta;
        document.getElementById('edit_cantidad').value = item.cantidad;
        
        const modal = new bootstrap.Modal(document.getElementById('ModalEditarInventario'));
        modal.show();
    } else {
        mostrarMensaje('No se encontró el inventario', 'error');
    }
};