<div class="mb-4">
    <h2><i class="bi bi-hand-thumbs-up me-2"></i>Entregas de Dotación</h2>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="bi bi-clock me-2"></i>Solicitudes Pendientes</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm" id="TablaPendientes">
                    <thead>
                        <tr>
                            <th>Personal</th>
                            <th>Tipo/Talla</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-check-circle me-2"></i>Entregas Realizadas</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm" id="TablaEntregas">
                    <thead>
                        <tr>
                            <th>Personal</th>
                            <th>Tipo/Talla</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="<?= asset('build/js/dotacion/entregas.js'); ?>"></script>