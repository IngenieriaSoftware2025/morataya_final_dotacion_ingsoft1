<div class="container">
    <div style="padding: 40px 20px;">

        <div style="background-color: #1a1a1a; padding: 30px; border-radius: 10px; margin-bottom: 30px;">
            <h1 style="color: #ffffff; margin-bottom: 10px;">¡Bienvenido al Sistema de Dotaciones!</h1>
            <p style="color: #cccccc; font-size: 1.2rem;">
                Usuario: <strong><?= $_SESSION['usuario_nombre'] ?? 'Administrador' ?></strong>
                (Código: <?= $_SESSION['usuario_codigo'] ?? 'N/A' ?>)
            </p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">

            <div style="background-color: #dc3545; padding: 20px; border-radius: 8px; text-align: center;">
                <h3 style="color: white; margin-bottom: 10px;">📦 Inventario</h3>
                <p style="color: white; margin-bottom: 15px;">Gestionar stock de dotaciones</p>
                <a href="index.php?url=dotacion/inventario" style="color: white; text-decoration: underline;">Ver Inventario</a>
            </div>

            <div style="background-color: #28a745; padding: 20px; border-radius: 8px; text-align: center;">
                <h3 style="color: white; margin-bottom: 10px;">📋 Solicitudes</h3>
                <p style="color: white; margin-bottom: 15px;">Procesar solicitudes de personal</p>
                <a href="index.php?url=solicitudes" style="color: white; text-decoration: underline;">Ver Solicitudes</a>
            </div>

            <div style="background-color: #17a2b8; padding: 20px; border-radius: 8px; text-align: center;">
                <h3 style="color: white; margin-bottom: 10px;">🤝 Entregas</h3>
                <p style="color: white; margin-bottom: 15px;">Gestionar entregas realizadas</p>
                <a href="index.php?url=entregas" style="color: white; text-decoration: underline;">Ver Entregas</a>
            </div>

            <div style="background-color: #6f42c1; padding: 20px; border-radius: 8px; text-align: center;">
                <h3 style="color: white; margin-bottom: 10px;">👥 Usuarios</h3>
                <p style="color: white; margin-bottom: 15px;">Administrar usuarios del sistema</p>
                <a href="index.php?url=usuarios" style="color: white; text-decoration: underline;">Ver Usuarios</a>
            </div>

            <div style="background-color: #fd7e14; padding: 20px; border-radius: 8px; text-align: center;">
                <h3 style="color: white; margin-bottom: 10px;">📊 Reportes</h3>
                <p style="color: white; margin-bottom: 15px;">Estadísticas y reportes</p>
                <a href="index.php?url=reportes" style="color: white; text-decoration: underline;">Ver Reportes</a>
            </div>

            <div style="background-color: #6c757d; padding: 20px; border-radius: 8px; text-align: center;">
                <h3 style="color: white; margin-bottom: 10px;">🔒 Cerrar Sesión</h3>
                <p style="color: white; margin-bottom: 15px;">Salir del sistema</p>
                <a href="index.php?url=logout" style="color: white; text-decoration: underline;">Cerrar Sesión</a>
            </div>

        </div>

        <div style="margin-top: 40px; text-align: center;">
            <p style="color: #666666;">
                Sistema de Dotaciones MINDEF - Versión 1.0<br>
                Sesión iniciada: <?= date('d/m/Y H:i') ?>
            </p>
        </div>

    </div>
</div>