<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-person-badge me-2"></i>Personal</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ModalPersonal">
        <i class="bi bi-plus-circle me-2"></i>Nuevo Personal
    </button>
</div>

<div class="card shadow">
    <div class="card-body">
        <table class="table table-striped table-hover" id="TablaPersonal">
            <thead class="table-dark">
                <tr>
                    <th>Nombre</th>
                    <th>CUI</th>
                    <th>Puesto</th>
                    <th>Fecha Ingreso</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="ModalPersonal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tituloModal">Nuevo Personal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="FormPersonal">
                    <input type="hidden" name="personal_id" id="personal_id">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Nombre Completo <span class="text-danger">*</span></label>
                                <input type="text" name="personal_nombre" id="personal_nombre" class="form-control" required maxlength="100">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">CUI <span class="text-danger">*</span></label>
                                <input type="text" name="personal_cui" id="personal_cui" class="form-control" required 
                                       pattern="[0-9]{13}" maxlength="13" placeholder="1234567890123">
                                <div class="form-text">Debe tener exactamente 13 dígitos</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Puesto <span class="text-danger">*</span></label>
                                <input type="text" name="personal_puesto" id="personal_puesto" class="form-control" required maxlength="100">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Fecha de Ingreso</label>
                                <input type="date" name="personal_fecha_ingreso" id="personal_fecha_ingreso" class="form-control">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" form="FormPersonal" class="btn btn-primary">Guardar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="ModalEditarPersonal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Personal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="FormEditarPersonal">
                    <input type="hidden" name="personal_id" id="edit_personal_id">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Nombre Completo <span class="text-danger">*</span></label>
                                <input type="text" name="personal_nombre" id="edit_personal_nombre" class="form-control" required maxlength="100">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">CUI <span class="text-danger">*</span></label>
                                <input type="text" name="personal_cui" id="edit_personal_cui" class="form-control" required 
                                       pattern="[0-9]{13}" maxlength="13">
                                <div class="form-text">Debe tener exactamente 13 dígitos</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Puesto <span class="text-danger">*</span></label>
                                <input type="text" name="personal_puesto" id="edit_personal_puesto" class="form-control" required maxlength="100">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Fecha de Ingreso</label>
                                <input type="date" name="personal_fecha_ingreso" id="edit_personal_fecha_ingreso" class="form-control">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" form="FormEditarPersonal" class="btn btn-primary">Actualizar</button>
            </div>
        </div>
    </div>
</div>

<script src="<?= asset('build/js/personal/index.js'); ?>"></script>