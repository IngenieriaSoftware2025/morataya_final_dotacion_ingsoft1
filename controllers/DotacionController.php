<?php

namespace Controllers;
use MVC\Router;
use Model\TipoDotacion;
use Model\Talla;
use Model\InventarioDotacion;
use Model\SolicitudDotacion;
use Model\EntregaDotacion;
use Model\Personal;
use Model\Auditoria;
use Exception;

class DotacionController {
    
    // Inventario
    public static function inventario(Router $router) {
        AuthController::verificarLogin();
        $router->render('dotacion/inventario/index', []);
    }
    
    public static function obtenerInventarioAPI() {
        header('Content-Type: application/json');
        AuthController::verificarLogin();
        
        try {
            $inventarios = self::obtenerInventarioConDetalles();
            echo json_encode($inventarios);
        } catch (Exception $e) {
            echo json_encode([
                'resultado' => false,
                'mensaje' => 'Error al obtener inventario'
            ]);
        }
    }
    
    public static function guardarInventarioAPI() {
        header('Content-Type: application/json');
        AuthController::verificarLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['resultado' => false, 'mensaje' => 'Método no permitido']);
            return;
        }
        
        $errores = [];
        
        $tipo_id = isset($_POST['tipo_id']) ? (int)$_POST['tipo_id'] : 0;
        $talla_id = isset($_POST['talla_id']) ? (int)$_POST['talla_id'] : 0;
        $cantidad = isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : 0;
        
        if(!$tipo_id) $errores[] = 'Debe seleccionar un tipo de dotación';
        if(!$talla_id) $errores[] = 'Debe seleccionar una talla';
        if(!is_numeric($cantidad) || $cantidad < 1 || $cantidad > 9999) $errores[] = 'La cantidad debe ser entre 1 y 9999';
        
        if(empty($errores)) {
            try {
                // Verificar si ya existe inventario para este tipo y talla usando SQL directo
                $inventarioExistente = self::buscarInventarioExistentePorSQL($tipo_id, $talla_id);
                
                if($inventarioExistente) {
                    // Actualizar cantidad existente usando SQL directo
                    $nuevaCantidad = $inventarioExistente['cantidad'] + $cantidad;
                    $sqlUpdate = "UPDATE morataya_inventario_dotacion SET cantidad = {$nuevaCantidad} WHERE inv_id = {$inventarioExistente['inv_id']}";
                    $resultado = InventarioDotacion::SQL($sqlUpdate);
                    $exito = true;
                } else {
                    // Crear nuevo registro
                    $inventario = new InventarioDotacion([
                        'tipo_id' => $tipo_id,
                        'talla_id' => $talla_id,
                        'cantidad' => $cantidad
                    ]);
                    $resultado = $inventario->guardar();
                    $exito = $resultado['resultado'] ?? false;
                }
                
                if($exito) {
                    $auditoria = new Auditoria([
                        'usu_id' => $_SESSION['usuario_id'],
                        'aud_modulo' => 'Inventario',
                        'aud_accion' => "Ingreso de inventario cantidad: {$cantidad}",
                        'aud_ip' => $_SERVER['REMOTE_ADDR'],
                        'aud_navegador' => substr($_SERVER['HTTP_USER_AGENT'], 0, 100)
                    ]);
                    $auditoria->guardar();
                    
                    echo json_encode([
                        'resultado' => true,
                        'mensaje' => 'Inventario agregado correctamente'
                    ]);
                    return;
                }
            } catch (Exception $e) {
                $errores[] = 'Error al guardar: ' . $e->getMessage();
            }
        }
        
        echo json_encode([
            'resultado' => false,
            'mensaje' => 'Errores de validación',
            'errores' => $errores
        ]);
    }
    
    // Solicitudes
    public static function solicitudes(Router $router) {
        AuthController::verificarLogin();
        $router->render('dotacion/solicitudes/index', []);
    }
    
    public static function obtenerSolicitudesAPI() {
        header('Content-Type: application/json');
        AuthController::verificarLogin();
        
        try {
            $solicitudes = self::obtenerSolicitudesConDetalles();
            echo json_encode($solicitudes);
        } catch (Exception $e) {
            echo json_encode([
                'resultado' => false,
                'mensaje' => 'Error al obtener solicitudes'
            ]);
        }
    }
    
    public static function obtenerSolicitudesPendientesAPI() {
        header('Content-Type: application/json');
        AuthController::verificarLogin();
        
        try {
            $query = "
                SELECT 
                    s.solicitud_id,
                    p.personal_nombre,
                    td.tipo_nombre,
                    t.talla_etiqueta,
                    s.fecha_solicitud
                FROM morataya_solicitudes_dotacion s
                INNER JOIN morataya_personal p ON s.personal_id = p.personal_id
                INNER JOIN morataya_tipos_dotacion td ON s.tipo_id = td.tipo_id
                INNER JOIN morataya_tallas t ON s.talla_id = t.talla_id
                WHERE s.estado_entrega = 0 AND s.solicitud_situacion = 1
                ORDER BY s.fecha_solicitud ASC
            ";
            
            $pendientes = SolicitudDotacion::fetchArray($query);
            echo json_encode($pendientes);
        } catch (Exception $e) {
            echo json_encode([
                'resultado' => false,
                'mensaje' => 'Error al obtener solicitudes pendientes'
            ]);
        }
    }
    
    public static function guardarSolicitudAPI() {
        header('Content-Type: application/json');
        AuthController::verificarLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['resultado' => false, 'mensaje' => 'Método no permitido']);
            return;
        }
        
        $errores = [];
        
        $personal_id = isset($_POST['personal_id']) ? (int)$_POST['personal_id'] : 0;
        $tipo_id = isset($_POST['tipo_id']) ? (int)$_POST['tipo_id'] : 0;
        $talla_id = isset($_POST['talla_id']) ? (int)$_POST['talla_id'] : 0;
        
        if(!$personal_id) $errores[] = 'Debe seleccionar el personal';
        if(!$tipo_id) $errores[] = 'Debe seleccionar un tipo de dotación';
        if(!$talla_id) $errores[] = 'Debe seleccionar una talla';
        
        // Validar límite anual (máximo 3 dotaciones por año) usando SQL directo
        if($personal_id) {
            $año_actual = date('Y');
            $entregas_año = self::contarEntregasAnualesPorSQL($personal_id, $año_actual);
            
            if($entregas_año >= 3) {
                $errores[] = 'El personal ya recibió el máximo de 3 dotaciones este año';
            }
        }
        
        // Verificar que no tenga solicitud pendiente del mismo tipo usando SQL directo
        if($personal_id && $tipo_id) {
            $solicitudExistente = self::verificarSolicitudPendientePorSQL($personal_id, $tipo_id);
            if($solicitudExistente) {
                $errores[] = 'Ya existe una solicitud pendiente de este tipo para el personal';
            }
        }
        
        if(empty($errores)) {
            try {
                $solicitud = new SolicitudDotacion([
                    'personal_id' => $personal_id,
                    'tipo_id' => $tipo_id,
                    'talla_id' => $talla_id
                ]);
                
                $resultado = $solicitud->guardar();
                
                if($resultado['resultado'] ?? false) {
                    $auditoria = new Auditoria([
                        'usu_id' => $_SESSION['usuario_id'],
                        'aud_modulo' => 'Solicitudes',
                        'aud_accion' => "Solicitud creada para personal ID: {$personal_id}",
                        'aud_ip' => $_SERVER['REMOTE_ADDR'],
                        'aud_navegador' => substr($_SERVER['HTTP_USER_AGENT'], 0, 100)
                    ]);
                    $auditoria->guardar();
                    
                    echo json_encode([
                        'resultado' => true,
                        'mensaje' => 'Solicitud creada correctamente'
                    ]);
                    return;
                }
            } catch (Exception $e) {
                $errores[] = 'Error al guardar: ' . $e->getMessage();
            }
        }
        
        echo json_encode([
            'resultado' => false,
            'mensaje' => 'Errores de validación',
            'errores' => $errores
        ]);
    }
    
    // Entregas
    public static function entregas(Router $router) {
        AuthController::verificarLogin();
        $router->render('dotacion/entregas/index', []);
    }
    
    public static function obtenerEntregasAPI() {
        header('Content-Type: application/json');
        AuthController::verificarLogin();
        
        try {
            $entregas = self::obtenerEntregasConDetalles();
            echo json_encode($entregas);
        } catch (Exception $e) {
            echo json_encode([
                'resultado' => false,
                'mensaje' => 'Error al obtener entregas'
            ]);
        }
    }
    
    public static function procesarEntregaAPI() {
        header('Content-Type: application/json');
        AuthController::verificarLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['resultado' => false, 'mensaje' => 'Método no permitido']);
            return;
        }
        
        $errores = [];
        
        $solicitud_id = isset($_POST['solicitud_id']) ? (int)$_POST['solicitud_id'] : 0;
        
        if(!$solicitud_id) $errores[] = 'Debe seleccionar una solicitud';
        
        // Buscar solicitud usando SQL directo
        $solicitud = self::buscarSolicitudPorSQL($solicitud_id);
        if(!$solicitud || $solicitud['estado_entrega'] == 1) {
            $errores[] = 'Solicitud no válida o ya entregada';
        }
        
        if($solicitud) {
            // Verificar inventario disponible usando SQL directo
            $inventario = self::buscarInventarioExistentePorSQL($solicitud['tipo_id'], $solicitud['talla_id']);
            if(!$inventario || $inventario['cantidad'] < 1) {
                $errores[] = 'No hay inventario disponible para esta dotación';
            }
        }
        
        if(empty($errores)) {
            try {
                // Crear entrega
                $entrega = new EntregaDotacion([
                    'solicitud_id' => $solicitud_id,
                    'usuario_id' => $_SESSION['usuario_id']
                ]);
                
                $resultado = $entrega->guardar();
                
                if($resultado['resultado'] ?? false) {
                    // Actualizar estado de solicitud usando SQL directo
                    $sqlUpdateSolicitud = "UPDATE morataya_solicitudes_dotacion SET estado_entrega = 1 WHERE solicitud_id = {$solicitud_id}";
                    SolicitudDotacion::SQL($sqlUpdateSolicitud);
                    
                    // Descontar del inventario usando SQL directo
                    $nuevaCantidad = $inventario['cantidad'] - 1;
                    $sqlUpdateInventario = "UPDATE morataya_inventario_dotacion SET cantidad = {$nuevaCantidad} WHERE inv_id = {$inventario['inv_id']}";
                    InventarioDotacion::SQL($sqlUpdateInventario);
                    
                    $auditoria = new Auditoria([
                        'usu_id' => $_SESSION['usuario_id'],
                        'aud_modulo' => 'Entregas',
                        'aud_accion' => "Entrega realizada solicitud ID: {$solicitud_id}",
                        'aud_ip' => $_SERVER['REMOTE_ADDR'],
                        'aud_navegador' => substr($_SERVER['HTTP_USER_AGENT'], 0, 100)
                    ]);
                    $auditoria->guardar();
                    
                    echo json_encode([
                        'resultado' => true,
                        'mensaje' => 'Entrega procesada correctamente'
                    ]);
                    return;
                }
            } catch (Exception $e) {
                $errores[] = 'Error al procesar: ' . $e->getMessage();
            }
        }
        
        echo json_encode([
            'resultado' => false,
            'mensaje' => 'Errores de validación',
            'errores' => $errores
        ]);
    }
    
    // ==================== MÉTODOS PRIVADOS CON SQL DIRECTO ====================
    
    private static function obtenerInventarioConDetalles() {
        $query = "
            SELECT 
                i.inv_id,
                i.tipo_id,
                i.talla_id,
                i.cantidad,
                i.fecha_ingreso,
                td.tipo_nombre,
                t.talla_etiqueta
            FROM morataya_inventario_dotacion i
            INNER JOIN morataya_tipos_dotacion td ON i.tipo_id = td.tipo_id
            INNER JOIN morataya_tallas t ON i.talla_id = t.talla_id
            WHERE i.inv_situacion = 1 AND td.tipo_situacion = 1 AND t.talla_situacion = 1
            ORDER BY td.tipo_nombre, t.talla_etiqueta
        ";
        
        return InventarioDotacion::fetchArray($query);
    }
    
    private static function obtenerSolicitudesConDetalles() {
        $query = "
            SELECT 
                s.solicitud_id,
                s.personal_id,
                s.tipo_id,
                s.talla_id,
                s.fecha_solicitud,
                s.estado_entrega,
                p.personal_nombre,
                p.personal_cui,
                td.tipo_nombre,
                t.talla_etiqueta
            FROM morataya_solicitudes_dotacion s
            INNER JOIN morataya_personal p ON s.personal_id = p.personal_id
            INNER JOIN morataya_tipos_dotacion td ON s.tipo_id = td.tipo_id
            INNER JOIN morataya_tallas t ON s.talla_id = t.talla_id
            WHERE s.solicitud_situacion = 1
            ORDER BY s.fecha_solicitud DESC
        ";
        
        return SolicitudDotacion::fetchArray($query);
    }
    
    private static function obtenerEntregasConDetalles() {
        $query = "
            SELECT 
                e.entrega_id,
                e.fecha_entrega,
                p.personal_nombre,
                p.personal_cui,
                td.tipo_nombre,
                t.talla_etiqueta,
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
        
        return EntregaDotacion::fetchArray($query);
    }
    
    private static function buscarInventarioExistentePorSQL($tipo_id, $talla_id) {
        $query = "
            SELECT * FROM morataya_inventario_dotacion 
            WHERE tipo_id = {$tipo_id} AND talla_id = {$talla_id} AND inv_situacion = 1
        ";
        
        return InventarioDotacion::fetchFirst($query);
    }
    
    private static function buscarSolicitudPorSQL($solicitud_id) {
        $query = "
            SELECT * FROM morataya_solicitudes_dotacion 
            WHERE solicitud_id = {$solicitud_id} AND solicitud_situacion = 1
        ";
        
        return SolicitudDotacion::fetchFirst($query);
    }
    
    private static function contarEntregasAnualesPorSQL($personal_id, $año) {
        $query = "
            SELECT COUNT(*) as total
            FROM morataya_entregas_dotacion e
            INNER JOIN morataya_solicitudes_dotacion s ON e.solicitud_id = s.solicitud_id
            WHERE s.personal_id = {$personal_id} 
            AND YEAR(e.fecha_entrega) = {$año}
            AND e.entrega_situacion = 1
        ";
        
        $resultado = EntregaDotacion::fetchFirst($query);
        return $resultado['total'] ?? 0;
    }
    
    private static function verificarSolicitudPendientePorSQL($personal_id, $tipo_id) {
        $query = "
            SELECT COUNT(*) as total FROM morataya_solicitudes_dotacion 
            WHERE personal_id = {$personal_id} 
            AND tipo_id = {$tipo_id} 
            AND estado_entrega = 0 
            AND solicitud_situacion = 1
        ";
        
        $resultado = SolicitudDotacion::fetchFirst($query);
        return ($resultado['total'] ?? 0) > 0;
    }
}