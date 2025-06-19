<?php

namespace Controllers;
use MVC\Router;
use Model\Usuario;
use Model\Auditoria;
use Exception;

class AuthController {
    
    public static function index(Router $router) {
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $auth = new AuthController();
            $auth->procesarLogin($router);
            return;
        }
        
        $router->render('auth/index', [], 'principal');
    }
    
    public function procesarLogin(Router $router) {
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
            try {
                $query = "SELECT * FROM morataya_usuario WHERE usu_codigo = '{$codigo}' AND usu_situacion = 1";
                
                error_log("Consulta SQL: " . $query);
                
                $resultado = Usuario::fetchFirst($query);
                
                error_log("Resultado de consulta: " . print_r($resultado, true));
                
                if($resultado) {
                    $passwordBD = $resultado['usu_password'] ?? '';
                    
                    error_log("Contraseña ingresada: " . $password);
                    error_log("Contraseña en BD: " . $passwordBD);
                    
                    if($password === $passwordBD || password_verify($password, $passwordBD)) {
                        session_start();
                        $_SESSION['usuario_id'] = $resultado['usu_id'];
                        $_SESSION['usuario_nombre'] = $resultado['usu_nombre'];
                        $_SESSION['usuario_codigo'] = $resultado['usu_codigo'];
                        $_SESSION['usuario_fotografia'] = $resultado['usu_fotografia'] ?? '';
                        $_SESSION['login'] = true;
                        
                        header('Location: index.php?url=dashboard');
                        exit;
                    } else {
                        $errores[] = 'Contraseña incorrecta. Ingresada: ' . $password . ' - BD: ' . substr($passwordBD, 0, 20);
                    }
                } else {
                    $errores[] = 'Usuario no encontrado con código: ' . $codigo;
                }
            } catch (Exception $e) {
                $errores[] = 'Error en el sistema: ' . $e->getMessage();
            }
        }
        
        $router->render('auth/index', ['errores' => $errores], 'principal');
    }
    
    public static function logout() {
        session_start();
        $_SESSION = [];
        session_destroy();
        header('Location: ../index.php');
        exit;
    }
    
    public static function verificarLogin() {
        session_start();
        if(!isset($_SESSION['login']) || !$_SESSION['login']) {
            header('Location: index.php?url=login');
            exit;
        }
    }
}