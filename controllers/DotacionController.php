<?php

namespace Controllers;
use Model\TipoDotacion;
use Model\Talla;
use Model\InventarioDotacion;
use Model\SolicitudDotacion;
use Model\EntregaDotacion;
use Model\Personal;
use Model\Auditoria;

class DotacionController {
    
    public static function inventario() {
        AuthController::verificarLogin();
        
        $inventarios = InventarioDotacion::obtenerConDetalles();
        $tipos = TipoDotacion::all();
        $tallas = Talla::all();
        
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $resultado = self::procesarInventario();
            if($resultado['success']) {
                header('Location: /dotacion/inventario');
                exit;
            }
        }
        
        $router = new Router();
        $router->render('dotacion/inventario', [
            'inventarios' => $inventarios,
            'tipos' => $tipos,
            'tallas' => $tallas,
            'errores' => $resultado['errores'] ?? []
        ]);
    }
    
    private static function procesarInventario() {
        $errores = [];
        
        $tipo_id = $_POST['tipo_id'] ?? '';
        $talla_id = $_POST['talla_id'] ?? '';
        $cantidad = $_POST['cantidad'] ?? '';
        
        if(!$tipo_id) {
            $errores[] = 'Debe seleccionar un tipo de dotación';
        }
        
        if(!$talla_id) {
            $errores[] = 'Debe seleccionar una talla';
        }
        
        if(!validarNumero($cantidad, 1, 9999)) {
            $errores[] = 'La cantidad debe ser un número entre 1 y 9999';
        }
        
        if(empty($errores)) {
            // Verificar si ya existe inventario para este tipo y talla
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
                // Auditoría
                $tipo = TipoDotacion::find($tipo_id);
                $talla = Talla::find($talla_id);
                
                $auditoria = new Auditoria([
                    'usu_id' => $_SESSION['usuario_id'],
                    'aud_modulo' => 'Inventario',
                    'aud_accion' => "Ingreso de inventario: {$tipo->tipo_nombre} talla {$talla->talla_etiqueta} cantidad {$cantidad}",
                    'aud_ip' => $_SERVER['REMOTE_ADDR'],
                    'aud_navegador' => substr($_SERVER['HTTP_USER_AGENT'], 0, 100)
                ]);
                $auditoria->guardar();
                
                return ['success' => true];
            }
        }
        
        return ['success' => false, 'errores' => $errores];
    }
    
    public static function solicitudes() {
        AuthController::verificarLogin();
        
        $solicitudes = SolicitudDotacion::obtenerConDetalles();
        $personal = Personal::all();
        $tipos = TipoDotacion::all();
        $tallas = Talla::all();
        
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $resultado = self::procesarSolicitud();
            if($resultado['success']) {
                header('Location: /dotacion/solicitudes');
                exit;
            }
        }
        
        $router = new Router();
        $router->render('dotacion/solicitudes', [
            'solicitudes' => $solicitudes,
            'personal' => $personal,
            'tipos' => $tipos,
            'tallas' => $tallas,
            'errores' => $resultado['errores'] ?? []
        ]);
    }
    
    private static function procesarSolicitud() {
        $errores = [];
        
        $personal_id = $_POST['personal_id'] ?? '';
        $tipo_id = $_POST['tipo_id'] ?? '';
        $talla_id = $_POST['talla_id'] ?? '';
        
        if(!$personal_id) {
            $errores[] = 'Debe seleccionar el personal';
        }
        
        if(!$tipo_id) {
            $errores[] = 'Debe seleccionar un tipo de dotación';
        }
        
        if(!$talla_id) {
            $errores[] = 'Debe seleccionar una talla';
        }
        
        // Validar límite anual (máximo 3 dotaciones por año)
        if($personal_id && $tipo_id) {
            $año_actual = date('Y');
            $entregas_año = EntregaDotacion::contarEntregasAnuales($personal_id, $año_actual);
            
            if($entregas_año >= 3) {
                $errores[] = 'El personal ya recibió el máximo de 3 dotaciones este año';
            }
        }
        
        if(empty($errores)) {
            $solicitud = new SolicitudDotacion([
                'personal_id' => $personal_id,
                'tipo_id' => $tipo_id,
                'talla_id' => $talla_id
            ]);
            
            $resultado = $solicitud->guardar();
            
            if($resultado) {
                // Auditoría
                $personal = Personal::find($personal_id);
                $tipo = TipoDotacion::find($tipo_id);
                $talla = Talla::find($talla_id);
                
                $auditoria = new Auditoria([
                    'usu_id' => $_SESSION['usuario_id'],
                    'aud_modulo' => 'Solicitudes',
                    'aud_accion' => "Solicitud creada: {$personal->personal_nombre} - {$tipo->tipo_nombre} talla {$talla->talla_etiqueta}",
                    'aud_ip' => $_SERVER['REMOTE_ADDR'],
                    'aud_navegador' => substr($_SERVER['HTTP_USER_AGENT'], 0, 100)
                ]);
                $auditoria->guardar();
                
                return ['success' => true];
            }
        }
        
        return ['success' => false, 'errores' => $errores];
    }
    
    public static function entregas() {
        AuthController::verificarLogin();
        
        $solicitudes_pendientes = SolicitudDotacion::obtenerPendientes();
        $entregas = EntregaDotacion::obtenerConDetalles();
        
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $resultado = self::procesarEntrega();
            if($resultado['success']) {
                header('Location: /dotacion/entregas');
                exit;
            }
        }
        
        $router = new Router();
        $router->render('dotacion/entregas', [
            'solicitudes_pendientes' => $solicitudes_pendientes,
            'entregas' => $entregas,
            'errores' => $resultado['errores'] ?? []
        ]);
    }
    
    private static function procesarEntrega() {
        $errores = [];
        
        $solicitud_id = $_POST['solicitud_id'] ?? '';
        
        if(!$solicitud_id) {
            $errores[] = 'Debe seleccionar una solicitud';
        }
        
        $solicitud = SolicitudDotacion::find($solicitud_id);
        if(!$solicitud || $solicitud->estado_entrega == 1) {
            $errores[] = 'Solicitud no válida o ya entregada';
        }
        
        // Verificar inventario disponible
        if($solicitud) {
            $inventario = InventarioDotacion::where('tipo_id', $solicitud->tipo_id, 'talla_id', $solicitud->talla_id);
            if(!$inventario || $inventario->cantidad < 1) {
                $errores[] = 'No hay inventario disponible para esta solicitud';
            }
        }
        
        if(empty($errores)) {
            // Crear entrega
            $entrega = new EntregaDotacion([
                'solicitud_id' => $solicitud_id,
                'usuario_id' => $_SESSION['usuario_id']
            ]);
            
            $resultado = $entrega->guardar();
            
            if($resultado) {
                // Actualizar estado de solicitud
                $solicitud->estado_entrega = 1;
                $solicitud->guardar();
                
                // Reducir inventario
                $inventario->cantidad -= 1;
                $inventario->guardar();
                
                // Auditoría
                $personal = Personal::find($solicitud->personal_id);
                $tipo = TipoDotacion::find($solicitud->tipo_id);
                $talla = Talla::find($solicitud->talla_id);
                
                $auditoria = new Auditoria([
                    'usu_id' => $_SESSION['usuario_id'],
                    'aud_modulo' => 'Entregas',
                    'aud_accion' => "Entrega realizada: {$personal->personal_nombre} - {$tipo->tipo_nombre} talla {$talla->talla_etiqueta}",
                    'aud_ip' => $_SERVER['REMOTE_ADDR'],
                    'aud_navegador' => substr($_SERVER['HTTP_USER_AGENT'], 0, 100)
                ]);
                $auditoria->guardar();
                
                return ['success' => true];
            }
        }
        
        return ['success' => false, 'errores' => $errores];
    }
}