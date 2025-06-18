<?php

namespace Controllers;
use MVC\Router;
use Model\Usuario;
use Model\Auditoria;

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
                // Buscar usuario por código
                $query = "SELECT * FROM morataya_usuario WHERE usu_codigo = '{$codigo}' AND usu_situacion = 1";
                $resultado = Usuario::fetchFirst($query);
                
                if($resultado) {
                    // Verificar contraseña (primero sin hash, luego con hash)
                    if($password === $resultado['usu_password'] || password_verify($password, $resultado['usu_password'])) {
                        // Login exitoso
                        session_start();
                        $_SESSION['usuario_id'] = $resultado['usu_id'];
                        $_SESSION['usuario_nombre'] = $resultado['usu_nombre'];
                        $_SESSION['usuario_codigo'] = $resultado['usu_codigo'];
                        $_SESSION['usuario_fotografia'] = $resultado['usu_fotografia'] ?? '';
                        $_SESSION['login'] = true;
                        
                        // Redirigir a estadísticas
                        header('Location: index.php?url=dashboard');
                        exit;
                    } else {
                        $errores[] = 'Contraseña incorrecta';
                    }
                } else {
                    $errores[] = 'Usuario no encontrado';
                }
            } catch (Exception $e) {
                $errores[] = 'Error en el sistema: ' . $e->getMessage();
            }
        }
        
        // Mostrar formulario con errores
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