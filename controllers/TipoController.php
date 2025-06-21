<?php

namespace Controllers;

use MVC\Router;
use Model\TipoDotacion;
use Model\Auditoria;
use Exception;

class TipoController
{

    public static function index(Router $router)
    {
        AuthController::verificarLogin();
        $router->render('tipos/index', []);
    }

    public static function obtenerAPI()
    {
        if (ob_get_level()) {
            ob_clean();
        }

        header('Content-Type: application/json; charset=utf-8');
        AuthController::verificarLogin();

        try {
            $tipos = TipoDotacion::where('tipo_situacion', 1);
            echo json_encode($tipos, JSON_UNESCAPED_UNICODE);
            exit;
        } catch (Exception $e) {
            echo json_encode([
                'resultado' => false,
                'mensaje' => 'Error al obtener tipos de dotación: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    public static function guardarAPI()
    {
        if (ob_get_level()) {
            ob_clean();
        }

        header('Content-Type: application/json; charset=utf-8');
        AuthController::verificarLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['resultado' => false, 'mensaje' => 'Método no permitido'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $errores = [];

        $id = isset($_POST['tipo_id']) ? (int)$_POST['tipo_id'] : 0;
        $nombre = isset($_POST['tipo_nombre']) ? trim($_POST['tipo_nombre']) : '';
        $descripcion = isset($_POST['tipo_descripcion']) ? trim($_POST['tipo_descripcion']) : '';

        if (strlen(trim($nombre)) < 3 || strlen(trim($nombre)) > 50) {
            $errores[] = 'El nombre debe tener entre 3 y 50 caracteres';
        }

        if ($descripcion && strlen(trim($descripcion)) > 100) {
            $errores[] = 'La descripción no puede exceder 100 caracteres';
        }

        $query = "SELECT COUNT(*) as total FROM morataya_tipos_dotacion WHERE tipo_nombre = '{$nombre}' AND tipo_situacion = 1";
        if ($id > 0) {
            $query .= " AND tipo_id != {$id}";
        }
        $resultado = TipoDotacion::fetchFirst($query);
        if (($resultado['total'] ?? 0) > 0) {
            $errores[] = 'Ya existe un tipo de dotación con ese nombre';
        }

        if (empty($errores)) {
            try {
                if ($id > 0) {
                    $sqlUpdate = "UPDATE morataya_tipos_dotacion SET 
                        tipo_nombre = '{$nombre}',
                        tipo_descripcion = '{$descripcion}'
                        WHERE tipo_id = {$id}";
                    $resultado = TipoDotacion::SQL($sqlUpdate);
                    $exito = true;
                    $accion = 'Actualización';
                } else {
                    $tipo = new TipoDotacion([
                        'tipo_nombre' => $nombre,
                        'tipo_descripcion' => $descripcion
                    ]);

                    $resultado = $tipo->guardar();
                    $exito = $resultado['resultado'] ?? false;
                    $accion = 'Creación';
                }

                if ($exito) {
                    try {
                        $auditoria = new Auditoria([
                            'usu_id' => $_SESSION['usuario_id'] ?? 1,
                            'aud_usuario_nombre' => $_SESSION['usuario_nombre'] ?? 'Sistema',
                            'aud_modulo' => 'Tipos de Dotación',
                            'aud_accion' => $accion . ' de tipo: ' . $nombre,
                            'aud_descripcion' => $accion . ' del tipo de dotación: ' . $nombre,
                            'aud_ruta' => $_SERVER['REQUEST_URI'] ?? '/tipos',
                            'aud_ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                            'aud_navegador' => substr($_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido', 0, 100)
                        ]);
                        $auditoria->guardar();
                    } catch (Exception $e) {
                        error_log("Error en auditoría: " . $e->getMessage());
                    }

                    echo json_encode([
                        'resultado' => true,
                        'mensaje' => 'Tipo de dotación ' . ($id > 0 ? 'actualizado' : 'creado') . ' correctamente'
                    ], JSON_UNESCAPED_UNICODE);
                    exit;
                }
            } catch (Exception $e) {
                $errores[] = 'Error al guardar: ' . $e->getMessage();
            }
        }

        echo json_encode([
            'resultado' => false,
            'mensaje' => 'Errores de validación',
            'errores' => $errores
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function eliminarAPI()
    {
        if (ob_get_level()) {
            ob_clean();
        }

        header('Content-Type: application/json; charset=utf-8');
        AuthController::verificarLogin();

        if (!isset($_GET['tipo_id'])) {
            echo json_encode(['resultado' => false, 'mensaje' => 'ID no proporcionado'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $id = filter_var($_GET['tipo_id'], FILTER_VALIDATE_INT);

        if (!$id) {
            echo json_encode(['resultado' => false, 'mensaje' => 'ID no válido'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        try {
            $queryInventario = "SELECT COUNT(*) as total FROM morataya_inventario_dotacion WHERE tipo_id = {$id} AND inv_situacion = 1";
            $inventario = TipoDotacion::fetchFirst($queryInventario);

            $querySolicitudes = "SELECT COUNT(*) as total FROM morataya_solicitudes_dotacion WHERE tipo_id = {$id} AND solicitud_situacion = 1";
            $solicitudes = TipoDotacion::fetchFirst($querySolicitudes);

            if (($inventario['total'] ?? 0) > 0 || ($solicitudes['total'] ?? 0) > 0) {
                echo json_encode([
                    'resultado' => false,
                    'mensaje' => 'No se puede eliminar este tipo porque está siendo usado en inventario o solicitudes'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
            $query = "SELECT * FROM morataya_tipos_dotacion WHERE tipo_id = {$id} AND tipo_situacion = 1";
            $tipo = TipoDotacion::fetchFirst($query);

            if (!$tipo) {
                echo json_encode(['resultado' => false, 'mensaje' => 'Tipo no encontrado'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $nombre = $tipo['tipo_nombre'];

            $sqlUpdate = "UPDATE morataya_tipos_dotacion SET tipo_situacion = 0 WHERE tipo_id = {$id}";
            $resultado = TipoDotacion::SQL($sqlUpdate);

            if ($resultado) {
                try {
                    $auditoria = new Auditoria([
                        'usu_id' => $_SESSION['usuario_id'] ?? 1,
                        'aud_usuario_nombre' => $_SESSION['usuario_nombre'] ?? 'Sistema',
                        'aud_modulo' => 'Tipos de Dotación',
                        'aud_accion' => 'Eliminación de tipo: ' . $nombre,
                        'aud_descripcion' => 'Eliminación del tipo de dotación: ' . $nombre,
                        'aud_ruta' => $_SERVER['REQUEST_URI'] ?? '/tipos',
                        'aud_ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                        'aud_navegador' => substr($_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido', 0, 100)
                    ]);
                    $auditoria->guardar();
                } catch (Exception $e) {
                    error_log("Error en auditoría: " . $e->getMessage());
                }

                echo json_encode([
                    'resultado' => true,
                    'mensaje' => 'Tipo eliminado correctamente'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
        } catch (Exception $e) {
            echo json_encode([
                'resultado' => false,
                'mensaje' => 'Error al eliminar tipo',
                'detalle' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
}
