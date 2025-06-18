<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="<?= asset('build/js/app.js'); ?>"></script>
    <link rel="shortcut icon" href="<?= asset('images/cit.png') ?>" type="image/x-icon">
    <link rel="stylesheet" href="<?= asset('build/styles.css') ?>">
    <title>Sistema de Dotaciones</title>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarToggler" aria-controls="navbarToggler" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <a class="navbar-brand" href="/dashboard">
            <img src="<?= asset('./images/cit.png') ?>" width="35px'" alt="cit">
            Sistema de Dotaciones
        </a>
        <div class="collapse navbar-collapse" id="navbarToggler">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0" style="margin: 0;">
                <li class="nav-item">
                    <a class="nav-link" aria-current="page" href="/dashboard"><i class="bi bi-house-fill me-2"></i>Dashboard</a>
                </li>

                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-people me-2"></i>Usuarios
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark" style="margin: 0;">
                        <li><a class="dropdown-item nav-link text-white" href="/usuarios"><i class="ms-lg-0 ms-2 bi bi-list me-2"></i>Gestión de Usuarios</a></li>
                        <li><a class="dropdown-item nav-link text-white" href="/personal"><i class="ms-lg-0 ms-2 bi bi-person-badge me-2"></i>Personal</a></li>
                        <li><a class="dropdown-item nav-link text-white" href="/roles"><i class="ms-lg-0 ms-2 bi bi-shield me-2"></i>Roles</a></li>
                    </ul>
                </div>

                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-box me-2"></i>Dotaciones
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark" style="margin: 0;">
                        <li><a class="dropdown-item nav-link text-white" href="/dotacion/inventario"><i class="ms-lg-0 ms-2 bi bi-boxes me-2"></i>Inventario</a></li>
                        <li><a class="dropdown-item nav-link text-white" href="/dotacion/solicitudes"><i class="ms-lg-0 ms-2 bi bi-clipboard-check me-2"></i>Solicitudes</a></li>
                        <li><a class="dropdown-item nav-link text-white" href="/dotacion/entregas"><i class="ms-lg-0 ms-2 bi bi-hand-thumbs-up me-2"></i>Entregas</a></li>
                    </ul>
                </div>

                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-graph-up me-2"></i>Reportes
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark" style="margin: 0;">
                        <li><a class="dropdown-item nav-link text-white" href="/reportes/estadisticas"><i class="ms-lg-0 ms-2 bi bi-bar-chart me-2"></i>Estadísticas</a></li>
                        <li><a class="dropdown-item nav-link text-white" href="/auditoria"><i class="ms-lg-0 ms-2 bi bi-clock-history me-2"></i>Auditoría</a></li>
                    </ul>
                </div>
            </ul>
            
            <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle text-white" href="#" data-bs-toggle="dropdown">
                    <?php if(isset($_SESSION['usuario_fotografia']) && $_SESSION['usuario_fotografia']): ?>
                        <img src="/storage/fotosUsuarios/<?php echo $_SESSION['usuario_fotografia']; ?>" width="25" height="25" class="rounded-circle me-2" alt="Avatar">
                    <?php else: ?>
                        <i class="bi bi-person-circle me-2"></i>
                    <?php endif; ?>
                    <?php echo $_SESSION['usuario_nombre'] ?? 'Usuario'; ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end" style="margin: 0;">
                    <li><a class="dropdown-item" href="/perfil"><i class="bi bi-person me-2"></i>Mi Perfil</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="/logout"><i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<div class="progress fixed-bottom" style="height: 6px;">
    <div class="progress-bar progress-bar-animated bg-danger" id="bar" role="progressbar" aria-valuemin="0" aria-valuemax="100"></div>
</div>

<div class="container-fluid pt-5 mb-4" style="min-height: 85vh">
    <?php echo $contenido; ?>
</div>

<div class="container-fluid">
    <div class="row justify-content-center text-center">
        <div class="col-12">
            <p style="font-size:xx-small; font-weight: bold;">
                Sistema de Dotaciones, <?= date('Y') ?> &copy;
            </p>
        </div>
    </div>
</div>
</body>
</html>
