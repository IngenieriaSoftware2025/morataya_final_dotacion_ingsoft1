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
            $inventarios = InventarioDotacion::obtenerConDetalles();
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
        if(!validarNumero($cantidad, 1, 9999)) $errores[] = 'La cantidad debe ser entre 1 y 9999';
        
        if(empty($errores)) {
            try {
                $inventarioExistente = InventarioDotacion::where('tipo_id', $tipo_id, 'talla_id', $talla_id);
                
                if($inventarioExistente) {
                    $inventarioExistente->cantidad += $cantidad;
                    $resultado = $inventarioExistente->guardar();
                } else {
                    $inventario = new InventarioDotacion([
                        'tipo_id' => $tipo_id,
                        'talla_id' => $talla_id,
                        'cantidad' => $cantidad
                    ]);
                    $resultado = $inventario->guardar();
                }
                
                if($resultado) {
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
            $solicitudes = SolicitudDotacion::obtenerConDetalles();
            echo json_encode($solicitudes);
        } catch (Exception $e) {
            echo json_encode([
                'resultado' => false,
                'mensaje' => 'Error al obtener solicitudes'
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
        
        // Validar límite anual (máximo 3 dotaciones por año)
        if($personal_id) {
            $año_actual = date('Y');
            $entregas_año = EntregaDotacion::contarEntregasAnuales($personal_id, $año_actual);
            
            if($entregas_año >= 3) {
                $errores[] = 'El personal ya recibió el máximo de 3 dotaciones este año';
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
                
                if($resultado) {
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
            $entregas = EntregaDotacion::obtenerConDetalles();
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
        
        $solicitud = SolicitudDotacion::find($solicitud_id);
        if(!$solicitud || $solicitud->estado_entrega == 1) {
            $errores[] = 'Solicitud no válida o ya entregada';
        }
        
        if($solicitud) {
            $inventario = InventarioDotacion::where('tipo_id', $solicitud->tipo_id, 'talla_id', $solicitud->talla_id);
            if(!$inventario || $inventario->cantidad < 1) {
                $errores[] = 'No hay inventario disponible';
            }
        }
        
        if(empty($errores)) {
            try {
                $entrega = new EntregaDotacion([
                    'solicitud_id' => $solicitud_id,
                    'usuario_id' => $_SESSION['usuario_id']
                ]);
                
                $resultado = $entrega->guardar();
                
                if($resultado) {
                    $solicitud->estado_entrega = 1;
                    $solicitud->guardar();
                    
                    $inventario->cantidad -= 1;
                    $inventario->guardar();
                    
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
}
