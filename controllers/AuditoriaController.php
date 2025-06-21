<?php

namespace Controllers;

use Exception;
use MVC\Router;
use Model\Auditoria;

class AuditoriaController {

    public static function index(Router $router) {
        AuthController::verificarLogin();
        $router->render('auditoria/index', []);
    }

    public static function obtenerAPI() {
        header('Content-Type: application/json');
        AuthController::verificarLogin();
        
        try {
            $query = "SELECT 
                        a.aud_id,
                        a.usu_id,
                        a.aud_usuario_nombre,
                        a.aud_modulo,
                        a.aud_accion,
                        a.aud_descripcion,
                        a.aud_ruta,
                        a.aud_ip,
                        a.aud_navegador,
                        a.aud_fecha_creacion,
                        a.aud_situacion
                    FROM morataya_auditoria a
                    WHERE a.aud_situacion = 1 
                    ORDER BY a.aud_fecha_creacion DESC";
            
            $auditoria = Auditoria::fetchArray($query);
            echo json_encode($auditoria);
            
        } catch (Exception $e) {
            echo json_encode([
                'resultado' => false,
                'mensaje' => 'Error al obtener auditoría: ' . $e->getMessage()
            ]);
        }
    }

    public static function buscarAPI() {
        header('Content-Type: application/json');
        AuthController::verificarLogin();
        
        try {
            $fecha_inicio = $_GET['fecha_inicio'] ?? null;
            $fecha_fin = $_GET['fecha_fin'] ?? null;
            $usuario_id = $_GET['usuario_id'] ?? null;
            $modulo = $_GET['modulo'] ?? null;

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

            $where = implode(" AND ", $condiciones);
            
            $query = "SELECT 
                        a.aud_id,
                        a.usu_id,
                        a.aud_usuario_nombre,
                        a.aud_modulo,
                        a.aud_accion,
                        a.aud_descripcion,
                        a.aud_ruta,
                        a.aud_ip,
                        a.aud_navegador,
                        a.aud_fecha_creacion,
                        a.aud_situacion
                    FROM morataya_auditoria a
                    WHERE $where 
                    ORDER BY a.aud_fecha_creacion DESC";
            
            $data = Auditoria::fetchArray($query);

            echo json_encode([
                'resultado' => true,
                'mensaje' => 'Auditoría obtenida correctamente',
                'data' => $data
            ]);

        } catch (Exception $e) {
            echo json_encode([
                'resultado' => false,
                'mensaje' => 'Error al buscar auditoría: ' . $e->getMessage()
            ]);
        }
    }

    public static function obtenerUsuariosAPI() {
        header('Content-Type: application/json');
        AuthController::verificarLogin();
        
        try {
            $query = "SELECT DISTINCT 
                        a.usu_id,
                        a.aud_usuario_nombre
                    FROM morataya_auditoria a
                    WHERE a.aud_situacion = 1
                    AND a.aud_usuario_nombre IS NOT NULL
                    ORDER BY a.aud_usuario_nombre";
            
            $usuarios = Auditoria::fetchArray($query);

            echo json_encode([
                'resultado' => true,
                'usuarios' => $usuarios
            ]);

        } catch (Exception $e) {
            echo json_encode([
                'resultado' => false,
                'mensaje' => 'Error al obtener usuarios: ' . $e->getMessage()
            ]);
        }
    }

    public static function obtenerModulosAPI() {
        header('Content-Type: application/json');
        AuthController::verificarLogin();
        
        try {
            $query = "SELECT DISTINCT aud_modulo
                     FROM morataya_auditoria 
                     WHERE aud_situacion = 1
                     AND aud_modulo IS NOT NULL
                     ORDER BY aud_modulo";
            
            $modulos = Auditoria::fetchArray($query);

            echo json_encode([
                'resultado' => true,
                'modulos' => $modulos
            ]);

        } catch (Exception $e) {
            echo json_encode([
                'resultado' => false,
                'mensaje' => 'Error al obtener módulos: ' . $e->getMessage()
            ]);
        }
    }

    public static function estadisticasAPI() {
        header('Content-Type: application/json');
        AuthController::verificarLogin();
        
        try {
            $queryTotal = "SELECT COUNT(*) as total FROM morataya_auditoria WHERE aud_situacion = 1";
            $total = Auditoria::fetchFirst($queryTotal);
            
            $queryModulos = "SELECT aud_modulo, COUNT(*) as cantidad
                           FROM morataya_auditoria 
                           WHERE aud_situacion = 1
                           GROUP BY aud_modulo
                           ORDER BY cantidad DESC";
            $modulos = Auditoria::fetchArray($queryModulos);

            $queryDias = "SELECT DATE(aud_fecha_creacion) as fecha, COUNT(*) as cantidad
                         FROM morataya_auditoria 
                         WHERE aud_situacion = 1 
                         AND aud_fecha_creacion >= (TODAY - INTERVAL(7) DAY TO DAY)
                         GROUP BY DATE(aud_fecha_creacion)
                         ORDER BY fecha DESC";
            $dias = Auditoria::fetchArray($queryDias);

            echo json_encode([
                'resultado' => true,
                'estadisticas' => [
                    'total' => $total['total'] ?? 0,
                    'modulos' => $modulos,
                    'dias' => $dias
                ]
            ]);

        } catch (Exception $e) {
            echo json_encode([
                'resultado' => false,
                'mensaje' => 'Error al obtener estadísticas: ' . $e->getMessage()
            ]);
        }
    }
}