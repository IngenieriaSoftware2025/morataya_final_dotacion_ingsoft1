<div class="row justify-content-center align-items-center min-vh-100">
    <div class="col-md-4">
        <div class="card shadow-lg border-0">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <img src="<?= asset('images/cit.png') ?>" width="80" alt="Logo" class="mb-3">
                    <h3 class="text-primary">Sistema de Dotaciones</h3>
                    <p class="text-muted">Inicia sesión con tu cuenta</p>
                </div>
                
                <?php if(isset($errores) && !empty($errores)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach($errores as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form method="POST" id="FormLogin">
                    <div class="mb-3">
                        <label class="form-label">Código de Usuario</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="number" name="codigo" class="form-control" placeholder="Ingresa tu código" required>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">Contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" name="password" class="form-control" placeholder="Ingresa tu contraseña" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Iniciar Sesión
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="<?= asset('build/js/auth/index.js'); ?>"></script>
