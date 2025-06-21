<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-hand-thumbs-up me-2"></i>Entregas de Dotación</h2>
    <button class="btn btn-primary btn-sm" onclick="cargarDatos()" title="Actualizar datos">
        <i class="bi bi-arrow-clockwise"></i> Actualizar
    </button>
</div>

<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card shadow">
            <div class="card-header bg-warning text-dark">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-clock me-2"></i>Solicitudes Pendientes
                    </h5>
                    <span class="badge bg-dark" id="contadorPendientes">0</span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-hover mb-0" id="TablaPendientes">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>Personal</th>
                                <th>Dotación</th>
                                <th width="120">Acción</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-check-circle me-2"></i>Entregas Realizadas
                    </h5>
                    <span class="badge bg-light text-dark" id="contadorEntregas">0</span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-hover mb-0" id="TablaEntregas">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>Personal</th>
                                <th>Dotación</th>
                                <th>Entrega</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card bg-light">
            <div class="card-body py-2">
                <div class="row text-center">
                    <div class="col-md-3">
                        <small class="text-muted">Última actualización:</small><br>
                        <span class="fw-bold" id="ultimaActualizacion">-</span>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">Pendientes:</small><br>
                        <span class="fw-bold text-warning" id="totalPendientes">0</span>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">Entregadas hoy:</small><br>
                        <span class="fw-bold text-success" id="entregasHoy">0</span>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">Estado:</small><br>
                        <span class="badge bg-success" id="estadoSistema">
                            <i class="bi bi-check-circle"></i> Operativo
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= asset('build/js/entregas/index.js'); ?>"></script>