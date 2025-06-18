<?php

namespace Controllers;
use MVC\Router;
use Model\Personal;
use Model\Auditoria;
use Exception;

class PersonalController {
    
    public static function index(Router $router) {
        AuthController::verificarLogin();
        $router->render('personal/index', []);
    }
    
    public static function crear(Router $router) {
        AuthController::verificarLogin();
        $router->render('personal/crear', []);
    }
    
    public static function obtenerAPI() {
        header('Content-Type: application/json');
        AuthController::verificarLogin();
        
        try {
            $personal = Personal::all();
            echo json_encode($personal);
        } catch (Exception $e) {
            echo json_encode([
                'resultado' => false,
                'mensaje' => 'Error al obtener personal'
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
        
        $nombre = isset($_POST['personal_nombre']) ? trim($_POST['personal_nombre']) : '';
        $cui = isset($_POST['personal_cui']) ? trim($_POST['personal_cui']) : '';
        $puesto = isset($_POST['personal_puesto']) ? trim($_POST['personal_puesto']) : '';
        $fecha_ingreso = isset($_POST['personal_fecha_ingreso']) ? $_POST['personal_fecha_ingreso'] : date('Y-m-d');
        
        // Validaciones
        if(strlen(trim($nombre)) < 3 || strlen(trim($nombre)) > 100) {
            $errores[] = 'El nombre debe tener entre 3 y 100 caracteres';
        }
        
        if(strlen($cui) !== 13 || !is_numeric($cui)) {
            $errores[] = 'El CUI debe tener 13 dígitos';
        }
        
        if(strlen(trim($puesto)) < 3 || strlen(trim($puesto)) > 100) {
            $errores[] = 'El puesto debe tener entre 3 y 100 caracteres';
        }
        
        if(!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_ingreso)) {
            $errores[] = 'La fecha de ingreso no es válida';
        }
        
        // Verificar CUI único usando SQL directo
        $query = "SELECT COUNT(*) as total FROM morataya_personal WHERE personal_cui = '{$cui}' AND personal_situacion = 1";
        $resultado = Personal::fetchFirst($query);
        if(($resultado['total'] ?? 0) > 0) {
            $errores[] = 'El CUI ya está registrado';
        }
        
        if(empty($errores)) {
            try {
                $personal = new Personal([
                    'personal_nombre' => $nombre,
                    'personal_cui' => $cui,
                    'personal_puesto' => $puesto,
                    'personal_fecha_ingreso' => $fecha_ingreso
                ]);
                
                $resultado = $personal->guardar();
                
                if($resultado['resultado'] ?? false) {
                    $auditoria = new Auditoria([
                        'usu_id' => $_SESSION['usuario_id'],
                        'aud_modulo' => 'Personal',
                        'aud_accion' => 'Creación de personal: ' . $nombre,
                        'aud_ip' => $_SERVER['REMOTE_ADDR'],
                        'aud_navegador' => substr($_SERVER['HTTP_USER_AGENT'], 0, 100)
                    ]);
                    $auditoria->guardar();
                    
                    echo json_encode([
                        'resultado' => true,
                        'mensaje' => 'Personal creado correctamente'
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
        
        if(!isset($_GET['personal_id'])) {
            echo json_encode(['resultado' => false, 'mensaje' => 'ID no proporcionado']);
            return;
        }
        
        $id = filter_var($_GET['personal_id'], FILTER_VALIDATE_INT);
        
        if(!$id) {
            echo json_encode(['resultado' => false, 'mensaje' => 'ID no válido']);
            return;
        }
        
        try {
            // Buscar personal usando SQL directo
            $query = "SELECT * FROM morataya_personal WHERE personal_id = {$id} AND personal_situacion = 1";
            $personal = Personal::fetchFirst($query);
            
            if(!$personal) {
                echo json_encode(['resultado' => false, 'mensaje' => 'Personal no encontrado']);
                return;
            }
            
            $nombre = $personal['personal_nombre'];
            
            // Soft delete usando SQL directo
            $sqlUpdate = "UPDATE morataya_personal SET personal_situacion = 0 WHERE personal_id = {$id}";
            $resultado = Personal::SQL($sqlUpdate);
            
            if($resultado) {
                $auditoria = new Auditoria([
                    'usu_id' => $_SESSION['usuario_id'],
                    'aud_modulo' => 'Personal',
                    'aud_accion' => 'Eliminación de personal: ' . $nombre,
                    'aud_ip' => $_SERVER['REMOTE_ADDR'],
                    'aud_navegador' => substr($_SERVER['HTTP_USER_AGENT'], 0, 100)
                ]);
                $auditoria->guardar();
                
                echo json_encode([
                    'resultado' => true,
                    'mensaje' => 'Personal eliminado correctamente'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'resultado' => false,
                'mensaje' => 'Error al eliminar personal',
                'detalle' => $e->getMessage()
            ]);
        }
    }
}