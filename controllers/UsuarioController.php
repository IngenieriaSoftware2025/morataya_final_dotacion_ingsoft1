<?php

namespace Controllers;
use MVC\Router;
use Model\Usuario;
use Model\Rol;
use Model\Permiso;
use Model\Auditoria;
use Exception;

class UsuarioController {
    
    public static function index(Router $router) {
        AuthController::verificarLogin();
        $router->render('usuarios/index', []);
    }
    
    public static function crear(Router $router) {
        AuthController::verificarLogin();
        $router->render('usuarios/crear', []);
    }
    
    public static function obtenerAPI() {
        header('Content-Type: application/json');
        AuthController::verificarLogin();
        
        try {
            $usuarios = Usuario::all();
            echo json_encode($usuarios);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'resultado' => false,
                'mensaje' => 'Error al obtener usuarios',
                'detalle' => $e->getMessage()
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
        
        $nombre = isset($_POST['usu_nombre']) ? trim($_POST['usu_nombre']) : '';
        $codigo = isset($_POST['usu_codigo']) ? (int)$_POST['usu_codigo'] : 0;
        $correo = isset($_POST['usu_correo']) ? trim($_POST['usu_correo']) : '';
        $password = isset($_POST['usu_password']) ? $_POST['usu_password'] : '';
        $roles_seleccionados = isset($_POST['roles']) ? $_POST['roles'] : [];
        
        if(!validarTexto($nombre, 3, 100)) {
            $errores[] = 'El nombre debe tener entre 3 y 100 caracteres';
        }
        
        if(!validarNumero($codigo, 1000, 999999)) {
            $errores[] = 'El código debe ser un número entre 1000 y 999999';
        }
        
        if($correo && !validarCorreo($correo)) {
            $errores[] = 'El correo electrónico no es válido';
        }
        
        if(empty($password)) {
            $errores[] = 'La contraseña es obligatoria';
        }
        
        if($password && strlen($password) < 6) {
            $errores[] = 'La contraseña debe tener al menos 6 caracteres';
        }
        
        $usuarioExistente = Usuario::where('usu_codigo', $codigo);
        if($usuarioExistente) {
            $errores[] = 'El código de usuario ya existe';
        }
        
        $fotografia = '';
        if(isset($_FILES['usu_fotografia']) && $_FILES['usu_fotografia']['error'] === 0) {
            $resultado_foto = subirFotografia($_FILES['usu_fotografia'], 'storage/fotosUsuarios/');
            if($resultado_foto === false) {
                $errores[] = 'Error al subir la fotografía';
            } else {
                $fotografia = $resultado_foto;
            }
        }
        
        if(empty($errores)) {
            try {
                $usuario = new Usuario([
                    'usu_nombre' => $nombre,
                    'usu_codigo' => $codigo,
                    'usu_password' => password_hash($password, PASSWORD_DEFAULT),
                    'usu_correo' => $correo,
                    'usu_fotografia' => $fotografia
                ]);
                
                $resultado = $usuario->guardar();
                
                if($resultado) {
                    foreach($roles_seleccionados as $rol_id) {
                        $permiso = new Permiso([
                            'permiso_usuario' => $usuario->usu_id,
                            'permiso_rol' => $rol_id
                        ]);
                        $permiso->guardar();
                    }
                    
                    $auditoria = new Auditoria([
                        'usu_id' => $_SESSION['usuario_id'],
                        'aud_modulo' => 'Usuarios',
                        'aud_accion' => 'Creación de usuario: ' . $nombre,
                        'aud_ip' => $_SERVER['REMOTE_ADDR'],
                        'aud_navegador' => substr($_SERVER['HTTP_USER_AGENT'], 0, 100)
                    ]);
                    $auditoria->guardar();
                    
                    echo json_encode([
                        'resultado' => true,
                        'mensaje' => 'Usuario creado correctamente'
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
        
        if(!isset($_GET['usu_id'])) {
            echo json_encode(['resultado' => false, 'mensaje' => 'ID no proporcionado']);
            return;
        }
        
        $id = filter_var($_GET['usu_id'], FILTER_VALIDATE_INT);
        
        if(!$id) {
            echo json_encode(['resultado' => false, 'mensaje' => 'ID no válido']);
            return;
        }
        
        try {
            $usuario = Usuario::find($id);
            
            if(!$usuario) {
                echo json_encode(['resultado' => false, 'mensaje' => 'Usuario no encontrado']);
                return;
            }
            
            $nombre = $usuario->usu_nombre;
            $resultado = $usuario->eliminar();
            
            if($resultado) {
                $auditoria = new Auditoria([
                    'usu_id' => $_SESSION['usuario_id'],
                    'aud_modulo' => 'Usuarios',
                    'aud_accion' => 'Eliminación de usuario: ' . $nombre,
                    'aud_ip' => $_SERVER['REMOTE_ADDR'],
                    'aud_navegador' => substr($_SERVER['HTTP_USER_AGENT'], 0, 100)
                ]);
                $auditoria->guardar();
                
                echo json_encode([
                    'resultado' => true,
                    'mensaje' => 'Usuario eliminado correctamente'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'resultado' => false,
                'mensaje' => 'Error al eliminar usuario',
                'detalle' => $e->getMessage()
            ]);
        }
    }
}