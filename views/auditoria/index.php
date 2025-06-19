<div class="mb-4">
    <h2><i class="bi bi-clock-history me-2"></i>Auditoría del Sistema</h2>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h5>Total Acciones</h5>
                <h3 id="totalAcciones">0</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h5>Hoy</h5>
                <h3 id="accionesHoy">0</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h5>Esta Semana</h5>
                <h3 id="accionesSemana">0</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h5>Este Mes</h5>
                <h3 id="accionesMes">0</h3>
            </div>
        </div>
    </div>
</div>

<div class="card shadow">
    <div class="card-header">
        <h5 class="mb-0">Registro de Auditoría</h5>
    </div>
    <div class="card-body">
        <table class="table table-striped table-hover" id="TablaAuditoria">
            <thead class="table-dark">
                <tr>
                    <th>Fecha</th>
                    <th>Usuario</th>
                    <th>Módulo</th>
                    <th>Acción</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    cargarAuditoria();
    cargarResumen();
});

let tablaAuditoria;

async function cargarAuditoria() {
    try {
        const respuesta = await fetch('/auditoria/obtenerAPI');
        const auditoria = await respuesta.json();
        
        if(tablaAuditoria) {
            tablaAuditoria.destroy();
        }
        
        mostrarAuditoria(auditoria);
        inicializarDataTable();
    } catch (error) {
        console.error('Error al cargar auditoría:', error);
    }
}

async function cargarResumen() {
    try {
        const respuesta = await fetch('/auditoria/resumenAPI');
        const resumen = await respuesta.json();
        
        if(resumen.total_acciones) {
            document.getElementById('totalAcciones').textContent = resumen.total_acciones;
        }
    } catch (error) {
        console.error('Error al cargar resumen:', error);
    }
}

function mostrarAuditoria(auditoria) {
    const tbody = document.querySelector('#TablaAuditoria tbody');
    tbody.innerHTML = '';
    
    auditoria.forEach(registro => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${registro.aud_fecha}</td>
            <td>${registro.usu_nombre} (${registro.usu_codigo})</td>
            <td><span class="badge bg-secondary">${registro.aud_modulo}</span></td>
            <td>${registro.aud_accion}</td>
            <td>${registro.aud_ip}</td>
        `;
        tbody.appendChild(tr);
    });
}

function inicializarDataTable() {
    if (typeof $ !== 'undefined' && $.fn.DataTable) {
        tablaAuditoria = $('#TablaAuditoria').DataTable({
            responsive: true,
            pageLength: 25,
            order: [[0, 'desc']], 
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
            }
        });
    }
}
</script>