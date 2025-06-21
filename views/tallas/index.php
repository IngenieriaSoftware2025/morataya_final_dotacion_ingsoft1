<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-rulers me-2"></i>Tallas</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ModalTalla">
        <i class="bi bi-plus-circle me-2"></i>Nueva Talla
    </button>
</div>
<div class="card shadow">
    <div class="card-body">
        <table class="table table-striped table-hover" id="TablaTallas">
            <thead class="table-dark">
                <tr>
                    <th>Etiqueta</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="TablaTallasBody"></tbody>
        </table>
    </div>
</div>
<div class="modal fade" id="ModalTalla" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nueva Talla</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="FormTalla">
                    <div class="mb-3">
                        <label class="form-label">Etiqueta <span class="text-danger">*</span></label>
                        <input type="text" name="talla_etiqueta" class="form-control" required maxlength="10" 
                               placeholder="Ej: S, M, L, XL o 38, 40, 42">
                        <div class="form-text">
                            Formatos válidos: XS, S, M, L, XL, XXL, XXXL o números de 1-60
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" form="FormTalla" class="btn btn-primary">Guardar</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="ModalEditarTalla" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Talla</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="FormEditarTalla">
                    <input type="hidden" name="talla_id" id="edit_talla_id">
                    <div class="mb-3">
                        <label class="form-label">Etiqueta <span class="text-danger">*</span></label>
                        <input type="text" name="talla_etiqueta" id="edit_talla_etiqueta" class="form-control" required maxlength="10" 
                               placeholder="Ej: S, M, L, XL o 38, 40, 42">
                        <div class="form-text">
                            Formatos válidos: XS, S, M, L, XL, XXL, XXXL o números de 1-60
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" form="FormEditarTalla" class="btn btn-primary">Actualizar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= asset('build/js/tallas/index.js'); ?>"></script>