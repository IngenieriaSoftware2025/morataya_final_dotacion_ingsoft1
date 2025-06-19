<?php 
require_once __DIR__ . '/../includes/app.php';

use MVC\Router;
use Controllers\AppController;
use Controllers\AuthController;
use Controllers\EstadisticaController;
use Controllers\UsuarioController;
use Controllers\RolController;
use Controllers\DotacionController;
use Controllers\PersonalController;
use Controllers\TipoController;
use Controllers\TallaController;
use Controllers\ReportesController;
use Controllers\AuditoriaController;

$router = new Router();
$router->setBaseURL('/' . $_ENV['APP_NAME']);

// Página principal
$router->get('/', [AppController::class, 'index']);

// Login
$router->get('/login', [AuthController::class, 'index']);
$router->post('/login', [AuthController::class, 'index']);

// Logout
$router->get('/logout', [AuthController::class, 'logout']);

// Panel principal era "/estasditicas" 
$router->get('/dashboard', [EstadisticaController::class, 'index']);
$router->get('/estadisticas', [EstadisticaController::class, 'index']);
$router->get('/estadisticas/obtenerAPI', [EstadisticaController::class, 'obtenerEstadisticasAPI']);

// Usuarios
$router->get('/usuarios', [UsuarioController::class, 'index']);

// APIs de Usuarios
$router->get('/usuarios/obtenerAPI', [UsuarioController::class, 'obtenerAPI']);
$router->get('/usuarios/obtenerPorIdAPI', [UsuarioController::class, 'obtenerPorIdAPI']);
$router->post('/usuarios/guardarAPI', [UsuarioController::class, 'guardarAPI']);
$router->post('/usuarios/actualizarAPI', [UsuarioController::class, 'actualizarAPI']);
$router->get('/usuarios/eliminarAPI', [UsuarioController::class, 'eliminarAPI']);

// Personal
$router->get('/personal', [PersonalController::class, 'index']);

// APIs de Personal
$router->get('/personal/obtenerAPI', [PersonalController::class, 'obtenerAPI']);
$router->post('/personal/guardarAPI', [PersonalController::class, 'guardarAPI']);
$router->get('/personal/eliminarAPI', [PersonalController::class, 'eliminarAPI']);

// Roles
$router->get('/roles', [RolController::class, 'index']);

// APIs de Roles
$router->get('/roles/obtenerAPI', [RolController::class, 'obtenerAPI']);
$router->post('/roles/guardarAPI', [RolController::class, 'guardarAPI']);

// Inventario
$router->get('/dotacion/inventario', [DotacionController::class, 'inventario']);
$router->get('/dotacion/obtenerInventarioAPI', [DotacionController::class, 'obtenerInventarioAPI']);
$router->post('/dotacion/guardarInventarioAPI', [DotacionController::class, 'guardarInventarioAPI']);

// Solicitudes
$router->get('/dotacion/solicitudes', [DotacionController::class, 'solicitudes']);
$router->get('/dotacion/obtenerSolicitudesAPI', [DotacionController::class, 'obtenerSolicitudesAPI']);
$router->get('/dotacion/obtenerSolicitudesPendientesAPI', [DotacionController::class, 'obtenerSolicitudesPendientesAPI']);
$router->post('/dotacion/guardarSolicitudAPI', [DotacionController::class, 'guardarSolicitudAPI']);

// Entregas
$router->get('/dotacion/entregas', [DotacionController::class, 'entregas']);
$router->get('/dotacion/obtenerEntregasAPI', [DotacionController::class, 'obtenerEntregasAPI']);
$router->post('/dotacion/procesarEntregaAPI', [DotacionController::class, 'procesarEntregaAPI']);

// Tipos de Dotación
$router->get('/tipos', [TipoController::class, 'index']);
$router->get('/tipos/obtenerAPI', [TipoController::class, 'obtenerAPI']);
$router->post('/tipos/guardarAPI', [TipoController::class, 'guardarAPI']);
$router->get('/tipos/eliminarAPI', [TipoController::class, 'eliminarAPI']);

// Tallas
$router->get('/tallas', [TallaController::class, 'index']);
$router->get('/tallas/obtenerAPI', [TallaController::class, 'obtenerAPI']);
$router->post('/tallas/guardarAPI', [TallaController::class, 'guardarAPI']);
$router->get('/tallas/eliminarAPI', [TallaController::class, 'eliminarAPI']);

// Reportes
$router->get('/reportes/estadisticas', [ReportesController::class, 'estadisticas']);
$router->get('/reportes/dotaciones', [ReportesController::class, 'dotaciones']);
$router->get('/reportes/inventario', [ReportesController::class, 'inventario']);

// APIs de Reportes
$router->get('/reportes/estadisticasAPI', [ReportesController::class, 'estadisticasAPI']);
$router->get('/reportes/dotacionesAPI', [ReportesController::class, 'dotacionesAPI']);
$router->get('/reportes/inventarioAPI', [ReportesController::class, 'inventarioAPI']);
$router->get('/reportes/entregasPorMesAPI', [ReportesController::class, 'entregasPorMesAPI']);
$router->get('/reportes/entregasPorTallaAPI', [ReportesController::class, 'entregasPorTallaAPI']);

// Auditoría
$router->get('/auditoria', [AuditoriaController::class, 'renderizarPagina']);
$router->get('/auditoria/buscarAPI', [AuditoriaController::class, 'buscarAPI']);
$router->get('/auditoria/buscarUsuariosAPI', [AuditoriaController::class, 'buscarUsuariosAPI']);
$router->get('/auditoria/estadisticasAPI', [AuditoriaController::class, 'estadisticasAPI']);

// Perfil
$router->get('/perfil', [UsuarioController::class, 'perfil']);
$router->post('/perfil/actualizarAPI', [UsuarioController::class, 'actualizarPerfilAPI']);

// Comprueba y valida las rutas, que existan y les asigna las funciones del Controlador
$router->comprobarRutas();