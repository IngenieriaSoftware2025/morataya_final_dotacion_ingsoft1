<?php
// controllers/AuthController.php
namespace Controllers;
use Model\Usuario;
use Model\Auditoria;
use MVC\Router;
use Model\ActiveRecord[];


class AuthController {
    
    public static function login() {
        $router = new Router();
        
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $auth = new AuthController();
            $auth->procesarLogin();
        }
        
        $router->render('auth/login', [], 'auth');
    }
    
    public function procesarLogin() {
        $errores = [];
        
        $codigo = $_POST['codigo'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if(!$codigo) {
            $errores[] = 'El código de usuario es obligatorio';
        }
        
        if(!$password) {
            $errores[] = 'La contraseña es obligatoria';
        }
        
        if(empty($errores)) {
            $usuario = Usuario::where('usu_codigo', $codigo);
            
            if($usuario && password_verify($password, $usuario->usu_password) && $usuario->usu_situacion == 1) {
                session_start();
                $_SESSION['usuario_id'] = $usuario->usu_id;
                $_SESSION['usuario_nombre'] = $usuario->usu_nombre;
                $_SESSION['login'] = true;
                
                // Registrar auditoría
                $auditoria = new Auditoria([
                    'usu_id' => $usuario->usu_id,
                    'aud_modulo' => 'Autenticación',
                    'aud_accion' => 'Inicio de sesión exitoso',
                    'aud_ip' => $_SERVER['REMOTE_ADDR'],
                    'aud_navegador' => substr($_SERVER['HTTP_USER_AGENT'], 0, 100)
                ]);
                $auditoria->guardar();
                
                header('Location: /dashboard');
                exit;
            } else {
                $errores[] = 'Credenciales incorrectas';
            }
        }
        
        $router = new Router();
        $router->render('auth/login', ['errores' => $errores], 'auth');
    }
    
    public static function logout() {
        session_start();
        
        if(isset($_SESSION['usuario_id'])) {
            $auditoria = new Auditoria([
                'usu_id' => $_SESSION['usuario_id'],
                'aud_modulo' => 'Autenticación',
                'aud_accion' => 'Cierre de sesión',
                'aud_ip' => $_SERVER['REMOTE_ADDR'],
                'aud_navegador' => substr($_SERVER['HTTP_USER_AGENT'], 0, 100)
            ]);
            $auditoria->guardar();
        }
        
        $_SESSION = [];
        session_destroy();
        header('Location: /');
        exit;
    }
    
    public static function verificarLogin() {
        session_start();
        if(!isset($_SESSION['login']) || !$_SESSION['login']) {
            header('Location: /login');
            exit;
        }
    }
}