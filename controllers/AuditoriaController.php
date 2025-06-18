<?php

namespace Controllers;
use MVC\Router;
use Model\Auditoria;
use Model\Usuario;
use Exception;

class AuditoriaController {
    
    public static function index(Router $router) {
        AuthController::verificarLogin();
        $router->render('auditoria/index', []);
    }
    
    public static function obtenerAPI() {
        header('Content-Type: application/json');
        AuthController::verificarLogin();
        
        try {
            $query = "
                SELECT 
                    a.aud_id,
                    a.aud_fecha,
                    a.aud_modulo,
                    a.aud_accion,
                    a.aud_ip,
                    a.aud_navegador,
                    u.usu_nombre,
                    u.usu_codigo
                FROM morataya_auditoria a
                INNER JOIN morataya_usuario u ON a.usu_id = u.usu_id
                WHERE a.aud_situacion = 1
                ORDER BY a.aud_fecha DESC
                LIMIT 1000
            ";
            
            $auditoria = Auditoria::fetchArray($query);
            echo json_encode($auditoria);
        } catch (Exception $e) {
            echo json_encode([
                'resultado' => false,
                'mensaje' => 'Error al obtener auditoría'
            ]);
        }
    }
    
    public static function resumenAPI() {
        header('Content-Type: application/json');
        AuthController::verificarLogin();
        
        try {
            $resumen = [
                'total_acciones' => self::obtenerTotalAcciones(),
                'acciones_por_modulo' => self::obtenerAccionesPorModulo(),
                'acciones_por_usuario' => self::obtenerAccionesPorUsuario(),
                'acciones_por_dia' => self::obtenerAccionesPorDia()
            ];
            
            echo json_encode($resumen);
        } catch (Exception $e) {
            echo json_encode([
                'resultado' => false,
                'mensaje' => 'Error al obtener resumen de auditoría'
            ]);
        }
    }
    
   
    private static function obtenerTotalAcciones() {
        try {
            $query = "
                SELECT COUNT(*) as total 
                FROM morataya_auditoria 
                WHERE aud_situacion = 1
            ";
            
            $resultado = Auditoria::fetchFirst($query);
            return $resultado['total'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }
    
    private static function obtenerAccionesPorModulo() {
        try {
            $query = "
                SELECT 
                    aud_modulo as modulo,
                    COUNT(*) as total
                FROM morataya_auditoria
                WHERE aud_situacion = 1
                AND aud_fecha >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY aud_modulo
                ORDER BY total DESC
            ";
            
            return Auditoria::fetchArray($query);
        } catch (Exception $e) {
            return [];
        }
    }
    
    private static function obtenerAccionesPorUsuario() {
        try {
            $query = "
                SELECT 
                    u.usu_nombre,
                    u.usu_codigo,
                    COUNT(a.aud_id) as total_acciones
                FROM morataya_usuario u
                INNER JOIN morataya_auditoria a ON u.usu_id = a.usu_id
                WHERE a.aud_situacion = 1
                AND a.aud_fecha >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY u.usu_id, u.usu_nombre, u.usu_codigo
                ORDER BY total_acciones DESC
                LIMIT 10
            ";
            
            return Auditoria::fetchArray($query);
        } catch (Exception $e) {
            return [];
        }
    }
    
    private static function obtenerAccionesPorDia() {
        try {
            $query = "
                SELECT 
                    DATE(aud_fecha) as fecha,
                    COUNT(*) as total
                FROM morataya_auditoria
                WHERE aud_situacion = 1
                AND aud_fecha >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                GROUP BY DATE(aud_fecha)
                ORDER BY fecha
            ";
            
            return Auditoria::fetchArray($query);
        } catch (Exception $e) {
            return [];
        }
    }
}