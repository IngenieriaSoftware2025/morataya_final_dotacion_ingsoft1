<?php

namespace Controllers;
use MVC\Router;
use Model\Rol;
use Model\Auditoria;

class RolController {
    
    public static function index(Router $router) {
        AuthController::verificarLogin();
        $router->render('roles/index', []);
    }
    
    public static function obtenerAPI() {
        header('Content-Type: application/json');
        AuthController::verificarLogin();
        
        try {
            $roles = Rol::all();
            echo json_encode($roles);
        } catch (Exception $e) {
            echo json_encode([
                'resultado' => false,
                'mensaje' => 'Error al obtener roles'
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
        
        $nombre = isset($_POST['rol_nombre']) ? trim($_POST['rol_nombre']) : '';
        $nombre_ct = isset($_POST['rol_nombre_ct']) ? trim($_POST['rol_nombre_ct']) : '';
        
        if(!validarTexto($nombre, 3, 75)) {
            $errores[] = 'El nombre debe tener entre 3 y 75 caracteres';
        }
        
        if(empty($errores)) {
            try {
                $rol = new Rol([
                    'rol_nombre' => $nombre,
                    'rol_nombre_ct' => $nombre_ct
                ]);
                
                $resultado = $rol->guardar();
                
                if($resultado) {
                    $auditoria = new Auditoria([
                        'usu_id' => $_SESSION['usuario_id'],
                        'aud_modulo' => 'Roles',
                        'aud_accion' => 'Creación de rol: ' . $nombre,
                        'aud_ip' => $_SERVER['REMOTE_ADDR'],
                        'aud_navegador' => substr($_SERVER['HTTP_USER_AGENT'], 0, 100)
                    ]);
                    $auditoria->guardar();
                    
                    echo json_encode([
                        'resultado' => true,
                        'mensaje' => 'Rol creado correctamente'
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
}
?>