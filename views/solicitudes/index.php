<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-clipboard-check me-2"></i>Solicitudes de Dotación</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ModalSolicitud">
        <i class="bi bi-plus-circle me-2"></i>Nueva Solicitud
    </button>
</div>

<div class="card shadow">
    <div class="card-body">
        <table class="table table-striped table-hover" id="TablaSolicitudes">
            <thead class="table-dark">
                <tr>
                    <th>Personal</th>
                    <th>Tipo</th>
                    <th>Talla</th>
                    <th>Fecha Solicitud</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="ModalSolicitud" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nueva Solicitud</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="FormSolicitud">
                    <div class="mb-3">
                        <label class="form-label">Personal</label>
                        <select name="personal_id" class="form-select" required>
                            <option value="">Seleccionar...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tipo de Dotación</label>
                        <select name="tipo_id" class="form-select" required>
                            <option value="">Seleccionar...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Talla</label>
                        <select name="talla_id" class="form-select" required>
                            <option value="">Seleccionar...</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" form="FormSolicitud" class="btn btn-primary">Guardar</button>
            </div>
        </div>
    </div>
</div>

<script src="<?= asset('build/js/solicitudes/index.js'); ?>"></script>