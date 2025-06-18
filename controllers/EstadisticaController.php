<?php

namespace Controllers;
use MVC\Router;
use Model\EntregaDotacion;
use Model\InventarioDotacion;
use Model\SolicitudDotacion;
use Model\Personal;
use Model\Auditoria;
use Exception;

class EstadisticaController {
    
    public static function index(Router $router) {
        AuthController::verificarLogin();
        $router->render('estadisticas/index', []);
    }
    
    public static function obtenerEstadisticasAPI() {
        header('Content-Type: application/json');
        AuthController::verificarLogin();
        
        try {
            $estadisticas = [
                'resumen' => self::obtenerResumenGeneral(),
                'entregas_mes' => self::obtenerEntregasPorMes(),
                'inventario_bajo' => self::obtenerInventarioBajo(),
                'solicitudes_pendientes' => self::obtenerSolicitudesPendientes()
            ];
            
            echo json_encode($estadisticas);
        } catch (Exception $e) {
            echo json_encode([
                'resultado' => false,
                'mensaje' => 'Error al obtener estadísticas: ' . $e->getMessage()
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
    
    private static function obtenerInventarioBajo() {
        try {
            $query = "
                SELECT 
                    td.tipo_nombre,
                    t.talla_etiqueta,
                    i.cantidad
                FROM morataya_inventario_dotacion i
                INNER JOIN morataya_tipos_dotacion td ON i.tipo_id = td.tipo_id
                INNER JOIN morataya_tallas t ON i.talla_id = t.talla_id
                WHERE i.inv_situacion = 1 AND i.cantidad <= 5
                ORDER BY i.cantidad ASC
            ";
            
            return InventarioDotacion::fetchArray($query);
        } catch (Exception $e) {
            return [];
        }
    }
    
    private static function obtenerSolicitudesPendientes() {
        try {
            $query = "
                SELECT COUNT(*) as total
                FROM morataya_solicitudes_dotacion 
                WHERE estado_entrega = 0 AND solicitud_situacion = 1
            ";
            
            $resultado = SolicitudDotacion::fetchFirst($query);
            return $resultado['total'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }
}