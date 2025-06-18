<div class="mb-4">
    <h2><i class="bi bi-person-plus me-2"></i>Crear Usuario</h2>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/usuarios">Usuarios</a></li>
            <li class="breadcrumb-item active">Crear</li>
        </ol>
    </nav>
</div>

<div class="card shadow">
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data" id="FormUsuario">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Nombre Completo <span class="text-danger">*</span></label>
                        <input type="text" name="usu_nombre" class="form-control" required>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Código de Usuario <span class="text-danger">*</span></label>
                        <input type="number" name="usu_codigo" class="form-control" required>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Correo Electrónico</label>
                        <input type="email" name="usu_correo" class="form-control">
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Contraseña <span class="text-danger">*</span></label>
                        <input type="password" name="usu_password" class="form-control" required>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Fotografía</label>
                        <input type="file" name="usu_fotografia" class="form-control" accept="image/*">
                        <div class="form-text">Formatos permitidos: JPG, PNG, GIF (máximo 5MB)</div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Roles</label>
                        <div id="RolesContainer">
                            <!-- Se cargan dinámicamente -->
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-2"></i>Guardar Usuario
                </button>
                <a href="/usuarios" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<script src="<?= asset('build/js/usuarios/crear.js'); ?>"></script>
