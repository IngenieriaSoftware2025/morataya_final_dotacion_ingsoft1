<div class="container">
    <div class="text-center" style="min-height: 100vh; display: flex; flex-direction: column; justify-content: center; align-items: center;">
        
        <img src="<?= asset('images/cit.png') ?>" alt="Logo MINDEF" class="logo-img">
        
        <h1>Iniciar Sesión</h1>
        
        <?php if(isset($errores) && !empty($errores)): ?>
            <div style="background-color: #dc3545; color: white; padding: 15px; border-radius: 5px; margin: 20px 0; max-width: 400px;">
                <ul style="margin: 0; padding-left: 20px;">
                    <?php foreach($errores as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="index.php?url=login" style="max-width: 400px; width: 100%;">
            <div style="margin-bottom: 20px;">
                <label for="codigo" style="display: block; margin-bottom: 5px; text-align: left;">Código de Usuario:</label>
                <input type="number" id="codigo" name="codigo" required 
                       style="width: 100%; padding: 10px; border: 1px solid #444; background-color: #333; color: white; border-radius: 5px;">
            </div>
            
            <div style="margin-bottom: 30px;">
                <label for="password" style="display: block; margin-bottom: 5px; text-align: left;">Contraseña:</label>
                <input type="password" id="password" name="password" required
                       style="width: 100%; padding: 10px; border: 1px solid #444; background-color: #333; color: white; border-radius: 5px;">
            </div>
            
            <button type="submit" class="btn-red" style="width: 100%; padding: 12px;">
                Iniciar Sesión
            </button>
        </form>
        
        <div style="margin-top: 20px;">
            <a href="../index.php" style="color: #cccccc; text-decoration: none;">
                ← Volver al inicio
            </a>
        </div>
        
    </div>
</div>