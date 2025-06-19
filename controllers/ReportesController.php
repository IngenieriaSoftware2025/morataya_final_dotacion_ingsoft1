<?php

namespace Controllers;
use MVC\Router;
use Model\EntregaDotacion;
use Model\InventarioDotacion;
use Model\SolicitudDotacion;
use Model\Personal;
use Model\TipoDotacion;
use Model\Talla;
use Exception;

class ReportesController {
    
    public static function estadisticas(Router $router) {
        AuthController::verificarLogin();
        $router->render('reportes/estadisticas', []);
    }
    
    public static function dotaciones(Router $router) {
        AuthController::verificarLogin();
        $router->render('reportes/dotaciones', []);
    }
    
    public static function inventario(Router $router) {
        AuthController::verificarLogin();
        $router->render('reportes/inventario', []);
    }
    
    
    public static function estadisticasAPI() {
        header('Content-Type: application/json');
        AuthController::verificarLogin();
        
        try {
            $estadisticas = [
                'resumen' => self::obtenerResumenGeneral(),
                'entregas_por_mes' => self::obtenerEntregasPorMes(),
                'entregas_por_tipo' => self::obtenerEntregasPorTipo(),
                'entregas_por_talla' => self::obtenerEntregasPorTalla()
            ];
            
            echo json_encode($estadisticas);
        } catch (Exception $e) {
            echo json_encode([
                'resultado' => false,
                'mensaje' => 'Error al obtener estadísticas'
            ]);
        }
    }
    
    public static function dotacionesAPI() {
        header('Content-Type: application/json');
        AuthController::verificarLogin();
        
        try {
            $query = "
                SELECT 
                    e.entrega_id,
                    p.personal_nombre,
                    p.personal_cui,
                    td.tipo_nombre,
                    t.talla_etiqueta,
                    e.fecha_entrega,
                    u.usu_nombre as usuario_entrega
                FROM morataya_entregas_dotacion e
                INNER JOIN morataya_solicitudes_dotacion s ON e.solicitud_id = s.solicitud_id
                INNER JOIN morataya_personal p ON s.personal_id = p.personal_id
                INNER JOIN morataya_tipos_dotacion td ON s.tipo_id = td.tipo_id
                INNER JOIN morataya_tallas t ON s.talla_id = t.talla_id
                INNER JOIN morataya_usuario u ON e.usuario_id = u.usu_id
                WHERE e.entrega_situacion = 1
                ORDER BY e.fecha_entrega DESC
            ";
            
            $entregas = EntregaDotacion::fetchArray($query);
            echo json_encode($entregas);
        } catch (Exception $e) {
            echo json_encode([
                'resultado' => false,
                'mensaje' => 'Error al obtener dotaciones'
            ]);
        }
    }
    
    public static function inventarioAPI() {
        header('Content-Type: application/json');
        AuthController::verificarLogin();
        
        try {
            $query = "
                SELECT 
                    i.inv_id,
                    td.tipo_nombre,
                    t.talla_etiqueta,
                    i.cantidad,
                    i.fecha_ingreso
                FROM morataya_inventario_dotacion i
                INNER JOIN morataya_tipos_dotacion td ON i.tipo_id = td.tipo_id
                INNER JOIN morataya_tallas t ON i.talla_id = t.talla_id
                WHERE i.inv_situacion = 1 AND td.tipo_situacion = 1 AND t.talla_situacion = 1
                ORDER BY td.tipo_nombre, t.talla_etiqueta
            ";
            
            $inventario = InventarioDotacion::fetchArray($query);
            echo json_encode($inventario);
        } catch (Exception $e) {
            echo json_encode([
                'resultado' => false,
                'mensaje' => 'Error al obtener inventario'
            ]);
        }
    }
    
    public static function entregasPorMesAPI() {
        header('Content-Type: application/json');
        AuthController::verificarLogin();
        
        try {
            $query = "
                SELECT 
                    YEAR(fecha_entrega) as año,
                    MONTH(fecha_entrega) as mes,
                    COUNT(*) as total_entregas
                FROM morataya_entregas_dotacion
                WHERE entrega_situacion = 1 
                AND fecha_entrega >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY YEAR(fecha_entrega), MONTH(fecha_entrega)
                ORDER BY año DESC, mes DESC
            ";
            
            $entregas = EntregaDotacion::fetchArray($query);
            echo json_encode($entregas);
        } catch (Exception $e) {
            echo json_encode([
                'resultado' => false,
                'mensaje' => 'Error al obtener entregas por mes'
            ]);
        }
    }
    
    public static function entregasPorTallaAPI() {
        header('Content-Type: application/json');
        AuthController::verificarLogin();
        
        try {
            $query = "
                SELECT 
                    t.talla_etiqueta,
                    td.tipo_nombre,
                    COUNT(e.entrega_id) as total_entregas
                FROM morataya_tallas t
                LEFT JOIN morataya_solicitudes_dotacion s ON t.talla_id = s.talla_id
                LEFT JOIN morataya_entregas_dotacion e ON s.solicitud_id = e.solicitud_id 
                    AND e.entrega_situacion = 1
                LEFT JOIN morataya_tipos_dotacion td ON s.tipo_id = td.tipo_id
                WHERE t.talla_situacion = 1
                GROUP BY t.talla_id, t.talla_etiqueta, td.tipo_id, td.tipo_nombre
                HAVING total_entregas > 0
                ORDER BY td.tipo_nombre, t.talla_etiqueta
            ";
            
            $entregas = EntregaDotacion::fetchArray($query);
            echo json_encode($entregas);
        } catch (Exception $e) {
            echo json_encode([
                'resultado' => false,
                'mensaje' => 'Error al obtener entregas por talla'
            ]);
        }
    }
    
    
    private static function obtenerResumenGeneral() {
        try {
            $query = "
                SELECT 
                    (SELECT COUNT(*) FROM morataya_entregas_dotacion WHERE entrega_situacion = 1) as total_entregas,
                    (SELECT COUNT(*) FROM morataya_solicitudes_dotacion WHERE estado_entrega = 0 AND solicitud_situacion = 1) as solicitudes_pendientes,
                    (SELECT COALESCE(SUM(cantidad), 0) FROM morataya_inventario_dotacion WHERE inv_situacion = 1) as total_inventario,
                    (SELECT COUNT(*) FROM morataya_personal WHERE personal_situacion = 1) as total_personal
            ";
            
            return EntregaDotacion::fetchFirst($query);
        } catch (Exception $e) {
            return [
                'total_entregas' => 0,
                'solicitudes_pendientes' => 0,
                'total_inventario' => 0,
                'total_personal' => 0
            ];
        }
    }
    
    private static function obtenerEntregasPorMes() {
        try {
            $query = "
                SELECT 
                    DATE_FORMAT(fecha_entrega, '%Y-%m') as mes,
                    COUNT(*) as total
                FROM morataya_entregas_dotacion
                WHERE entrega_situacion = 1 
                AND fecha_entrega >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                GROUP BY DATE_FORMAT(fecha_entrega, '%Y-%m')
                ORDER BY mes
            ";
            
            return EntregaDotacion::fetchArray($query);
        } catch (Exception $e) {
            return [];
        }
    }
    
    private static function obtenerEntregasPorTipo() {
        try {
            $query = "
                SELECT 
                    td.tipo_nombre,
                    COUNT(e.entrega_id) as total
                FROM morataya_tipos_dotacion td
                LEFT JOIN morataya_solicitudes_dotacion s ON td.tipo_id = s.tipo_id
                LEFT JOIN morataya_entregas_dotacion e ON s.solicitud_id = e.solicitud_id 
                    AND e.entrega_situacion = 1
                WHERE td.tipo_situacion = 1
                GROUP BY td.tipo_id, td.tipo_nombre
                ORDER BY total DESC
            ";
            
            return EntregaDotacion::fetchArray($query);
        } catch (Exception $e) {
            return [];
        }
    }
    
    private static function obtenerEntregasPorTalla() {
        try {
            $query = "
                SELECT 
                    t.talla_etiqueta,
                    COUNT(e.entrega_id) as total
                FROM morataya_tallas t
                LEFT JOIN morataya_solicitudes_dotacion s ON t.talla_id = s.talla_id
                LEFT JOIN morataya_entregas_dotacion e ON s.solicitud_id = e.solicitud_id 
                    AND e.entrega_situacion = 1
                WHERE t.talla_situacion = 1
                GROUP BY t.talla_id, t.talla_etiqueta
                HAVING total > 0
                ORDER BY total DESC
            ";
            
            return EntregaDotacion::fetchArray($query);
        } catch (Exception $e) {
            return [];
        }
    }
}