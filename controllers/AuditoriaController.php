<?php

namespace Controllers;

use Exception;
use MVC\Router;
use Model\ActiveRecord;
use Model\Auditoria;

class AuditoriaController extends ActiveRecord
{

    public static function renderizarPagina(Router $router)
    {
        session_start();
        if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'ADMIN') {
            header('Location: /morataya_final_dotacion_ingsoft1/inicio');
            exit;
        }
        
        $router->render('auditoria/index', []);
    }


    public static function registrarActividad($modulo, $accion, $descripcion, $ruta = '')
    {
        try {
            session_start();
            if(isset($_SESSION['usuario_id'])) {
                $auditoria = new Auditoria([
                    'usu_id' => $_SESSION['usuario_id'],
                    'aud_usuario_nombre' => $_SESSION['user'] ?? 'Usuario desconocido',
                    'aud_modulo' => $modulo,
                    'aud_accion' => $accion,
                    'aud_descripcion' => $descripcion,
                    'aud_ruta' => $ruta,
                    'aud_ip' => $_SERVER['REMOTE_ADDR'] ?? 'No disponible',
                    'aud_navegador' => self::obtenerInfoNavegador(),
                    'aud_situacion' => 1
                ]);
                return $auditoria->crear();
            }
            return false;
        } catch (Exception $e) {
            error_log("Error registrando auditoría: " . $e->getMessage());
            return false;
        }
    }


    private static function obtenerInfoNavegador() 
    {
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido';
        
        if (strpos($user_agent, 'Chrome') !== false) {
            return 'Chrome';
        } elseif (strpos($user_agent, 'Firefox') !== false) {
            return 'Firefox';
        } elseif (strpos($user_agent, 'Safari') !== false) {
            return 'Safari';
        } elseif (strpos($user_agent, 'Edge') !== false) {
            return 'Edge';
        } else {
            return 'Otro';
        }
    }

 
    public static function buscarAPI()
    {
        try {
            $fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : null;
            $fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : null;
            $usuario_id = isset($_GET['usuario_id']) ? $_GET['usuario_id'] : null;
            $modulo = isset($_GET['modulo']) ? $_GET['modulo'] : null;
            $accion = isset($_GET['accion']) ? $_GET['accion'] : null;

            $condiciones = ["a.aud_situacion = 1"];

            if ($fecha_inicio) {
                $condiciones[] = "DATE(a.aud_fecha_creacion) >= '{$fecha_inicio}'";
            }

            if ($fecha_fin) {
                $condiciones[] = "DATE(a.aud_fecha_creacion) <= '{$fecha_fin}'";
            }

            if ($usuario_id) {
                $condiciones[] = "a.usu_id = {$usuario_id}";
            }

            if ($modulo) {
                $condiciones[] = "a.aud_modulo = '{$modulo}'";
            }

            if ($accion) {
                $condiciones[] = "a.aud_accion = '{$accion}'";
            }

            $where = implode(" AND ", $condiciones);
            
            $sql = "SELECT 
                        a.aud_id,
                        a.usu_id,
                        COALESCE(a.aud_usuario_nombre, u.usu_nombre) as usuario_nombre,
                        a.aud_modulo,
                        a.aud_accion,
                        a.aud_descripcion,
                        a.aud_ruta,
                        a.aud_ip,
                        a.aud_navegador,
                        a.aud_fecha_creacion,
                        a.aud_situacion
                    FROM morataya_auditoria a
                    LEFT JOIN morataya_usuario u ON a.usu_id = u.usu_id
                    WHERE $where 
                    ORDER BY a.aud_fecha_creacion DESC, a.aud_id DESC";
            
            $data = self::fetchArray($sql);

            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Actividades obtenidas correctamente',
                'data' => $data
            ]);

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al obtener las actividades',
                'detalle' => $e->getMessage(),
            ]);
        }
    }


    public static function buscarUsuariosAPI()
    {
        try {
            $sql = "SELECT DISTINCT 
                        a.usu_id,
                        COALESCE(a.aud_usuario_nombre, u.usu_nombre) as usuario_nombre
                    FROM morataya_auditoria a
                    LEFT JOIN morataya_usuario u ON a.usu_id = u.usu_id
                    WHERE a.aud_situacion = 1
                    ORDER BY usuario_nombre";
            
            $data = self::fetchArray($sql);

            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Usuarios obtenidos correctamente',
                'data' => $data
            ]);

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al obtener los usuarios',
                'detalle' => $e->getMessage(),
            ]);
        }
    }

 
    public static function estadisticasAPI()
    {
        try {
            $sqlModulos = "SELECT 
                            aud_modulo,
                            COUNT(*) as total
                          FROM morataya_auditoria 
                          WHERE aud_situacion = 1
                          GROUP BY aud_modulo
                          ORDER BY total DESC";
            
            $sqlDias = "SELECT 
                          DATE(aud_fecha_creacion) as fecha,
                          COUNT(*) as total
                        FROM morataya_auditoria 
                        WHERE aud_situacion = 1 
                        AND aud_fecha_creacion >= (CURRENT - 7 UNITS DAY)
                        GROUP BY DATE(aud_fecha_creacion)
                        ORDER BY fecha DESC";

            $estadisticasModulos = self::fetchArray($sqlModulos);
            $estadisticasDias = self::fetchArray($sqlDias);

            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Estadísticas obtenidas correctamente',
                'data' => [
                    'modulos' => $estadisticasModulos,
                    'dias' => $estadisticasDias
                ]
            ]);

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al obtener estadísticas',
                'detalle' => $e->getMessage(),
            ]);
        }
    }
}