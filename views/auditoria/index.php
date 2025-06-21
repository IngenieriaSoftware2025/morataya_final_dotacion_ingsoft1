<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-clock-history me-2"></i>Auditoría del Sistema</h2>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-secondary" onclick="cargarAuditoria()">
            <i class="bi bi-arrow-clockwise me-2"></i>Actualizar
        </button>
        <button class="btn btn-info" onclick="mostrarEstadisticas()">
            <i class="bi bi-graph-up me-2"></i>Estadísticas
        </button>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <label class="form-label">Usuario</label>
                <select id="filtroUsuario" class="form-select">
                    <option value="">Todos los usuarios</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Módulo</label>
                <select id="filtroModulo" class="form-select">
                    <option value="">Todos los módulos</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Fecha Inicio</label>
                <input type="date" id="fechaInicio" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label">Fecha Fin</label>
                <input type="date" id="fechaFin" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary" onclick="filtrarAuditoria()">
                        <i class="bi bi-search me-2"></i>Buscar
                    </button>
                    <button class="btn btn-outline-secondary" onclick="limpiarFiltros()">
                        <i class="bi bi-x me-2"></i>Limpiar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Estadísticas rápidas -->
<div class="row mb-4" id="estadisticas" style="display: none;">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h4 class="card-title" id="totalRegistros">0</h4>
                <p class="card-text">Total Registros</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h4 class="card-title" id="moduloMasUsado">-</h4>
                <p class="card-text">Módulo Más Usado</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h4 class="card-title" id="actividadHoy">0</h4>
                <p class="card-text">Actividad Hoy</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h4 class="card-title" id="ultimaSemana">0</h4>
                <p class="card-text">Última Semana</p>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de auditoría -->
<div class="card shadow">
    <div class="card-header bg-white">
        <h5 class="mb-0">
            <i class="bi bi-table me-2"></i>Registro de Auditoría
            <span class="badge bg-secondary ms-2" id="contadorRegistros">0 registros</span>
        </h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover" id="TablaAuditoria">
                <thead class="table-dark">
                    <tr>
                        <th>Fecha</th>
                        <th>Usuario</th>
                        <th>Módulo</th>
                        <th>Acción</th>
                        <th>Descripción</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="6" class="text-center">Cargando datos...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="<?= asset('build/js/auditoria/index.js'); ?>"></script>