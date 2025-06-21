import Swal from "sweetalert2";

document.addEventListener('DOMContentLoaded', function() {
    cargarAuditoria();
    cargarUsuarios();
    cargarModulos();
    configurarFiltros();
});

async function cargarAuditoria() {
    try {
        const respuesta = await fetch('/morataya_final_dotacion_ingsoft1/auditoria/obtenerAPI');
        const auditoria = await respuesta.json();
        
        mostrarAuditoria(auditoria);
        actualizarContador(auditoria.length);
        
    } catch (error) {
        console.error('Error al cargar auditoría:', error);
        Swal.fire('Error', 'No se pudo cargar la auditoría', 'error');
    }
}

function mostrarAuditoria(auditoria) {
    const tbody = document.querySelector('#TablaAuditoria tbody');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    if (!Array.isArray(auditoria)) {
        console.error('Los datos de auditoría no son un array:', auditoria);
        tbody.innerHTML = '<tr><td colspan="6" class="text-center">No hay datos disponibles</td></tr>';
        return;
    }
    
    if (auditoria.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center">No se encontraron registros</td></tr>';
        return;
    }
    
    auditoria.forEach(registro => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${formatearFecha(registro.aud_fecha_creacion)}</td>
            <td>${registro.aud_usuario_nombre || 'N/A'}</td>
            <td>
                <span class="badge bg-${obtenerColorModulo(registro.aud_modulo)}">
                    ${registro.aud_modulo}
                </span>
            </td>
            <td>
                <span class="badge bg-${obtenerColorAccion(registro.aud_accion)}">
                    ${registro.aud_accion}
                </span>
            </td>
            <td>${registro.aud_descripcion || 'Sin descripción'}</td>
            <td><code>${registro.aud_ip || 'N/A'}</code></td>
        `;
        tbody.appendChild(tr);
    });
}

function obtenerColorModulo(modulo) {
    const colores = {
        'Personal': 'primary',
        'Usuario': 'success',
        'Inventario': 'warning',
        'Solicitudes': 'info',
        'Entregas': 'danger',
        'Dotacion': 'secondary'
    };
    return colores[modulo] || 'light';
}

function obtenerColorAccion(accion) {
    const colores = {
        'Creación': 'success',
        'Actualización': 'warning',
        'Eliminación': 'danger',
        'Consulta': 'info'
    };
    return colores[accion] || 'secondary';
}

function formatearFecha(fecha) {
    if (!fecha) return 'N/A';
    try {
        const date = new Date(fecha);
        return date.toLocaleString('es-GT', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit'
        });
    } catch (error) {
        return fecha; // Devolver fecha original si hay error
    }
}

async function cargarUsuarios() {
    try {
        const respuesta = await fetch('/morataya_final_dotacion_ingsoft1/auditoria/obtenerUsuariosAPI');
        const resultado = await respuesta.json();
        
        if (resultado.resultado && resultado.usuarios) {
            const select = document.getElementById('filtroUsuario');
            if (select) {
                select.innerHTML = '<option value="">Todos los usuarios</option>';
                
                resultado.usuarios.forEach(usuario => {
                    const option = document.createElement('option');
                    option.value = usuario.usu_id;
                    option.textContent = usuario.aud_usuario_nombre;
                    select.appendChild(option);
                });
            }
        }
    } catch (error) {
        console.error('Error al cargar usuarios:', error);
    }
}

async function cargarModulos() {
    try {
        const respuesta = await fetch('/morataya_final_dotacion_ingsoft1/auditoria/obtenerModulosAPI');
        const resultado = await respuesta.json();
        
        if (resultado.resultado && resultado.modulos) {
            const select = document.getElementById('filtroModulo');
            if (select) {
                select.innerHTML = '<option value="">Todos los módulos</option>';
                
                resultado.modulos.forEach(modulo => {
                    const option = document.createElement('option');
                    option.value = modulo.aud_modulo;
                    option.textContent = modulo.aud_modulo;
                    select.appendChild(option);
                });
            }
        }
    } catch (error) {
        console.error('Error al cargar módulos:', error);
    }
}

function configurarFiltros() {
    // Configurar fechas por defecto (últimos 30 días)
    const hoy = new Date();
    const hace30Dias = new Date();
    hace30Dias.setDate(hoy.getDate() - 30);
    
    const fechaInicio = document.getElementById('fechaInicio');
    const fechaFin = document.getElementById('fechaFin');
    
    if (fechaInicio) fechaInicio.value = hace30Dias.toISOString().split('T')[0];
    if (fechaFin) fechaFin.value = hoy.toISOString().split('T')[0];
}

async function filtrarAuditoria() {
    try {
        const params = new URLSearchParams();
        
        const usuario = document.getElementById('filtroUsuario')?.value;
        const modulo = document.getElementById('filtroModulo')?.value;
        const fechaInicio = document.getElementById('fechaInicio')?.value;
        const fechaFin = document.getElementById('fechaFin')?.value;
        
        if (usuario) params.append('usuario_id', usuario);
        if (modulo) params.append('modulo', modulo);
        if (fechaInicio) params.append('fecha_inicio', fechaInicio);
        if (fechaFin) params.append('fecha_fin', fechaFin);
        
        const url = `/morataya_final_dotacion_ingsoft1/auditoria/buscarAPI?${params.toString()}`;
        const respuesta = await fetch(url);
        const resultado = await respuesta.json();
        
        if (resultado.resultado) {
            mostrarAuditoria(resultado.data);
            actualizarContador(resultado.data.length);
        } else {
            Swal.fire('Error', resultado.mensaje, 'error');
        }
        
    } catch (error) {
        console.error('Error al filtrar:', error);
        Swal.fire('Error', 'Error al aplicar filtros', 'error');
    }
}

function limpiarFiltros() {
    const filtroUsuario = document.getElementById('filtroUsuario');
    const filtroModulo = document.getElementById('filtroModulo');
    
    if (filtroUsuario) filtroUsuario.value = '';
    if (filtroModulo) filtroModulo.value = '';
    
    configurarFiltros(); // Restaurar fechas por defecto
    cargarAuditoria();
}

async function mostrarEstadisticas() {
    try {
        const respuesta = await fetch('/morataya_final_dotacion_ingsoft1/auditoria/estadisticasAPI');
        const resultado = await respuesta.json();
        
        if (resultado.resultado) {
            const stats = resultado.estadisticas;
            
            const totalRegistros = document.getElementById('totalRegistros');
            const moduloMasUsado = document.getElementById('moduloMasUsado');
            const actividadHoy = document.getElementById('actividadHoy');
            const ultimaSemana = document.getElementById('ultimaSemana');
            
            if (totalRegistros) totalRegistros.textContent = stats.total;
            
            if (stats.modulos.length > 0 && moduloMasUsado) {
                moduloMasUsado.textContent = stats.modulos[0].aud_modulo;
            }
            
            const hoy = new Date().toISOString().split('T')[0];
            const actividadHoyData = stats.dias.find(d => d.fecha === hoy);
            if (actividadHoy) actividadHoy.textContent = actividadHoyData ? actividadHoyData.cantidad : 0;
            
            const totalSemana = stats.dias.reduce((sum, dia) => sum + parseInt(dia.cantidad), 0);
            if (ultimaSemana) ultimaSemana.textContent = totalSemana;
            
            // Mostrar sección de estadísticas
            const estadisticas = document.getElementById('estadisticas');
            if (estadisticas) estadisticas.style.display = 'block';
            
            Swal.fire({
                icon: 'success',
                title: 'Estadísticas cargadas',
                text: `Total de ${stats.total} registros de auditoría`,
                timer: 2000,
                showConfirmButton: false
            });
        }
    } catch (error) {
        console.error('Error al cargar estadísticas:', error);
        Swal.fire('Error', 'Error al cargar estadísticas', 'error');
    }
}

function actualizarContador(cantidad) {
    const contador = document.getElementById('contadorRegistros');
    if (contador) {
        contador.textContent = `${cantidad} registro${cantidad !== 1 ? 's' : ''}`;
    }
}

// Funciones globales
window.cargarAuditoria = cargarAuditoria;
window.filtrarAuditoria = filtrarAuditoria;
window.limpiarFiltros = limpiarFiltros;
window.mostrarEstadisticas = mostrarEstadisticas;