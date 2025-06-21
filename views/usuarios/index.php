<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-people me-2"></i>Gestión de Usuarios</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ModalUsuario">
        <i class="bi bi-plus-circle me-2"></i>Nuevo Usuario
    </button>
</div>

<div class="card shadow">
    <div class="card-body">
        <table class="table table-striped table-hover" id="TablaUsuarios">
            <thead class="table-dark">
                <tr>
                    <th>Foto</th>
                    <th>Nombre</th>
                    <th>Código</th>
                    <th>Correo</th>
                    <th>Roles</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="ModalUsuario" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tituloModal">Nuevo Usuario</h5>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ModalUsuario" onclick="nuevoUsuario()">
                    <i class="bi bi-plus-circle me-2"></i>Nuevo Usuario
                </button>
            </div>
            <div class="modal-body">
                <form method="POST" enctype="multipart/form-data" id="FormUsuario">
                    <input type="hidden" name="usu_id" id="usu_id">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Nombre Completo <span class="text-danger">*</span></label>
                                <input type="text" name="usu_nombre" id="usu_nombre" class="form-control" required maxlength="100">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Código de Usuario <span class="text-danger">*</span></label>
                                <input type="number" name="usu_codigo" id="usu_codigo" class="form-control" required min="1000" max="999999">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Correo Electrónico</label>
                                <input type="email" name="usu_correo" id="usu_correo" class="form-control" maxlength="100">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Contraseña <span class="text-danger" id="passwordRequired">*</span></label>
                                <input type="password" name="usu_password" id="usu_password" class="form-control" minlength="6">
                                <div class="form-text" id="passwordHelp">Mínimo 6 caracteres</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Fotografía</label>
                                <input type="file" name="usu_fotografia" id="usu_fotografia" class="form-control" accept="image/*">
                                <div class="form-text">Formatos permitidos: JPG, PNG, GIF (máximo 5MB)</div>
                                <div id="previewFoto" class="mt-2"></div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Roles</label>
                                <div id="RolesContainer" class="border p-3 rounded" style="max-height: 150px; overflow-y: auto;">
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" form="FormUsuario" class="btn btn-primary">
                    <i class="bi bi-save me-2"></i>Guardar Usuario
                </button>
            </div>
        </div>
    </div>
</div>

<script src="<?= asset('build/js/usuarios/index.js'); ?>"></script>