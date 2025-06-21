<?php

namespace Controllers;

use MVC\Router;
use Model\Usuario;
use Model\Rol;
use Model\Permiso;
use Model\Auditoria;
use Exception;

class UsuarioController
{

    public static function index(Router $router)
    {
        AuthController::verificarLogin();
        $router->render('usuarios/index', []);
    }

    public static function crear(Router $router)
    {
        AuthController::verificarLogin();
        $router->render('usuarios/crear', []);
    }

    public static function editar(Router $router)
    {
        AuthController::verificarLogin();

        $id = $_GET['id'] ?? 0;
        if (!$id) {
            header('Location: /usuarios');
            exit;
        }

        $router->render('usuarios/editar', ['usuario_id' => $id]);
    }

    public static function obtenerAPI()
    {
        // Limpiar cualquier output previo
        if (ob_get_level()) {
            ob_clean();
        }
        
        header('Content-Type: application/json; charset=utf-8');
        AuthController::verificarLogin();

        try {
            $query = "
            SELECT u.usu_id, u.usu_nombre, u.usu_codigo, u.usu_correo, u.usu_fotografia, u.usu_situacion
            FROM morataya_usuario u
            WHERE u.usu_situacion = 1
            ORDER BY u.usu_nombre
        ";

            $usuarios = Usuario::fetchArray($query);

            foreach ($usuarios as &$usuario) {
                $queryRoles = "
                SELECT r.rol_nombre 
                FROM morataya_permiso p 
                INNER JOIN morataya_rol r ON p.permiso_rol = r.rol_id 
                WHERE p.permiso_usuario = {$usuario['usu_id']} 
                AND p.permiso_situacion = 1 
                AND r.rol_situacion = 1
            ";

                $roles = Usuario::fetchArray($queryRoles);

                if (!empty($roles) && is_array($roles)) {
                    $rolesNombres = array_column($roles, 'rol_nombre');
                    $usuario['roles_nombres'] = implode(', ', $rolesNombres);
                } else {
                    $usuario['roles_nombres'] = 'Sin roles';
                }
            }

            echo json_encode($usuarios, JSON_UNESCAPED_UNICODE);
            exit;
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'resultado' => false,
                'mensaje' => 'Error al obtener usuarios: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    public static function obtenerPorIdAPI()
    {
        if (ob_get_level()) {
            ob_clean();
        }
        
        header('Content-Type: application/json; charset=utf-8');
        AuthController::verificarLogin();

        $id = $_GET['id'] ?? 0;
        if (!$id) {
            echo json_encode(['resultado' => false, 'mensaje' => 'ID no proporcionado'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        try {
            $query = "SELECT * FROM morataya_usuario WHERE usu_id = {$id} AND usu_situacion = 1";
            $usuario = Usuario::fetchFirst($query);

            if (!$usuario) {
                echo json_encode(['resultado' => false, 'mensaje' => 'Usuario no encontrado'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $queryRoles = "
            SELECT r.rol_id 
            FROM morataya_permiso p 
            INNER JOIN morataya_rol r ON p.permiso_rol = r.rol_id 
            WHERE p.permiso_usuario = {$id} AND p.permiso_situacion = 1 AND r.rol_situacion = 1
        ";
            $roles = Usuario::fetchArray($queryRoles);
            $usuario['roles'] = array_column($roles, 'rol_id');

            echo json_encode(['resultado' => true, 'usuario' => $usuario], JSON_UNESCAPED_UNICODE);
            exit;
        } catch (Exception $e) {
            echo json_encode([
                'resultado' => false,
                'mensaje' => 'Error al obtener usuario: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
    
    public static function guardarAPI()
    {
        if (ob_get_level()) {
            ob_clean();
        }
        
        header('Content-Type: application/json; charset=utf-8');
        AuthController::verificarLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['resultado' => false, 'mensaje' => 'Método no permitido'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $errores = [];

        $id = isset($_POST['usu_id']) ? (int)$_POST['usu_id'] : 0;
        $nombre = isset($_POST['usu_nombre']) ? trim($_POST['usu_nombre']) : '';
        $codigo = isset($_POST['usu_codigo']) ? (int)$_POST['usu_codigo'] : 0;
        $correo = isset($_POST['usu_correo']) ? trim($_POST['usu_correo']) : '';
        $password = isset($_POST['usu_password']) ? $_POST['usu_password'] : '';
        $roles_seleccionados = isset($_POST['roles']) ? $_POST['roles'] : [];

        if (!validarTexto($nombre, 3, 100)) {
            $errores[] = 'El nombre debe tener entre 3 y 100 caracteres';
        }

        if (!validarNumero($codigo, 1000, 999999)) {
            $errores[] = 'El código debe ser un número entre 1000 y 999999';
        }

        if ($correo && !validarCorreo($correo)) {
            $errores[] = 'El correo electrónico no es válido';
        }

        if ($id == 0 && empty($password)) {
            $errores[] = 'La contraseña es obligatoria para usuarios nuevos';
        }

        if ($password && strlen($password) < 6) {
            $errores[] = 'La contraseña debe tener al menos 6 caracteres';
        }

        $query = "SELECT COUNT(*) as total FROM morataya_usuario WHERE usu_codigo = {$codigo} AND usu_situacion = 1";
        if ($id > 0) {
            $query .= " AND usu_id != {$id}";
        }
        $usuarioExistente = Usuario::fetchFirst($query);
        if (($usuarioExistente['total'] ?? 0) > 0) {
            $errores[] = 'El código de usuario ya existe';
        }

        if ($correo) {
            $queryCorreo = "SELECT COUNT(*) as total FROM morataya_usuario WHERE usu_correo = '{$correo}' AND usu_situacion = 1";
            if ($id > 0) {
                $queryCorreo .= " AND usu_id != {$id}";
            }
            $correoExistente = Usuario::fetchFirst($queryCorreo);
            if (($correoExistente['total'] ?? 0) > 0) {
                $errores[] = 'El correo electrónico ya está registrado';
            }
        }

        $fotografia = '';
        if (isset($_FILES['usu_fotografia']) && $_FILES['usu_fotografia']['error'] === 0) {
            $resultado_foto = subirFotografia($_FILES['usu_fotografia'], 'storage/fotosUsuarios/');
            if ($resultado_foto === false) {
                $errores[] = 'Error al subir la fotografía';
            } else {
                $fotografia = $resultado_foto;
            }
        }

        if (!empty($errores)) {
            echo json_encode([
                'resultado' => false,
                'mensaje' => 'Errores de validación',
                'errores' => $errores
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        try {
            $exito = false;
            $usuario_id = 0;
            
            if ($id > 0) {
                $campos = [
                    "usu_nombre = '{$nombre}'",
                    "usu_codigo = {$codigo}",
                    "usu_correo = '{$correo}'"
                ];

                if ($password) {
                    $campos[] = "usu_password = '" . password_hash($password, PASSWORD_DEFAULT) . "'";
                }

                if ($fotografia) {
                    $campos[] = "usu_fotografia = '{$fotografia}'";
                }

                $sqlUpdate = "UPDATE morataya_usuario SET " . implode(', ', $campos) . " WHERE usu_id = {$id}";
                $resultado = Usuario::SQL($sqlUpdate);
                $exito = true;
                $usuario_id = $id;
            } else {
                $usuario = new Usuario([
                    'usu_nombre' => $nombre,
                    'usu_codigo' => $codigo,
                    'usu_password' => password_hash($password, PASSWORD_DEFAULT),
                    'usu_correo' => $correo,
                    'usu_fotografia' => $fotografia
                ]);

                $resultado = $usuario->guardar();
                $exito = $resultado['resultado'] ?? false;
                $usuario_id = $resultado['id'] ?? 0;
            }

            if ($exito && $usuario_id) {
                if ($id > 0) {
                    $sqlDeleteRoles = "UPDATE morataya_permiso SET permiso_situacion = 0 WHERE permiso_usuario = {$usuario_id}";
                    Usuario::SQL($sqlDeleteRoles);
                }

                foreach ($roles_seleccionados as $rol_id) {
                    $permiso = new Permiso([
                        'permiso_usuario' => $usuario_id,
                        'permiso_rol' => $rol_id
                    ]);
                    $permiso->guardar();
                }

                try {
                    $accion = $id > 0 ? 'Actualización' : 'Creación';
                    $auditoria = new Auditoria([
                        'usu_id' => $_SESSION['usuario_id'],
                        'aud_modulo' => 'Usuarios',
                        'aud_accion' => $accion . ' de usuario: ' . $nombre,
                        'aud_ip' => $_SERVER['REMOTE_ADDR'],
                        'aud_navegador' => substr($_SERVER['HTTP_USER_AGENT'], 0, 100)
                    ]);
                    $auditoria->guardar();
                } catch (Exception $e) {
                    error_log("Error en auditoría: " . $e->getMessage());
                }

                echo json_encode([
                    'resultado' => true,
                    'mensaje' => 'Usuario ' . ($id > 0 ? 'actualizado' : 'creado') . ' correctamente'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            } else {
                throw new Exception('Error al guardar en la base de datos');
            }
        } catch (Exception $e) {
            echo json_encode([
                'resultado' => false,
                'mensaje' => 'Error al guardar usuario',
                'detalle' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    public static function eliminarAPI()
    {
        if (ob_get_level()) {
            ob_clean();
        }
        
        header('Content-Type: application/json; charset=utf-8');
        AuthController::verificarLogin();

        if (!isset($_GET['usu_id'])) {
            echo json_encode(['resultado' => false, 'mensaje' => 'ID no proporcionado'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $id = filter_var($_GET['usu_id'], FILTER_VALIDATE_INT);

        if (!$id) {
            echo json_encode(['resultado' => false, 'mensaje' => 'ID no válido'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if ($id == $_SESSION['usuario_id']) {
            echo json_encode(['resultado' => false, 'mensaje' => 'No puedes eliminar tu propio usuario'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        try {
            $query = "SELECT * FROM morataya_usuario WHERE usu_id = {$id} AND usu_situacion = 1";
            $usuario = Usuario::fetchFirst($query);

            if (!$usuario) {
                echo json_encode(['resultado' => false, 'mensaje' => 'Usuario no encontrado'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $nombre = $usuario['usu_nombre'];

            $sqlUpdateUsuario = "UPDATE morataya_usuario SET usu_situacion = 0 WHERE usu_id = {$id}";
            $sqlUpdatePermisos = "UPDATE morataya_permiso SET permiso_situacion = 0 WHERE permiso_usuario = {$id}";

            Usuario::SQL($sqlUpdateUsuario);
            Usuario::SQL($sqlUpdatePermisos);

            try {
                $auditoria = new Auditoria([
                    'usu_id' => $_SESSION['usuario_id'],
                    'aud_modulo' => 'Usuarios',
                    'aud_accion' => 'Eliminación de usuario: ' . $nombre,
                    'aud_ip' => $_SERVER['REMOTE_ADDR'],
                    'aud_navegador' => substr($_SERVER['HTTP_USER_AGENT'], 0, 100)
                ]);
                $auditoria->guardar();
            } catch (Exception $e) {
                error_log("Error en auditoría: " . $e->getMessage());
            }

            echo json_encode([
                'resultado' => true,
                'mensaje' => 'Usuario eliminado correctamente'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        } catch (Exception $e) {
            echo json_encode([
                'resultado' => false,
                'mensaje' => 'Error al eliminar usuario',
                'detalle' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
}