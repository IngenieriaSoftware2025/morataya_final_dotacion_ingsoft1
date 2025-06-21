let solicitudes = [];
let editMode = false;
let editId = null;
let stockDisponible = 0;
const BASE = '/morataya_final_dotacion_ingsoft1';

$(document).ready(() => {
    cargarDatos();
    configurarFormulario();
    configurarEventosTipo();
});

async function cargarDatos() {
    try {
        const [solicitudesData, personal, tipos] = await Promise.all([
            fetch(`${BASE}/dotacion/obtenerSolicitudesAPI`).then(r => r.json()),
            fetch(`${BASE}/personal/obtenerAPI`).then(r => r.json()),
            fetch(`${BASE}/tipos/obtenerAPI`).then(r => r.json())
        ]);
        
        solicitudes = solicitudesData;
        llenarSelect('personal_id', personal, 'personal_id', 'personal_nombre');
        llenarSelect('tipo_id', tipos, 'tipo_id', 'tipo_nombre');
        
        // Limpiar select de tallas al inicio
        $('select[name="talla_id"]').html('<option value="">-- SELECCIONA TIPO PRIMERO --</option>');
        
        mostrarSolicitudes();
        
    } catch (e) {
        alerta('Error al cargar datos', 'error');
    }
}

function configurarEventosTipo() {
    $('select[name="tipo_id"]').on('change', async function() {
        const tipoId = $(this).val();
        const tallaSelect = $('select[name="talla_id"]');
        const cantidadInput = $('input[name="cantidad"]');
        
        if (!tipoId) {
            tallaSelect.html('<option value="">-- SELECCIONA TIPO PRIMERO --</option>');
            cantidadInput.attr('max', 1).val(1);
            stockDisponible = 0;
            return;
        }
        
        try {
            tallaSelect.html('<option value="">Cargando...</option>');
            cantidadInput.attr('max', 1).val(1);
            
            const resp = await fetch(`${BASE}/tallas/obtenerDisponiblesAPI?tipo_id=${tipoId}`);
            const tallasDisponibles = await resp.json();
            
            tallaSelect.html('<option value="">-- SELECCIONA TALLA --</option>');
            
            if (tallasDisponibles && tallasDisponibles.length > 0) {
                tallasDisponibles.forEach(talla => {
                    tallaSelect.append(`<option value="${talla.talla_id}" data-stock="${talla.stock_disponible}">
                        ${talla.talla_etiqueta} (Stock: ${talla.stock_disponible})
                    </option>`);
                });
            } else {
                tallaSelect.html('<option value="">-- SIN STOCK DISPONIBLE --</option>');
            }
            
        } catch (e) {
            console.error('Error:', e);
            tallaSelect.html('<option value="">-- ERROR AL CARGAR --</option>');
        }
    });
    
    $('select[name="talla_id"]').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const stock = parseInt(selectedOption.data('stock')) || 0;
        const cantidadInput = $('input[name="cantidad"]');
        
        stockDisponible = stock;
        
        if (stock > 0) {
            cantidadInput.attr('max', stock).val(1);
            cantidadInput.next('.stock-info').remove();
            cantidadInput.after(`<small class="stock-info text-muted d-block">Máximo disponible: ${stock}</small>`);
        } else {
            cantidadInput.attr('max', 1).val(1);
            cantidadInput.next('.stock-info').remove();
        }
    });
}

function llenarSelect(name, data, valueField, textField) {
    const select = $(`select[name="${name}"]`);
    const placeholder = select.find('option:first').text();
    select.html(`<option value="">${placeholder}</option>`);
    data?.forEach(item => select.append(`<option value="${item[valueField]}">${item[textField]}</option>`));
}

function mostrarSolicitudes() {
    const tbody = $('#TablaSolicitudes tbody');
    tbody.empty();
    
    if (!solicitudes?.length) {
        tbody.html('<tr><td colspan="8" class="text-center">No hay solicitudes registradas</td></tr>');
        $('#infoRegistros').text('Mostrando 0 a 0 de 0 registros');
        return;
    }
    
    solicitudes.forEach((s, index) => {
        const entregado = s.estado_entrega == 1;
        tbody.append(`
            <tr>
                <td>${index + 1}</td>
                <td>${s.personal_nombre || 'N/A'}</td>
                <td>${s.tipo_nombre || 'N/A'}</td>
                <td><span class="badge bg-secondary">${s.talla_etiqueta || 'N/A'}</span></td>
                <td><span class="badge bg-info">${s.cantidad || 1}</span></td>
                <td>${s.fecha_solicitud || 'N/A'}</td>
                <td><span class="badge bg-${entregado ? 'success' : 'warning'}">${entregado ? 'ENTREGADO' : 'PENDIENTE'}</span></td>
                <td>
                    ${!entregado ? `<button class="btn btn-sm btn-success me-1" onclick="entregar(${s.solicitud_id})" title="Entregar"><i class="bi bi-check"></i></button>` : ''}
                    ${!entregado ? `<button class="btn btn-sm btn-warning me-1" onclick="editar(${s.solicitud_id})" title="Modificar"><i class="bi bi-pencil"></i></button>` : ''}
                    ${!entregado ? `<button class="btn btn-sm btn-danger" onclick="eliminar(${s.solicitud_id})" title="Eliminar"><i class="bi bi-trash"></i></button>` : ''}
                </td>
            </tr>
        `);
    });
    
    $('#infoRegistros').text(`Mostrando 1 a ${solicitudes.length} de ${solicitudes.length} registros`);
}

function configurarFormulario() {
    $('#FormSolicitud').on('submit', e => {
        e.preventDefault();
        if (validar()) guardar();
    });
    
    $('#btnLimpiar').on('click', limpiarFormulario);
}

function validar() {
    const data = new FormData($('#FormSolicitud')[0]);
    const errors = [];
    
    if (!data.get('personal_id')) errors.push('Seleccione el personal');
    if (!data.get('tipo_id')) errors.push('Seleccione el tipo de dotación');
    if (!data.get('talla_id')) errors.push('Seleccione la talla');
    
    const cantidad = parseInt(data.get('cantidad')) || 0;
    if (cantidad < 1) errors.push('La cantidad debe ser mayor a 0');
    if (cantidad > stockDisponible) errors.push(`La cantidad no puede ser mayor al stock disponible (${stockDisponible})`);
    
    if (errors.length) {
        alerta(errors.join('\n'), 'error');
        return false;
    }
    return true;
}

async function guardar() {
    try {
        const url = editMode ? `${BASE}/dotacion/actualizarSolicitudAPI` : `${BASE}/dotacion/guardarSolicitudAPI`;
        const data = new FormData($('#FormSolicitud')[0]);
        
        if (editMode) data.append('solicitud_id', editId);
        
        const resp = await fetch(url, { method: 'POST', body: data });
        const result = await resp.json();
        
        if (result.resultado) {
            alerta(result.mensaje || (editMode ? 'Actualizado correctamente' : 'Guardado correctamente'), 'success');
            limpiarFormulario();
            cargarDatos();
        } else {
            alerta(result.errores?.join('\n') || result.mensaje, 'error');
        }
    } catch (e) {
        alerta('Error al guardar', 'error');
    }
}

function limpiarFormulario() {
    $('#FormSolicitud')[0].reset();
    $('input[name="cantidad"]').val(1).attr('max', 1);
    $('select[name="talla_id"]').html('<option value="">-- SELECCIONA TIPO PRIMERO --</option>');
    $('.stock-info').remove();
    editMode = false;
    editId = null;
    stockDisponible = 0;
    $('#btnGuardar').html('<i class="bi bi-save me-1"></i>Guardar').removeClass('btn-warning').addClass('btn-success');
}

window.editar = async function(id) {
    const solicitud = solicitudes.find(s => s.solicitud_id == id);
    if (!solicitud) return alerta('Solicitud no encontrada', 'error');
    
    editMode = true;
    editId = id;
    
    $('select[name="personal_id"]').val(solicitud.personal_id);
    $('select[name="tipo_id"]').val(solicitud.tipo_id);
    
    await $('select[name="tipo_id"]').trigger('change');
    
    setTimeout(() => {
        $('select[name="talla_id"]').val(solicitud.talla_id);
        $('select[name="talla_id"]').trigger('change');
        $('input[name="cantidad"]').val(solicitud.cantidad || 1);
    }, 500);
    
    $('#btnGuardar').html('<i class="bi bi-arrow-up me-1"></i>Actualizar').removeClass('btn-success').addClass('btn-warning');
    
    $('html, body').animate({ scrollTop: 0 }, 500);
}

window.eliminar = async function(id) {
    const confirm = await Swal.fire({
        title: '¿Eliminar solicitud?',
        text: 'Esta acción no se puede deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#d33'
    });
    
    if (!confirm.isConfirmed) return;
    
    try {
        const data = new FormData();
        data.append('solicitud_id', id);
        
        const resp = await fetch(`${BASE}/dotacion/eliminarSolicitudAPI`, { method: 'POST', body: data });
        const result = await resp.json();
        
        if (result.resultado) {
            alerta(result.mensaje || 'Eliminado correctamente', 'success');
            cargarDatos();
        } else {
            alerta(result.errores?.join('\n') || result.mensaje, 'error');
        }
    } catch (e) {
        alerta('Error al eliminar', 'error');
    }
}

window.entregar = async function(id) {
    const confirm = await Swal.fire({
        title: '¿Procesar entrega?',
        text: 'Se marcará como entregada y se descontará del inventario',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Entregar',
        cancelButtonText: 'Cancelar'
    });
    
    if (!confirm.isConfirmed) return;
    
    try {
        const data = new FormData();
        data.append('solicitud_id', id);
        
        const resp = await fetch(`${BASE}/dotacion/procesarEntregaAPI`, { method: 'POST', body: data });
        const result = await resp.json();
        
        if (result.resultado) {
            alerta(result.mensaje || 'Entrega procesada correctamente', 'success');
            cargarDatos();
        } else {
            alerta(result.errores?.join('\n') || result.mensaje, 'error');
        }
    } catch (e) {
        alerta('Error al procesar entrega', 'error');
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