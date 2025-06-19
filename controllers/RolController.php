<?php

namespace Controllers;
use MVC\Router;
use Model\Rol;
use Model\Auditoria;
use Exception;

class RolController {
    
    public static function index(Router $router) {
        AuthController::verificarLogin();
        $router->render('roles/index', []);
    }
    
    public static function obtenerAPI() {
        header('Content-Type: application/json');
        AuthController::verificarLogin();
        
        try {
            $roles = Rol::where('rol_situacion', 1);
            echo json_encode($roles);
        } catch (Exception $e) {
            echo json_encode([
                'resultado' => false,
                'mensaje' => 'Error al obtener roles: ' . $e->getMessage()
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
        
        $id = isset($_POST['rol_id']) ? (int)$_POST['rol_id'] : 0;
        $nombre = isset($_POST['rol_nombre']) ? trim($_POST['rol_nombre']) : '';
        $nombre_ct = isset($_POST['rol_nombre_ct']) ? trim($_POST['rol_nombre_ct']) : '';
        
        if(!validarTexto($nombre, 3, 75)) {
            $errores[] = 'El nombre debe tener entre 3 y 75 caracteres';
        }
        
        if($nombre_ct && !validarTexto($nombre_ct, 1, 25)) {
            $errores[] = 'El nombre corto no puede exceder 25 caracteres';
        }
        
        $query = "SELECT COUNT(*) as total FROM morataya_rol WHERE rol_nombre = '{$nombre}' AND rol_situacion = 1";
        if($id > 0) {
            $query .= " AND rol_id != {$id}";
        }
        $resultado = Rol::fetchFirst($query);
        if(($resultado['total'] ?? 0) > 0) {
            $errores[] = 'Ya existe un rol con ese nombre';
        }
        
        if(empty($errores)) {
            try {
                if($id > 0) {
                    $sqlUpdate = "UPDATE morataya_rol SET 
                        rol_nombre = '{$nombre}',
                        rol_nombre_ct = '{$nombre_ct}'
                        WHERE rol_id = {$id}";
                    $resultado = Rol::SQL($sqlUpdate);
                    $exito = true;
                } else {
                    $rol = new Rol([
                        'rol_nombre' => $nombre,
                        'rol_nombre_ct' => $nombre_ct
                    ]);
                    
                    $resultado = $rol->guardar();
                    $exito = $resultado['resultado'] ?? false;
                }
                
                if($exito) {
                    $accion = $id > 0 ? 'Actualización' : 'Creación';
                    $auditoria = new Auditoria([
                        'usu_id' => $_SESSION['usuario_id'],
                        'aud_modulo' => 'Roles',
                        'aud_accion' => $accion . ' de rol: ' . $nombre,
                        'aud_ip' => $_SERVER['REMOTE_ADDR'],
                        'aud_navegador' => substr($_SERVER['HTTP_USER_AGENT'], 0, 100)
                    ]);
                    $auditoria->guardar();
                    
                    echo json_encode([
                        'resultado' => true,
                        'mensaje' => 'Rol ' . ($id > 0 ? 'actualizado' : 'creado') . ' correctamente'
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
        
        if(!isset($_GET['rol_id'])) {
            echo json_encode(['resultado' => false, 'mensaje' => 'ID no proporcionado']);
            return;
        }
        
        $id = filter_var($_GET['rol_id'], FILTER_VALIDATE_INT);
        
        if(!$id) {
            echo json_encode(['resultado' => false, 'mensaje' => 'ID no válido']);
            return;
        }
        
        try {
            $queryPermisos = "SELECT COUNT(*) as total FROM morataya_permiso WHERE permiso_rol = {$id} AND permiso_situacion = 1";
            $permisos = Rol::fetchFirst($queryPermisos);
            
            if(($permisos['total'] ?? 0) > 0) {
                echo json_encode([
                    'resultado' => false,
                    'mensaje' => 'No se puede eliminar este rol porque tiene usuarios asignados'
                ]);
                return;
            }
            
            $query = "SELECT * FROM morataya_rol WHERE rol_id = {$id} AND rol_situacion = 1";
            $rol = Rol::fetchFirst($query);
            
            if(!$rol) {
                echo json_encode(['resultado' => false, 'mensaje' => 'Rol no encontrado']);
                return;
            }
            
            $nombre = $rol['rol_nombre'];
            
            $sqlUpdate = "UPDATE morataya_rol SET rol_situacion = 0 WHERE rol_id = {$id}";
            $resultado = Rol::SQL($sqlUpdate);
            
            if($resultado) {
                $auditoria = new Auditoria([
                    'usu_id' => $_SESSION['usuario_id'],
                    'aud_modulo' => 'Roles',
                    'aud_accion' => 'Eliminación de rol: ' . $nombre,
                    'aud_ip' => $_SERVER['REMOTE_ADDR'],
                    'aud_navegador' => substr($_SERVER['HTTP_USER_AGENT'], 0, 100)
                ]);
                $auditoria->guardar();
                
                echo json_encode([
                    'resultado' => true,
                    'mensaje' => 'Rol eliminado correctamente'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'resultado' => false,
                'mensaje' => 'Error al eliminar rol',
                'detalle' => $e->getMessage()
            ]);
        }
    }
}