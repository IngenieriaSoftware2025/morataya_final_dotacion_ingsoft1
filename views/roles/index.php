<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-shield me-2"></i>Roles</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ModalRol">
        <i class="bi bi-plus-circle me-2"></i>Nuevo Rol
    </button>
</div>

<div class="card shadow">
    <div class="card-body">
        <table class="table table-striped table-hover" id="TablaRoles">
            <thead class="table-dark">
                <tr>
                    <th>Nombre</th>
                    <th>Nombre Corto</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="ModalRol" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tituloModal">Nuevo Rol</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="FormRol">
                    <input type="hidden" name="rol_id" id="rol_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Nombre del Rol <span class="text-danger">*</span></label>
                        <input type="text" name="rol_nombre" id="rol_nombre" class="form-control" required maxlength="75">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nombre Corto</label>
                        <input type="text" name="rol_nombre_ct" id="rol_nombre_ct" class="form-control" maxlength="25">
                        <div class="form-text">Opcional - Para mostrar en interfaces compactas</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" form="FormRol" class="btn btn-primary">
                    <i class="bi bi-save me-2"></i>Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<script src="<?= asset('build/js/roles/index.js'); ?>"></script>