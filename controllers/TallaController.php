<?php

namespace Controllers;

use MVC\Router;
use Model\Talla;
use Model\Auditoria;
use Exception;

class TallaController
{
    public static function index(Router $router)
    {
        AuthController::verificarLogin();
        $router->render('tallas/index', []);
    }

    public static function obtenerAPI()
    {
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            AuthController::verificarLogin();
            $tallas = Talla::where('talla_situacion', 1);
            echo json_encode($tallas, JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            echo json_encode([
                'resultado' => false,
                'mensaje' => 'Error al obtener tallas: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    public static function guardarAPI()
    {
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            AuthController::verificarLogin();

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['resultado' => false, 'mensaje' => 'Método no permitido'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $errores = [];
            $etiqueta = isset($_POST['talla_etiqueta']) ? trim($_POST['talla_etiqueta']) : '';
            $tallaId = isset($_POST['talla_id']) ? intval($_POST['talla_id']) : null;

            if (strlen(trim($etiqueta)) < 1 || strlen(trim($etiqueta)) > 10) {
                $errores[] = 'La etiqueta debe tener entre 1 y 10 caracteres';
            }

            $etiqueta = strtoupper(trim($etiqueta));

            $formatoValido = false;
            if (in_array($etiqueta, ['XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL'])) {
                $formatoValido = true;
            }
            if (is_numeric($etiqueta) && $etiqueta >= 1 && $etiqueta <= 60) {
                $formatoValido = true;
            }

            if (!$formatoValido) {
                $errores[] = 'Formato de talla no válido. Use letras (XS, S, M, L, XL, XXL, XXXL) o números (1-60)';
            }

            if (empty($errores)) {
                try {
                    if ($tallaId) {
                        $query = "SELECT FIRST 1 talla_id, talla_situacion FROM morataya_tallas WHERE UPPER(talla_etiqueta) = UPPER('{$etiqueta}') AND talla_id <> {$tallaId}";
                    } else {
                        $query = "SELECT FIRST 1 talla_id, talla_situacion FROM morataya_tallas WHERE UPPER(talla_etiqueta) = UPPER('{$etiqueta}')";
                    }
                    
                    $resultado = Talla::fetchFirst($query);
                    if ($resultado && isset($resultado['talla_id'])) {
                        if ($resultado['talla_situacion'] == 1) {
                            $errores[] = 'Ya existe una talla activa con la etiqueta: ' . $etiqueta;
                        } else {
                            $reactivarTalla = $resultado['talla_id'];
                        }
                    }
                } catch (Exception $e) {
                    $errores[] = 'Error al verificar duplicados: ' . $e->getMessage();
                }
            }

            if (empty($errores)) {
                try {
                    if ($tallaId) {
                        $sqlUpdate = "UPDATE morataya_tallas SET talla_etiqueta = '{$etiqueta}' WHERE talla_id = {$tallaId}";
                        $resultado = Talla::SQL($sqlUpdate);
                        $success = $resultado !== false;
                    } elseif (isset($reactivarTalla)) {
                        $sqlUpdate = "UPDATE morataya_tallas SET talla_situacion = 1 WHERE talla_id = {$reactivarTalla}";
                        $resultado = Talla::SQL($sqlUpdate);
                        $success = $resultado !== false;
                    } else {
                        $talla = new Talla([
                            'talla_etiqueta' => $etiqueta
                        ]);
                        $resultado = $talla->guardar();
                        $success = ($resultado['resultado'] ?? false);
                    }

                    if ($success) {
                        
                        try {
                            $accion = $tallaId ? 'Actualización' : (isset($reactivarTalla) ? 'Reactivación' : 'Creación');
                            $auditoria = new Auditoria([
                                'usu_id' => $_SESSION['usuario_id'] ?? 1,
                                'aud_usuario_nombre' => $_SESSION['usuario_nombre'] ?? 'Sistema',
                                'aud_modulo' => 'Tallas',
                                'aud_accion' => $accion . ' de talla: ' . $etiqueta,
                                'aud_descripcion' => 'Talla ' . strtolower($accion) . ': ' . $etiqueta,
                                'aud_ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                                'aud_navegador' => substr($_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido', 0, 100)
                            ]);
                            $auditoria->guardar();
                        } catch (Exception $e) {
                            error_log("Error en auditoría: " . $e->getMessage());
                        }

                        $mensaje = $tallaId ? 'actualizada' : (isset($reactivarTalla) ? 'reactivada' : 'creada');
                        echo json_encode([
                            'resultado' => true,
                            'mensaje' => 'Talla ' . $mensaje . ' correctamente'
                        ], JSON_UNESCAPED_UNICODE);
                        exit;
                    } else {
                        $errores[] = 'Error al guardar en la base de datos';
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

        } catch (Exception $e) {
            echo json_encode([
                'resultado' => false,
                'mensaje' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    public static function eliminarAPI()
    {
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            AuthController::verificarLogin();

            if (!isset($_GET['talla_id'])) {
                echo json_encode(['resultado' => false, 'mensaje' => 'ID no proporcionado'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $id = filter_var($_GET['talla_id'], FILTER_VALIDATE_INT);

            if (!$id) {
                echo json_encode(['resultado' => false, 'mensaje' => 'ID no válido'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $query = "SELECT * FROM morataya_tallas WHERE talla_id = {$id} AND talla_situacion = 1";
            $talla = Talla::fetchFirst($query);

            if (!$talla) {
                echo json_encode(['resultado' => false, 'mensaje' => 'Talla no encontrada'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $etiqueta = $talla['talla_etiqueta'];

            $sqlUpdate = "UPDATE morataya_tallas SET talla_situacion = 0 WHERE talla_id = {$id}";
            $resultado = Talla::SQL($sqlUpdate);

            if ($resultado !== false) {
                try {
                    $auditoria = new Auditoria([
                        'usu_id' => $_SESSION['usuario_id'] ?? 1,
                        'aud_usuario_nombre' => $_SESSION['usuario_nombre'] ?? 'Sistema',
                        'aud_modulo' => 'Tallas',
                        'aud_accion' => 'Eliminación de talla: ' . $etiqueta,
                        'aud_descripcion' => 'Talla eliminada: ' . $etiqueta,
                        'aud_ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                        'aud_navegador' => substr($_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido', 0, 100)
                    ]);
                    $auditoria->guardar();
                } catch (Exception $e) {
                    error_log("Error en auditoría: " . $e->getMessage());
                }

                echo json_encode([
                    'resultado' => true,
                    'mensaje' => 'Talla eliminada correctamente'
                ], JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode([
                    'resultado' => false,
                    'mensaje' => 'Error al eliminar la talla'
                ], JSON_UNESCAPED_UNICODE);
            }

        } catch (Exception $e) {
            echo json_encode([
                'resultado' => false,
                'mensaje' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    public static function obtenerPorTipoAPI()
    {
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            AuthController::verificarLogin();
            $tallas = Talla::where('talla_situacion', 1);
            echo json_encode($tallas, JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            echo json_encode([], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }
}