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
            $query = "SELECT * FROM morataya_personal WHERE personal_situacion = 1 ORDER BY personal_nombre";
            $personal = Personal::fetchArray($query);
            echo json_encode($personal);
        } catch (Exception $e) {
            echo json_encode([
                'resultado' => false,
                'mensaje' => 'Error al obtener personal: ' . $e->getMessage()
            ]);
        }
    }
    
    public static function obtenerPorIdAPI() {
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
            $query = "SELECT * FROM morataya_personal WHERE personal_id = {$id} AND personal_situacion = 1";
            $personal = Personal::fetchFirst($query);
            
            if(!$personal) {
                echo json_encode(['resultado' => false, 'mensaje' => 'Personal no encontrado']);
                return;
            }
            
            echo json_encode([
                'resultado' => true,
                'personal' => $personal
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'resultado' => false,
                'mensaje' => 'Error al obtener personal: ' . $e->getMessage()
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
        $esEdicion = !empty($_POST['personal_id']);
        
        $personal_id = $esEdicion ? filter_var($_POST['personal_id'], FILTER_VALIDATE_INT) : null;
        $nombre = isset($_POST['personal_nombre']) ? trim($_POST['personal_nombre']) : '';
        $cui = isset($_POST['personal_cui']) ? trim($_POST['personal_cui']) : '';
        $puesto = isset($_POST['personal_puesto']) ? trim($_POST['personal_puesto']) : '';
        $fecha_ingreso = isset($_POST['personal_fecha_ingreso']) ? $_POST['personal_fecha_ingreso'] : date('Y-m-d');
        
        if(strlen($nombre) < 3 || strlen($nombre) > 100) {
            $errores[] = 'El nombre debe tener entre 3 y 100 caracteres';
        }
        
        if(strlen($cui) !== 13 || !is_numeric($cui)) {
            $errores[] = 'El CUI debe tener exactamente 13 dígitos numéricos';
        }
        
        if(strlen($puesto) < 3 || strlen($puesto) > 100) {
            $errores[] = 'El puesto debe tener entre 3 y 100 caracteres';
        }
        
        if(!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_ingreso)) {
            $errores[] = 'La fecha de ingreso no es válida';
        }
        
        if(strtotime($fecha_ingreso) > time()) {
            $errores[] = 'La fecha de ingreso no puede ser futura';
        }
        
        $condicionCUI = $esEdicion ? "AND personal_id != {$personal_id}" : "";
        $query = "SELECT COUNT(*) as total FROM morataya_personal WHERE personal_cui = '{$cui}' AND personal_situacion = 1 {$condicionCUI}";
        $resultado = Personal::fetchFirst($query);
        
        if(($resultado['total'] ?? 0) > 0) {
            $errores[] = 'El CUI ya está registrado para otra persona';
        }
        
        if(empty($errores)) {
            try {
                if($esEdicion) {
                    $query = "SELECT * FROM morataya_personal WHERE personal_id = {$personal_id} AND personal_situacion = 1";
                    $personalData = Personal::fetchFirst($query);
                    
                    if(!$personalData) {
                        echo json_encode(['resultado' => false, 'mensaje' => 'Personal no encontrado']);
                        return;
                    }
                    
                    $nombreAnterior = $personalData['personal_nombre'];
                    
                    $sqlUpdate = "UPDATE morataya_personal SET 
                                 personal_nombre = '{$nombre}',
                                 personal_cui = '{$cui}',
                                 personal_puesto = '{$puesto}',
                                 personal_fecha_ingreso = '{$fecha_ingreso}'
                                 WHERE personal_id = {$personal_id}";
                    
                    $resultado = Personal::SQL($sqlUpdate);
                    
                    if($resultado) {
                        try {
                            $auditoria = new Auditoria([
                                'usu_id' => $_SESSION['usuario_id'] ?? 1,
                                'aud_usuario_nombre' => $_SESSION['usuario_nombre'] ?? 'Sistema',
                                'aud_modulo' => 'Personal',
                                'aud_accion' => 'Actualización',
                                'aud_descripcion' => "Personal actualizado: {$nombreAnterior} -> {$nombre}",
                                'aud_ruta' => '/personal',
                                'aud_ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                                'aud_navegador' => substr($_SERVER['HTTP_USER_AGENT'] ?? 'N/A', 0, 100)
                            ]);
                            $auditoria->guardar();
                        } catch (Exception $e) {
                            error_log("Error auditoría (ignorado): " . $e->getMessage());
                        }
                        
                        echo json_encode([
                            'resultado' => true,
                            'mensaje' => 'Personal actualizado correctamente'
                        ]);
                        return;
                    }
                } else {
                    $personal = new Personal([
                        'personal_nombre' => $nombre,
                        'personal_cui' => $cui,
                        'personal_puesto' => $puesto,
                        'personal_fecha_ingreso' => $fecha_ingreso
                    ]);
                    
                    $resultado = $personal->guardar();
                    
                    if($resultado['resultado'] ?? false) {
                        try {
                            $auditoria = new Auditoria([
                                'usu_id' => $_SESSION['usuario_id'] ?? 1,
                                'aud_usuario_nombre' => $_SESSION['usuario_nombre'] ?? 'Sistema',
                                'aud_modulo' => 'Personal',
                                'aud_accion' => 'Creación',
                                'aud_descripcion' => "Nuevo personal registrado: {$nombre}",
                                'aud_ruta' => '/personal',
                                'aud_ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                                'aud_navegador' => substr($_SERVER['HTTP_USER_AGENT'] ?? 'N/A', 0, 100)
                            ]);
                            $auditoria->guardar();
                        } catch (Exception $e) {
                            error_log("Error auditoría (ignorado): " . $e->getMessage());
                        }
                        
                        echo json_encode([
                            'resultado' => true,
                            'mensaje' => 'Personal creado correctamente'
                        ]);
                        return;
                    }
                }
                
                $errores[] = 'Error al guardar en la base de datos';
                
            } catch (Exception $e) {
                $errores[] = 'Error interno: ' . $e->getMessage();
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
            $query = "SELECT * FROM morataya_personal WHERE personal_id = {$id} AND personal_situacion = 1";
            $personal = Personal::fetchFirst($query);
            
            if(!$personal) {
                echo json_encode(['resultado' => false, 'mensaje' => 'Personal no encontrado']);
                return;
            }
            
            $nombre = $personal['personal_nombre'];
            
            $querySolicitudes = "SELECT COUNT(*) as total FROM morataya_solicitudes_dotacion 
                               WHERE personal_id = {$id} AND estado_entrega = 0 AND solicitud_situacion = 1";
            $resultadoSolicitudes = Personal::fetchFirst($querySolicitudes);
            
            if(($resultadoSolicitudes['total'] ?? 0) > 0) {
                echo json_encode([
                    'resultado' => false, 
                    'mensaje' => 'No se puede eliminar: el personal tiene solicitudes pendientes'
                ]);
                return;
            }
            
            $sqlUpdate = "UPDATE morataya_personal SET personal_situacion = 0 WHERE personal_id = {$id}";
            $resultado = Personal::SQL($sqlUpdate);
            
            if($resultado) {
                try {
                    $auditoria = new Auditoria([
                        'usu_id' => $_SESSION['usuario_id'] ?? 1,
                        'aud_usuario_nombre' => $_SESSION['usuario_nombre'] ?? 'Sistema',
                        'aud_modulo' => 'Personal',
                        'aud_accion' => 'Eliminación',
                        'aud_descripcion' => "Personal eliminado: {$nombre}",
                        'aud_ruta' => '/personal',
                        'aud_ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                        'aud_navegador' => substr($_SERVER['HTTP_USER_AGENT'] ?? 'N/A', 0, 100)
                    ]);
                    $auditoria->guardar();
                } catch (Exception $e) {
                    error_log("Error auditoría (ignorado): " . $e->getMessage());
                }
                
                echo json_encode([
                    'resultado' => true,
                    'mensaje' => 'Personal eliminado correctamente'
                ]);
            } else {
                echo json_encode([
                    'resultado' => false,
                    'mensaje' => 'Error al eliminar personal'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'resultado' => false,
                'mensaje' => 'Error al eliminar personal: ' . $e->getMessage()
            ]);
        }
    }
    
    public static function buscarAPI() {
        header('Content-Type: application/json');
        AuthController::verificarLogin();
        
        $termino = isset($_GET['termino']) ? trim($_GET['termino']) : '';
        
        if(strlen($termino) < 2) {
            echo json_encode([
                'resultado' => false,
                'mensaje' => 'El término de búsqueda debe tener al menos 2 caracteres'
            ]);
            return;
        }
        
        try {
            $query = "SELECT * FROM morataya_personal 
                     WHERE personal_situacion = 1 
                     AND (personal_nombre LIKE '%{$termino}%' 
                          OR personal_cui LIKE '%{$termino}%' 
                          OR personal_puesto LIKE '%{$termino}%')
                     ORDER BY personal_nombre";
            
            $personal = Personal::fetchArray($query);
            
            echo json_encode([
                'resultado' => true,
                'personal' => $personal
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'resultado' => false,
                'mensaje' => 'Error en la búsqueda: ' . $e->getMessage()
            ]);
        }
    }
    
    public static function activarAPI() {
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
            $query = "SELECT * FROM morataya_personal WHERE personal_id = {$id} AND personal_situacion = 0";
            $personal = Personal::fetchFirst($query);
            
            if(!$personal) {
                echo json_encode(['resultado' => false, 'mensaje' => 'Personal no encontrado']);
                return;
            }
            
            $nombre = $personal['personal_nombre'];
            
            $sqlUpdate = "UPDATE morataya_personal SET personal_situacion = 1 WHERE personal_id = {$id}";
            $resultado = Personal::SQL($sqlUpdate);
            
            if($resultado) {
                $auditoria = new Auditoria([
                    'usu_id' => $_SESSION['usuario_id'],
                    'aud_usuario_nombre' => $_SESSION['usuario_nombre'],
                    'aud_modulo' => 'Personal',
                    'aud_accion' => 'Activación',
                    'aud_descripcion' => "Personal activado: {$nombre}",
                    'aud_ruta' => '/personal',
                    'aud_ip' => $_SERVER['REMOTE_ADDR'] ?? 'N/A',
                    'aud_navegador' => substr($_SERVER['HTTP_USER_AGENT'] ?? 'N/A', 0, 100)
                ]);
                $auditoria->guardar();
                
                echo json_encode([
                    'resultado' => true,
                    'mensaje' => 'Personal activado correctamente'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'resultado' => false,
                'mensaje' => 'Error al activar personal: ' . $e->getMessage()
            ]);
        }
    }
}