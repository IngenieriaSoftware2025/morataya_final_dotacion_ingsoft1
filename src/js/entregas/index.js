let pendientes = [];
let entregas = [];
const BASE = '/morataya_final_dotacion_ingsoft1';

document.addEventListener('DOMContentLoaded', () => {
    cargarDatos();
});

async function cargarDatos() {
    actualizarEstado('Cargando...', 'warning');
    
    await Promise.all([
        cargarSolicitudesPendientes(),
        cargarEntregasRealizadas()
    ]);
    
    actualizarContadores();
    actualizarUltimaActualizacion();
    actualizarEstado('Operativo', 'success');
}

async function cargarSolicitudesPendientes() {
    try {
        const resp = await fetch(`${BASE}/dotacion/obtenerSolicitudesPendientesAPI`);
        pendientes = await resp.json();
        mostrarPendientes();
    } catch (e) {
        console.error('Error al cargar pendientes:', e);
        actualizarEstado('Error en pendientes', 'danger');
        alerta('Error al cargar solicitudes pendientes', 'error');
    }
}

async function cargarEntregasRealizadas() {
    try {
        const resp = await fetch(`${BASE}/dotacion/obtenerEntregasAPI`);
        entregas = await resp.json();
        mostrarEntregas();
    } catch (e) {
        console.error('Error al cargar entregas:', e);
        actualizarEstado('Error en entregas', 'danger');
        alerta('Error al cargar entregas realizadas', 'error');
    }
}

function mostrarPendientes() {
    const tbody = document.querySelector('#TablaPendientes tbody');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    if (!pendientes?.length) {
        tbody.innerHTML = `
            <tr>
                <td colspan="3" class="text-center text-muted py-4">
                    <i class="bi bi-inbox display-4 d-block mb-2"></i>
                    No hay solicitudes pendientes
                </td>
            </tr>
        `;
        return;
    }
    
    pendientes.forEach(solicitud => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>
                <div>
                    <strong>${solicitud.personal_nombre}</strong><br>
                    <small class="text-muted">
                        <i class="bi bi-calendar3"></i> ${solicitud.fecha_solicitud}
                    </small>
                </div>
            </td>
            <td>
                <div>
                    <span class="badge bg-primary me-1">${solicitud.tipo_nombre}</span>
                    <span class="badge bg-secondary">${solicitud.talla_etiqueta}</span><br>
                    <small class="text-info">
                        <i class="bi bi-box"></i> Cantidad: ${solicitud.cantidad || 1}
                    </small>
                </div>
            </td>
            <td>
                <button class="btn btn-success btn-sm w-100" onclick="procesarEntrega(${solicitud.solicitud_id})" 
                        title="Procesar entrega">
                    <i class="bi bi-check-circle"></i> Entregar
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function mostrarEntregas() {
    const tbody = document.querySelector('#TablaEntregas tbody');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    if (!entregas?.length) {
        tbody.innerHTML = `
            <tr>
                <td colspan="3" class="text-center text-muted py-4">
                    <i class="bi bi-archive display-4 d-block mb-2"></i>
                    No hay entregas registradas
                </td>
            </tr>
        `;
        return;
    }
    
    entregas.forEach(entrega => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>
                <div>
                    <strong>${entrega.personal_nombre}</strong><br>
                    <small class="text-muted">
                        <i class="bi bi-person-badge"></i> ${entrega.personal_cui || 'N/A'}
                    </small>
                </div>
            </td>
            <td>
                <div>
                    <span class="badge bg-success me-1">${entrega.tipo_nombre}</span>
                    <span class="badge bg-secondary">${entrega.talla_etiqueta}</span><br>
                    <small class="text-info">
                        <i class="bi bi-box"></i> Cantidad: ${entrega.cantidad || 1}
                    </small>
                </div>
            </td>
            <td>
                <div>
                    <strong>${entrega.fecha_entrega}</strong><br>
                    <small class="text-muted">
                        <i class="bi bi-person-check"></i> ${entrega.usuario_entrega}
                    </small>
                </div>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function actualizarContadores() {
    // Contadores en headers
    document.getElementById('contadorPendientes').textContent = pendientes?.length || 0;
    document.getElementById('contadorEntregas').textContent = entregas?.length || 0;
    
    // Estadísticas en footer
    document.getElementById('totalPendientes').textContent = pendientes?.length || 0;
    
    // Entregas de hoy
    const hoy = new Date().toISOString().split('T')[0];
    const entregasHoy = entregas?.filter(e => e.fecha_entrega === hoy).length || 0;
    document.getElementById('entregasHoy').textContent = entregasHoy;
}

function actualizarUltimaActualizacion() {
    const ahora = new Date();
    const fecha = ahora.toLocaleString('es-GT');
    document.getElementById('ultimaActualizacion').textContent = fecha;
}

function actualizarEstado(texto, tipo) {
    const estadoEl = document.getElementById('estadoSistema');
    if (!estadoEl) return;
    
    estadoEl.className = `badge bg-${tipo}`;
    
    const iconos = {
        success: 'bi-check-circle',
        warning: 'bi-exclamation-triangle',
        danger: 'bi-x-circle'
    };
    
    estadoEl.innerHTML = `<i class="bi ${iconos[tipo] || 'bi-info-circle'}"></i> ${texto}`;
}

// Función global para procesar entregas
window.procesarEntrega = async function(solicitudId) {
    const solicitud = pendientes.find(s => s.solicitud_id == solicitudId);
    if (!solicitud) return alerta('Solicitud no encontrada', 'error');
    
    const confirm = await Swal.fire({
        title: '¿Procesar entrega?',
        html: `
            <div class="text-start">
                <p><strong>Personal:</strong> ${solicitud.personal_nombre}</p>
                <p><strong>Artículo:</strong> ${solicitud.tipo_nombre} - Talla ${solicitud.talla_etiqueta}</p>
                <p><strong>Cantidad:</strong> ${solicitud.cantidad || 1}</p>
                <p><strong>Fecha solicitud:</strong> ${solicitud.fecha_solicitud}</p>
            </div>
            <hr>
            <p class="text-warning">
                <i class="bi bi-exclamation-triangle"></i> 
                Esta acción descontará del inventario y no se puede deshacer
            </p>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-check"></i> Sí, entregar',
        cancelButtonText: '<i class="bi bi-x"></i> Cancelar',
        confirmButtonColor: '#28a745'
    });
    
    if (!confirm.isConfirmed) return;
    
    try {
        // Mostrar loading
        Swal.fire({
            title: 'Procesando entrega...',
            html: 'Por favor espere mientras se procesa la entrega',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => Swal.showLoading()
        });
        
        const formData = new FormData();
        formData.append('solicitud_id', solicitudId);
        
        const resp = await fetch(`${BASE}/dotacion/procesarEntregaAPI`, {
            method: 'POST',
            body: formData
        });
        
        const result = await resp.json();
        
        if (result.resultado) {
            Swal.fire({
                title: '¡Entrega procesada!',
                text: result.mensaje || 'La entrega se procesó correctamente',
                icon: 'success',
                confirmButtonText: 'Entendido'
            });
            cargarDatos(); // Recargar ambas tablas
        } else {
            alerta(result.errores?.join('\n') || result.mensaje, 'error');
        }
    } catch (e) {
        console.error('Error:', e);
        alerta('Error al procesar la entrega', 'error');
    }
}

// Función global para actualizar manualmente
window.cargarDatos = cargarDatos;

function alerta(msg, tipo) {
    const icons = { error: 'error', success: 'success', warning: 'warning', info: 'info' };
    const titles = { error: 'Error', success: '¡Éxito!', warning: 'Advertencia', info: 'Información' };
    
    Swal.fire({
        title: titles[tipo] || 'Información',
        text: msg,
        icon: icons[tipo] || 'info',
        confirmButtonText: 'Entendido'
    });
}