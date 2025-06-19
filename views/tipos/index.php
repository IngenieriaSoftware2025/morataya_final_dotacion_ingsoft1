<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-tags me-2"></i>Tipos de Dotación</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ModalTipo">
        <i class="bi bi-plus-circle me-2"></i>Nuevo Tipo
    </button>
</div>

<div class="card shadow">
    <div class="card-body">
        <table class="table table-striped table-hover" id="TablaTipos">
            <thead class="table-dark">
                <tr>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="ModalTipo" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tituloModal">Nuevo Tipo de Dotación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="FormTipo">
                    <input type="hidden" name="tipo_id" id="tipo_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="tipo_nombre" id="tipo_nombre" class="form-control" required maxlength="50">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea name="tipo_descripcion" id="tipo_descripcion" class="form-control" rows="3" maxlength="100"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" form="FormTipo" class="btn btn-primary">
                    <i class="bi bi-save me-2"></i>Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<script src="<?= asset('build/js/tipos/index.js'); ?>"></script>