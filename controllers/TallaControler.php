<?php

namespace Controllers;
use MVC\Router;
use Model\Talla;
use Model\Auditoria;
use Exception;

class TallaController {
    
    public static function index(Router $router) {
        AuthController::verificarLogin();
        $router->render('tallas/index', []);
    }
    
    public static function obtenerAPI() {
        header('Content-Type: application/json');
        AuthController::verificarLogin();
        
        try {
            $tallas = Talla::where('talla_situacion', 1);
            echo json_encode($tallas);
        } catch (Exception $e) {
            echo json_encode([
                'resultado' => false,
                'mensaje' => 'Error al obtener tallas'
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
        
        $etiqueta = isset($_POST['talla_etiqueta']) ? trim($_POST['talla_etiqueta']) : '';
        
        // Validaciones
        if(strlen(trim($etiqueta)) < 1 || strlen(trim($etiqueta)) > 10) {
            $errores[] = 'La etiqueta debe tener entre 1 y 10 caracteres';
        }
        
        // Validar formato de talla simplificado
        $etiqueta = strtoupper(trim($etiqueta));
        if(!is_numeric($etiqueta) && !in_array($etiqueta, ['XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL'])) {
            $errores[] = 'Formato de talla no válido (use números 35-50 o XS, S, M, L, XL, XXL)';
        }
        
        // Verificar etiqueta única usando SQL directo
        $query = "SELECT COUNT(*) as total FROM morataya_tallas WHERE talla_etiqueta = '{$etiqueta}' AND talla_situacion = 1";
        $resultado = Talla::fetchFirst($query);
        if(($resultado['total'] ?? 0) > 0) {
            $errores[] = 'Ya existe una talla con esa etiqueta';
        }
        
        if(empty($errores)) {
            try {
                $talla = new Talla([
                    'talla_etiqueta' => strtoupper($etiqueta)
                ]);
                
                $resultado = $talla->guardar();
                
                if($resultado) {
                    $auditoria = new Auditoria([
                        'usu_id' => $_SESSION['usuario_id'],
                        'aud_modulo' => 'Tallas',
                        'aud_accion' => 'Creación de talla: ' . $etiqueta,
                        'aud_ip' => $_SERVER['REMOTE_ADDR'],
                        'aud_navegador' => substr($_SERVER['HTTP_USER_AGENT'], 0, 100)
                    ]);
                    $auditoria->guardar();
                    
                    echo json_encode([
                        'resultado' => true,
                        'mensaje' => 'Talla creada correctamente'
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
        
        if(!isset($_GET['talla_id'])) {
            echo json_encode(['resultado' => false, 'mensaje' => 'ID no proporcionado']);
            return;
        }
        
        $id = filter_var($_GET['talla_id'], FILTER_VALIDATE_INT);
        
        if(!$id) {
            echo json_encode(['resultado' => false, 'mensaje' => 'ID no válido']);
            return;
        }
        
        try {
            $talla = Talla::find($id);
            
            if(!$talla) {
                echo json_encode(['resultado' => false, 'mensaje' => 'Talla no encontrada']);
                return;
            }
            
            $etiqueta = $talla->talla_etiqueta;
            
            $talla->talla_situacion = 0;
            $resultado = $talla->guardar();
            
            if($resultado) {
                $auditoria = new Auditoria([
                    'usu_id' => $_SESSION['usuario_id'],
                    'aud_modulo' => 'Tallas',
                    'aud_accion' => 'Eliminación de talla: ' . $etiqueta,
                    'aud_ip' => $_SERVER['REMOTE_ADDR'],
                    'aud_navegador' => substr($_SERVER['HTTP_USER_AGENT'], 0, 100)
                ]);
                $auditoria->guardar();
                
                echo json_encode([
                    'resultado' => true,
                    'mensaje' => 'Talla eliminada correctamente'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'resultado' => false,
                'mensaje' => 'Error al eliminar talla',
                'detalle' => $e->getMessage()
            ]);
        }
    }
}