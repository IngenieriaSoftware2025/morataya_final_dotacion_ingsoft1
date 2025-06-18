<?php

namespace Controllers;
use MVC\Router;
use Model\TipoDotacion;
use Model\Auditoria;

class TipoController {
    
    public static function index(Router $router) {
        AuthController::verificarLogin();
        $router->render('tipos/index', []);
    }
    
    public static function obtenerAPI() {
        header('Content-Type: application/json');
        AuthController::verificarLogin();
        
        try {
            $tipos = TipoDotacion::where('tipo_situacion', 1);
            echo json_encode($tipos);
        } catch (Exception $e) {
            echo json_encode([
                'resultado' => false,
                'mensaje' => 'Error al obtener tipos de dotación'
            ]);
        }
    }
    
    public static function guardarAPI() {
        header('Content-Type: application/json');
        AuthController::verificarLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['resultado' => false, 'mensaje' => 'Método no permitido']);
            return;
        }
        
        $errores = [];
        
        $nombre = isset($_POST['tipo_nombre']) ? trim($_POST['tipo_nombre']) : '';
        $descripcion = isset($_POST['tipo_descripcion']) ? trim($_POST['tipo_descripcion']) : '';
        
        // Validaciones
        if(strlen(trim($nombre)) < 3 || strlen(trim($nombre)) > 50) {
            $errores[] = 'El nombre debe tener entre 3 y 50 caracteres';
        }
        
        if($descripcion && strlen(trim($descripcion)) > 100) {
            $errores[] = 'La descripción no puede exceder 100 caracteres';
        }
        
        // Verificar nombre único usando SQL directo
        $query = "SELECT COUNT(*) as total FROM morataya_tipos_dotacion WHERE tipo_nombre = '{$nombre}' AND tipo_situacion = 1";
        $resultado = TipoDotacion::fetchFirst($query);
        if(($resultado['total'] ?? 0) > 0) {
            $errores[] = 'Ya existe un tipo de dotación con ese nombre';
        }
        
        if(empty($errores)) {
            try {
                $tipo = new TipoDotacion([
                    'tipo_nombre' => $nombre,
                    'tipo_descripcion' => $descripcion
                ]);
                
                $resultado = $tipo->guardar();
                
                if($resultado) {
                    $auditoria = new Auditoria([
                        'usu_id' => $_SESSION['usuario_id'],
                        'aud_modulo' => 'Tipos de Dotación',
                        'aud_accion' => 'Creación de tipo: ' . $nombre,
                        'aud_ip' => $_SERVER['REMOTE_ADDR'],
                        'aud_navegador' => substr($_SERVER['HTTP_USER_AGENT'], 0, 100)
                    ]);
                    $auditoria->guardar();
                    
                    echo json_encode([
                        'resultado' => true,
                        'mensaje' => 'Tipo de dotación creado correctamente'
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
    
    public static function eliminarAPI() {
        header('Content-Type: application/json');
        AuthController::verificarLogin();
        
        if(!isset($_GET['tipo_id'])) {
            echo json_encode(['resultado' => false, 'mensaje' => 'ID no proporcionado']);
            return;
        }
        
        $id = filter_var($_GET['tipo_id'], FILTER_VALIDATE_INT);
        
        if(!$id) {
            echo json_encode(['resultado' => false, 'mensaje' => 'ID no válido']);
            return;
        }
        
        try {
            $tipo = TipoDotacion::find($id);
            
            if(!$tipo) {
                echo json_encode(['resultado' => false, 'mensaje' => 'Tipo no encontrado']);
                return;
            }
            
            $nombre = $tipo->tipo_nombre;
            
            // Soft delete
            $tipo->tipo_situacion = 0;
            $resultado = $tipo->guardar();
            
            if($resultado) {
                $auditoria = new Auditoria([
                    'usu_id' => $_SESSION['usuario_id'],
                    'aud_modulo' => 'Tipos de Dotación',
                    'aud_accion' => 'Eliminación de tipo: ' . $nombre,
                    'aud_ip' => $_SERVER['REMOTE_ADDR'],
                    'aud_navegador' => substr($_SERVER['HTTP_USER_AGENT'], 0, 100)
                ]);
                $auditoria->guardar();
                
                echo json_encode([
                    'resultado' => true,
                    'mensaje' => 'Tipo eliminado correctamente'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'resultado' => false,
                'mensaje' => 'Error al eliminar tipo',
                'detalle' => $e->getMessage()
            ]);
        }
    }
}