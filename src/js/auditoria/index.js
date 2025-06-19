import { Dropdown } from "bootstrap";
import Swal from "sweetalert2";
import DataTable from "datatables.net-bs5";
import { lenguaje } from "../lenguaje";

// Elementos del DOM
const BtnBuscarActividades = document.getElementById('BtnBuscarActividades');
const BtnEstadisticas = document.getElementById('BtnEstadisticas');
const BtnExportar = document.getElementById('BtnExportar');
const BtnResumen = document.getElementById('BtnResumen');
const SelectUsuario = document.getElementById('filtro_usuario');
const SelectModulo = document.getElementById('filtro_modulo');
const SelectAccion = document.getElementById('filtro_accion');
const InputFechaInicio = document.getElementById('fecha_inicio');
const InputFechaFin = document.getElementById('fecha_fin');
const BtnLimpiarFiltros = document.getElementById('BtnLimpiarFiltros');
const seccionTabla = document.getElementById('seccionTabla');
const seccionEstadisticas = document.getElementById('seccionEstadisticas');

// Variables para gráficos
let chartModulos = null;
let chartDias = null;

/**
 * Cargar usuarios para el filtro
 */
const cargarUsuarios = async () => {
    const url = `/morataya_final_dotacion_ingsoft1/auditoria/buscarUsuariosAPI`;
    const config = { method: 'GET' }

    try {
        const respuesta = await fetch(url, config);
        const datos = await respuesta.json();
        const { codigo, mensaje, data } = datos;

        if (codigo == 1) {
            SelectUsuario.innerHTML = '<option value="">Todos los usuarios</option>';
            
            data.forEach(usuario => {
                const option = document.createElement('option');
                option.value = usuario.usu_id;
                option.textContent = usuario.usuario_nombre;
                SelectUsuario.appendChild(option);
            });
        } else {
            await Swal.fire({
                position: "center",
                icon: "info",
                title: "Error",
                text: mensaje,
                showConfirmButton: true,
            });
        }
    } catch (error) {
        console.error('Error cargando usuarios:', error);
    }
}

/**
 * Organizar datos por módulo con iconos
 */
const organizarDatosPorModulo = (data) => {
    const modulos = ['LOGIN', 'USUARIOS', 'PERSONAL', 'INVENTARIO', 'SOLICITUDES', 'ENTREGAS', 'TIPOS_DOTACION', 'TALLAS', 'REPORTES'];
    const iconos = {
        'LOGIN': '🔐',
        'USUARIOS': '👤',
        'PERSONAL': '👥',
        'INVENTARIO': '📦',
        'SOLICITUDES': '📝',
        'ENTREGAS': '🚚',
        'TIPOS_DOTACION': '👕',
        'TALLAS': '📏',
        'REPORTES': '📊'
    };
    
    let datosOrganizados = [];
    let contador = 1;
    
    modulos.forEach(modulo => {
        const actividadesModulo = data.filter(actividad => actividad.aud_modulo === modulo);
        
        if (actividadesModulo.length > 0) {
            datosOrganizados.push({
                esSeparador: true,
                modulo: modulo,
                icono: iconos[modulo],
                cantidad: actividadesModulo.length
            });
            
            actividadesModulo.forEach(actividad => {
                datosOrganizados.push({
                    ...actividad,
                    numeroConsecutivo: contador++,
                    esSeparador: false
                });
            });
        }
    });
    
    return datosOrganizados;
}

/**
 * Buscar actividades de auditoría
 */
const buscarActividades = async () => {
    const params = new URLSearchParams();
    
    if (InputFechaInicio.value) params.append('fecha_inicio', InputFechaInicio.value);
    if (InputFechaFin.value) params.append('fecha_fin', InputFechaFin.value);
    if (SelectUsuario.value) params.append('usuario_id', SelectUsuario.value);
    if (SelectModulo.value) params.append('modulo', SelectModulo.value);
    if (SelectAccion.value) params.append('accion', SelectAccion.value);

    const url = `/morataya_final_dotacion_ingsoft1/auditoria/buscarAPI${params.toString() ? '?' + params.toString() : ''}`;
    const config = { method: 'GET' }

    try {
        const respuesta = await fetch(url, config);
        const datos = await respuesta.json();
        const { codigo, mensaje, data } = datos;

        if (codigo == 1) {
            console.log('Actividades encontradas:', data);
            
            const datosOrganizados = organizarDatosPorModulo(data);

            if (datatable) {
                datatable.clear().draw();
                datatable.rows.add(datosOrganizados).draw();
            }
        } else {
            await Swal.fire({
                position: "center",
                icon: "info",
                title: "Error",
                text: mensaje,
                showConfirmButton: true,
            });
        }
    } catch (error) {
        console.error('Error buscando actividades:', error);
    }
}

/**
 * Mostrar/ocultar tabla
 */
const mostrarTabla = () => {
    if (seccionTabla.style.display === 'none') {
        seccionTabla.style.display = 'block';
        buscarActividades();
    } else {
        seccionTabla.style.display = 'none';
    }
}

/**
 * Cargar y mostrar estadísticas
 */
const cargarEstadisticas = async () => {
    const url = `/morataya_final_dotacion_ingsoft1/auditoria/estadisticasAPI`;
    const config = { method: 'GET' }

    try {
        const respuesta = await fetch(url, config);
        const datos = await respuesta.json();
        const { codigo, mensaje, data } = datos;

        if (codigo == 1) {
            mostrarGraficos(data);
            seccionEstadisticas.style.display = 'block';
        } else {
            await Swal.fire({
                position: "center",
                icon: "error",
                title: "Error",
                text: mensaje,
                showConfirmButton: true,
            });
        }
    } catch (error) {
        console.error('Error cargando estadísticas:', error);
    }
}

/**
 * Mostrar gráficos con Chart.js
 */
const mostrarGraficos = (data) => {
    // Destruir gráficos existentes
    if (chartModulos) chartModulos.destroy();
    if (chartDias) chartDias.destroy();

    // Gráfico por módulos
    const ctxModulos = document.getElementById('chartModulos').getContext('2d');
    chartModulos = new Chart(ctxModulos, {
        type: 'doughnut',
        data: {
            labels: data.modulos.map(m => m.aud_modulo),
            datasets: [{
                data: data.modulos.map(m => m.total),
                backgroundColor: [
                    '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0',
                    '#9966FF', '#FF9F40', '#FF6384', '#C9CBCF'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Actividades por Módulo'
                }
            }
        }
    });

    // Gráfico por días
    const ctxDias = document.getElementById('chartDias').getContext('2d');
    chartDias = new Chart(ctxDias, {
        type: 'line',
        data: {
            labels: data.dias.map(d => d.fecha),
            datasets: [{
                label: 'Actividades por Día',
                data: data.dias.map(d => d.total),
                borderColor: '#36A2EB',
                backgroundColor: 'rgba(54, 162, 235, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Actividades de los Últimos 7 Días'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
}

/**
 * Limpiar todos los filtros
 */
const limpiarFiltros = () => {
    SelectUsuario.value = '';
    SelectModulo.value = '';
    SelectAccion.value = '';
    InputFechaInicio.value = '';
    InputFechaFin.value = '';
    
    if (seccionTabla.style.display !== 'none') {
        buscarActividades();
    }
}

/**
 * Exportar datos a CSV
 */
const exportarDatos = async () => {
    try {
        const params = new URLSearchParams();
        if (InputFechaInicio.value) params.append('fecha_inicio', InputFechaInicio.value);
        if (InputFechaFin.value) params.append('fecha_fin', InputFechaFin.value);
        if (SelectUsuario.value) params.append('usuario_id', SelectUsuario.value);
        if (SelectModulo.value) params.append('modulo', SelectModulo.value);
        if (SelectAccion.value) params.append('accion', SelectAccion.value);

        const url = `/morataya_final_dotacion_ingsoft1/auditoria/buscarAPI${params.toString() ? '?' + params.toString() : ''}`;
        const respuesta = await fetch(url);
        const datos = await respuesta.json();

        if (datos.codigo == 1) {
            const csvContent = generarCSV(datos.data);
            descargarCSV(csvContent, 'auditoria_sistema.csv');
        }
    } catch (error) {
        console.error('Error exportando datos:', error);
    }
}

/**
 * Generar contenido CSV
 */
const generarCSV = (data) => {
    const headers = ['ID', 'Usuario', 'Módulo', 'Acción', 'Descripción', 'Ruta', 'IP', 'Navegador', 'Fecha'];
    const csvRows = [headers.join(',')];
    
    data.forEach(row => {
        const values = [
            row.aud_id,
            `"${row.usuario_nombre}"`,
            row.aud_modulo,
            row.aud_accion,
            `"${row.aud_descripcion || ''}"`,
            `"${row.aud_ruta || ''}"`,
            row.aud_ip,
            row.aud_navegador,
            row.aud_fecha_creacion
        ];
        csvRows.push(values.join(','));
    });
    
    return csvRows.join('\n');
}

/**
 * Descargar archivo CSV
 */
const descargarCSV = (content, filename) => {
    const blob = new Blob([content], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', filename);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

/**
 * Mostrar resumen del día actual
 */
const mostrarResumenDia = async () => {
    const hoy = new Date().toISOString().split('T')[0];
    const params = new URLSearchParams();
    params.append('fecha_inicio', hoy);
    params.append('fecha_fin', hoy);

    try {
        const url = `/morataya_final_dotacion_ingsoft1/auditoria/buscarAPI?${params.toString()}`;
        const respuesta = await fetch(url);
        const datos = await respuesta.json();

        if (datos.codigo == 1) {
            const actividades = datos.data;
            const totalActividades = actividades.length;
            const usuariosUnicos = [...new Set(actividades.map(a => a.usuario_nombre))].length;
            const modulosActivos = [...new Set(actividades.map(a => a.aud_modulo))].length;

            await Swal.fire({
                title: `📊 Resumen del ${hoy}`,
                html: `
                    <div class="text-start">
                        <p><strong>📈 Total de actividades:</strong> ${totalActividades}</p>
                        <p><strong>👥 Usuarios activos:</strong> ${usuariosUnicos}</p>
                        <p><strong>🔧 Módulos utilizados:</strong> ${modulosActivos}</p>
                        <hr>
                        <small class="text-muted">Datos actualizados en tiempo real</small>
                    </div>
                `,
                icon: 'info',
                confirmButtonText: 'Entendido'
            });
        }
    } catch (error) {
        console.error('Error obteniendo resumen:', error);
    }
}

/**
 * Inicializar DataTable
 */
const datatable = new DataTable('#TableAuditoria', {
    dom: `
        <"row mt-3 justify-content-between" 
            <"col" l> 
            <"col" B> 
            <"col-3" f>
        >
        t
        <"row mt-3 justify-content-between" 
            <"col-md-3 d-flex align-items-center" i> 
            <"col-md-8 d-flex justify-content-end" p>
        >
    `,
    language: lenguaje,
    data: [],
    ordering: false,
    pageLength: 25,
    columns: [
        {
            title: 'No.',
            data: null,
            width: '5%',
            render: (data, type, row, meta) => {
                if (row.esSeparador) return '';
                return row.numeroConsecutivo;
            }
        },
        { 
            title: 'Usuario', 
            data: 'usuario_nombre',
            width: '12%',
            render: (data, type, row, meta) => {
                if (row.esSeparador) {
                    return `<strong class="text-primary fs-5 text-center w-100 d-block">${row.icono} ${row.modulo} (${row.cantidad})</strong>`;
                }
                return data;
            }
        },
        { 
            title: 'Módulo', 
            data: 'aud_modulo',
            width: '10%',
            render: (data, type, row, meta) => {
                if (row.esSeparador) return '';
                return `<span class="badge bg-secondary">${data}</span>`;
            }
        },
        { 
            title: 'Acción', 
            data: 'aud_accion',
            width: '10%',
            render: (data, type, row, meta) => {
                if (row.esSeparador) return '';
                const acciones = {
                    'CREAR': '<span class="badge bg-success">CREAR</span>',
                    'ACTUALIZAR': '<span class="badge bg-warning text-dark">ACTUALIZAR</span>',
                    'ELIMINAR': '<span class="badge bg-danger">ELIMINAR</span>',
                    'INICIAR_SESION': '<span class="badge bg-info">INICIAR SESIÓN</span>',
                    'CERRAR_SESION': '<span class="badge bg-secondary">CERRAR SESIÓN</span>',
                    'CONSULTAR': '<span class="badge bg-primary">CONSULTAR</span>',
                    'ENTREGAR': '<span class="badge bg-warning">ENTREGAR</span>',
                    'APROBAR': '<span class="badge bg-success">APROBAR</span>',
                    'RECHAZAR': '<span class="badge bg-danger">RECHAZAR</span>'
                };
                return acciones[data] || `<span class="badge bg-light text-dark">${data}</span>`;
            }
        },
        { 
            title: 'Descripción', 
            data: 'aud_descripcion',
            width: '25%',
            render: (data, type, row, meta) => {
                if (row.esSeparador) return '';
                return data || 'Sin descripción';
            }
        },
        { 
            title: 'Ruta', 
            data: 'aud_ruta',
            width: '12%',
            render: (data, type, row, meta) => {
                if (row.esSeparador) return '';
                return data ? `<code class="small">${data}</code>` : 'N/A';
            }
        },
        { 
            title: 'IP', 
            data: 'aud_ip',
            width: '8%',
            render: (data, type, row, meta) => {
                if (row.esSeparador) return '';
                return data || 'N/A';
            }
        },
        { 
            title: 'Navegador', 
            data: 'aud_navegador',
            width: '8%',
            render: (data, type, row, meta) => {
                if (row.esSeparador) return '';
                return data || 'N/A';
            }
        },
        { 
            title: 'Fecha', 
            data: 'aud_fecha_creacion',
            width: '8%',
            render: (data, type, row, meta) => {
                if (row.esSeparador) return '';
                return new Date(data).toLocaleString('es-ES');
            }
        },
        {
            title: 'Estado',
            data: 'aud_situacion',
            width: '5%',
            render: (data, type, row, meta) => {
                if (row.esSeparador) return '';
                return data == 1 ? 
                    '<span class="badge bg-success">ACTIVO</span>' : 
                    '<span class="badge bg-danger">INACTIVO</span>';
            }
        }
    ],
    rowCallback: function(row, data) {
        if (data.esSeparador) {
            row.classList.add('table-secondary');
            row.style.backgroundColor = '#f8f9fa';
            row.cells[1].colSpan = 9;
            for (let i = 2; i < row.cells.length; i++) {
                row.cells[i].style.display = 'none';
            }
        }
    }
});

/**
 * Event Listeners
 */
BtnBuscarActividades.addEventListener('click', mostrarTabla);
BtnLimpiarFiltros.addEventListener('click', limpiarFiltros);
BtnEstadisticas.addEventListener('click', cargarEstadisticas);
BtnExportar.addEventListener('click', exportarDatos);
BtnResumen.addEventListener('click', mostrarResumenDia);

// Auto-refresh al cambiar filtros
SelectUsuario.addEventListener('change', () => {
    if (seccionTabla.style.display !== 'none') buscarActividades();
});

SelectModulo.addEventListener('change', () => {
    if (seccionTabla.style.display !== 'none') buscarActividades();
});

SelectAccion.addEventListener('change', () => {
    if (seccionTabla.style.display !== 'none') buscarActividades();
});

InputFechaInicio.addEventListener('change', () => {
    if (seccionTabla.style.display !== 'none') buscarActividades();
});

InputFechaFin.addEventListener('change', () => {
    if (seccionTabla.style.display !== 'none') buscarActividades();
});

document.addEventListener('DOMContentLoaded', () => {
    cargarUsuarios();
});