<?php

namespace Controllers;

use MVC\Router;
use Model\TipoDotacion;
use Model\Talla;
use Model\InventarioDotacion;
use Model\SolicitudDotacion;
use Model\EntregaDotacion;
use Model\Personal;
use Model\Auditoria;
use Exception;

class DotacionController
{

    public static function inventario(Router $router)
    {
        AuthController::verificarLogin();
        $router->render('inventario/index', []);
    }

    public static function obtenerInventarioAPI()
    {
        if (ob_get_level()) {
            ob_clean();
        }

        header('Content-Type: application/json; charset=utf-8');
        AuthController::verificarLogin();

        try {
            $inventarios = self::obtenerInventarioConDetalles();
            echo json_encode($inventarios, JSON_UNESCAPED_UNICODE);
            exit;
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'resultado' => false,
                'mensaje' => 'Error al obtener inventario: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    public static function guardarInventarioAPI()
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

        $tipo_id = isset($_POST['tipo_id']) ? filter_var($_POST['tipo_id'], FILTER_VALIDATE_INT) : 0;
        $talla_id = isset($_POST['talla_id']) ? filter_var($_POST['talla_id'], FILTER_VALIDATE_INT) : 0;
        $cantidad = isset($_POST['cantidad']) ? filter_var($_POST['cantidad'], FILTER_VALIDATE_INT) : 0;

        if (!$tipo_id || $tipo_id <= 0) {
            $errores[] = 'Debe seleccionar un tipo de dotación válido';
        }

        if (!$talla_id || $talla_id <= 0) {
            $errores[] = 'Debe seleccionar una talla válida';
        }

        if (!$cantidad || $cantidad < 1 || $cantidad > 9999) {
            $errores[] = 'La cantidad debe ser entre 1 y 9999';
        }

        if ($tipo_id) {
            try {
                $queryTipo = "SELECT COUNT(*) as total FROM morataya_tipos_dotacion WHERE tipo_id = {$tipo_id} AND tipo_situacion = 1";
                $tipoExiste = TipoDotacion::fetchFirst($queryTipo);
                if (($tipoExiste['total'] ?? 0) == 0) {
                    $errores[] = 'El tipo de dotación seleccionado no es válido o no existe';
                }
            } catch (Exception $e) {
                $errores[] = 'Error al validar tipo de dotación: ' . $e->getMessage();
            }
        }

        if ($talla_id) {
            try {
                $queryTalla = "SELECT COUNT(*) as total FROM morataya_tallas WHERE talla_id = {$talla_id} AND talla_situacion = 1";
                $tallaExiste = Talla::fetchFirst($queryTalla);
                if (($tallaExiste['total'] ?? 0) == 0) {
                    $errores[] = 'La talla seleccionada no es válida o no existe';
                }
            } catch (Exception $e) {
                $errores[] = 'Error al validar talla: ' . $e->getMessage();
            }
        }

        if (empty($errores)) {
            try {
                $inventarioExistente = self::buscarInventarioExistentePorSQL($tipo_id, $talla_id);

                if ($inventarioExistente) {
                    $nuevaCantidad = $inventarioExistente['cantidad'] + $cantidad;
                    $sqlUpdate = "UPDATE morataya_inventario_dotacion SET cantidad = {$nuevaCantidad} WHERE inv_id = {$inventarioExistente['inv_id']}";
                    $resultado = InventarioDotacion::SQL($sqlUpdate);
                    $exito = true;
                    $accion = "Actualización de inventario - agregó {$cantidad} unidades";
                } else {
                    $inventario = new InventarioDotacion([
                        'tipo_id' => $tipo_id,
                        'talla_id' => $talla_id,
                        'cantidad' => $cantidad
                    ]);
                    $resultado = $inventario->guardar();
                    $exito = $resultado['resultado'] ?? false;
                    $accion = "Creación de inventario - {$cantidad} unidades";
                }

                if ($exito) {
                    try {
                        $auditoria = new Auditoria([
                            'usu_id' => $_SESSION['usuario_id'],
                            'aud_modulo' => 'Inventario',
                            'aud_accion' => $accion,
                            'aud_ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                            'aud_navegador' => substr($_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido', 0, 100)
                        ]);
                        $auditoria->guardar();
                    } catch (Exception $e) {
                        error_log("Error en auditoría de inventario: " . $e->getMessage());
                    }

                    echo json_encode([
                        'resultado' => true,
                        'mensaje' => 'Inventario agregado correctamente'
                    ], JSON_UNESCAPED_UNICODE);
                    exit;
                } else {
                    $errores[] = 'Error al guardar en la base de datos';
                    if (isset($resultado['mensaje'])) {
                        $errores[] = $resultado['mensaje'];
                    }
                }
            } catch (Exception $e) {
                $errores[] = 'Error al procesar la solicitud: ' . $e->getMessage();
                error_log("Error en guardarInventarioAPI: " . $e->getMessage());
            }
        }

        echo json_encode([
            'resultado' => false,
            'mensaje' => 'Errores de validación',
            'errores' => $errores
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function solicitudes(Router $router)
    {
        AuthController::verificarLogin();
        $router->render('solicitudes/index', []);
    }

    public static function obtenerSolicitudesAPI()
    {
        if (ob_get_level()) {
            ob_clean();
        }

        header('Content-Type: application/json; charset=utf-8');
        AuthController::verificarLogin();

        try {
            $solicitudes = self::obtenerSolicitudesConDetalles();
            echo json_encode($solicitudes, JSON_UNESCAPED_UNICODE);
            exit;
        } catch (Exception $e) {
            echo json_encode([
                'resultado' => false,
                'mensaje' => 'Error al obtener solicitudes: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    public static function obtenerSolicitudesPendientesAPI()
    {
        if (ob_get_level()) {
            ob_clean();
        }

        header('Content-Type: application/json; charset=utf-8');
        AuthController::verificarLogin();

        try {
            $query = "
                SELECT 
                    s.solicitud_id,
                    p.personal_nombre,
                    td.tipo_nombre,
                    t.talla_etiqueta,
                    s.cantidad,
                    s.fecha_solicitud
                FROM morataya_solicitudes_dotacion s
                INNER JOIN morataya_personal p ON s.personal_id = p.personal_id
                INNER JOIN morataya_tipos_dotacion td ON s.tipo_id = td.tipo_id
                INNER JOIN morataya_tallas t ON s.talla_id = t.talla_id
                WHERE s.estado_entrega = 0 AND s.solicitud_situacion = 1
                ORDER BY s.fecha_solicitud ASC
            ";

            $pendientes = SolicitudDotacion::fetchArray($query);
            echo json_encode($pendientes, JSON_UNESCAPED_UNICODE);
            exit;
        } catch (Exception $e) {
            echo json_encode([
                'resultado' => false,
                'mensaje' => 'Error al obtener solicitudes pendientes: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    public static function guardarSolicitudAPI()
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

        $personal_id = isset($_POST['personal_id']) ? (int)$_POST['personal_id'] : 0;
        $tipo_id = isset($_POST['tipo_id']) ? (int)$_POST['tipo_id'] : 0;
        $talla_id = isset($_POST['talla_id']) ? (int)$_POST['talla_id'] : 0;
        $cantidad = isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : 1;

        if (!$personal_id) $errores[] = 'Debe seleccionar el personal';
        if (!$tipo_id) $errores[] = 'Debe seleccionar un tipo de dotación';
        if (!$talla_id) $errores[] = 'Debe seleccionar una talla';
        if ($cantidad < 1 || $cantidad > 50) $errores[] = 'La cantidad debe ser entre 1 y 50';

        // Verificar inventario disponible
        if ($tipo_id && $talla_id && $cantidad) {
            $inventario = self::buscarInventarioExistentePorSQL($tipo_id, $talla_id);
            if (!$inventario || $inventario['cantidad'] < $cantidad) {
                $disponible = $inventario ? $inventario['cantidad'] : 0;
                $errores[] = "Stock insuficiente. Disponible: {$disponible}, Solicitado: {$cantidad}";
            }
        }

        if ($personal_id) {
            $año_actual = date('Y');
            $entregas_año = self::contarEntregasAnualesPorSQL($personal_id, $año_actual);

            if ($entregas_año >= 3) {
                $errores[] = 'El personal ya recibió el máximo de 3 dotaciones este año';
            }
        }

        if ($personal_id && $tipo_id) {
            $solicitudExistente = self::verificarSolicitudPendientePorSQL($personal_id, $tipo_id);
            if ($solicitudExistente) {
                $errores[] = 'Ya existe una solicitud pendiente de este tipo para el personal';
            }
        }

        if (empty($errores)) {
            try {
                $solicitud = new SolicitudDotacion([
                    'personal_id' => $personal_id,
                    'tipo_id' => $tipo_id,
                    'talla_id' => $talla_id,
                    'cantidad' => $cantidad
                ]);

                $resultado = $solicitud->guardar();

                if ($resultado['resultado'] ?? false) {
                    try {
                        $auditoria = new Auditoria([
                            'usu_id' => $_SESSION['usuario_id'],
                            'aud_modulo' => 'Solicitudes',
                            'aud_accion' => "Solicitud creada: {$cantidad} unidades para personal ID: {$personal_id}",
                            'aud_ip' => $_SERVER['REMOTE_ADDR'],
                            'aud_navegador' => substr($_SERVER['HTTP_USER_AGENT'], 0, 100)
                        ]);
                        $auditoria->guardar();
                    } catch (Exception $e) {
                        error_log("Error en auditoría de solicitud: " . $e->getMessage());
                    }

                    echo json_encode([
                        'resultado' => true,
                        'mensaje' => 'Solicitud creada correctamente'
                    ], JSON_UNESCAPED_UNICODE);
                    exit;
                } else {
                    throw new Exception('Error al guardar solicitud en la base de datos');
                }
            } catch (Exception $e) {
                $errores[] = 'Error al guardar: ' . $e->getMessage();
            }
        }

        echo json_encode([
            'resultado' => false,
            'mensaje' => 'Errores de validación',
            'errores' => $errores
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function entregas(Router $router)
    {
        AuthController::verificarLogin();
        $router->render('entregas/index', []);
    }

    public static function obtenerEntregasAPI()
    {
        if (ob_get_level()) {
            ob_clean();
        }

        header('Content-Type: application/json; charset=utf-8');
        AuthController::verificarLogin();

        try {
            $entregas = self::obtenerEntregasConDetalles();
            echo json_encode($entregas, JSON_UNESCAPED_UNICODE);
            exit;
        } catch (Exception $e) {
            echo json_encode([
                'resultado' => false,
                'mensaje' => 'Error al obtener entregas: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    public static function procesarEntregaAPI()
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

        $solicitud_id = isset($_POST['solicitud_id']) ? (int)$_POST['solicitud_id'] : 0;

        if (!$solicitud_id) $errores[] = 'Debe seleccionar una solicitud';

        $solicitud = self::buscarSolicitudPorSQL($solicitud_id);
        if (!$solicitud || $solicitud['estado_entrega'] == 1) {
            $errores[] = 'Solicitud no válida o ya entregada';
        }

        $inventario = null;
        if ($solicitud) {
            $inventario = self::buscarInventarioExistentePorSQL($solicitud['tipo_id'], $solicitud['talla_id']);
            $cantidadSolicitada = $solicitud['cantidad'] ?? 1;

            if (!$inventario || $inventario['cantidad'] < $cantidadSolicitada) {
                $disponible = $inventario ? $inventario['cantidad'] : 0;
                $errores[] = "Stock insuficiente. Disponible: {$disponible}, Solicitado: {$cantidadSolicitada}";
            }
        }

        if (empty($errores)) {
            try {
                $entrega = new EntregaDotacion([
                    'solicitud_id' => $solicitud_id,
                    'usuario_id' => $_SESSION['usuario_id']
                ]);

                $resultado = $entrega->guardar();

                if ($resultado['resultado'] ?? false) {
                    // Actualizar estado de solicitud
                    $sqlUpdateSolicitud = "UPDATE morataya_solicitudes_dotacion SET estado_entrega = 1 WHERE solicitud_id = {$solicitud_id}";
                    SolicitudDotacion::SQL($sqlUpdateSolicitud);

                    // Descontar del inventario la cantidad solicitada
                    $cantidadSolicitada = $solicitud['cantidad'] ?? 1;
                    $nuevaCantidad = $inventario['cantidad'] - $cantidadSolicitada;
                    $sqlUpdateInventario = "UPDATE morataya_inventario_dotacion SET cantidad = {$nuevaCantidad} WHERE inv_id = {$inventario['inv_id']}";
                    InventarioDotacion::SQL($sqlUpdateInventario);

                    try {
                        $auditoria = new Auditoria([
                            'usu_id' => $_SESSION['usuario_id'],
                            'aud_modulo' => 'Entregas',
                            'aud_accion' => "Entrega realizada solicitud ID: {$solicitud_id} - cantidad: {$cantidadSolicitada}",
                            'aud_ip' => $_SERVER['REMOTE_ADDR'],
                            'aud_navegador' => substr($_SERVER['HTTP_USER_AGENT'], 0, 100)
                        ]);
                        $auditoria->guardar();
                    } catch (Exception $e) {
                        error_log("Error en auditoría de entrega: " . $e->getMessage());
                    }

                    echo json_encode([
                        'resultado' => true,
                        'mensaje' => 'Entrega procesada correctamente'
                    ], JSON_UNESCAPED_UNICODE);
                    exit;
                } else {
                    throw new Exception('Error al registrar la entrega');
                }
            } catch (Exception $e) {
                $errores[] = 'Error al procesar: ' . $e->getMessage();
            }
        }

        echo json_encode([
            'resultado' => false,
            'mensaje' => 'Errores de validación',
            'errores' => $errores
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    private static function obtenerInventarioConDetalles()
    {
        $query = "
            SELECT 
                i.inv_id,
                i.tipo_id,
                i.talla_id,
                i.cantidad,
                i.fecha_ingreso,
                td.tipo_nombre,
                t.talla_etiqueta
            FROM morataya_inventario_dotacion i
            INNER JOIN morataya_tipos_dotacion td ON i.tipo_id = td.tipo_id
            INNER JOIN morataya_tallas t ON i.talla_id = t.talla_id
            WHERE i.inv_situacion = 1 AND td.tipo_situacion = 1 AND t.talla_situacion = 1
            ORDER BY td.tipo_nombre, t.talla_etiqueta
        ";

        return InventarioDotacion::fetchArray($query);
    }

    private static function obtenerSolicitudesConDetalles()
    {
        $query = "
            SELECT 
                s.solicitud_id,
                s.personal_id,
                s.tipo_id,
                s.talla_id,
                s.cantidad,
                s.fecha_solicitud,
                s.estado_entrega,
                p.personal_nombre,
                p.personal_cui,
                td.tipo_nombre,
                t.talla_etiqueta
            FROM morataya_solicitudes_dotacion s
            INNER JOIN morataya_personal p ON s.personal_id = p.personal_id
            INNER JOIN morataya_tipos_dotacion td ON s.tipo_id = td.tipo_id
            INNER JOIN morataya_tallas t ON s.talla_id = t.talla_id
            WHERE s.solicitud_situacion = 1
            ORDER BY s.fecha_solicitud DESC
        ";

        return SolicitudDotacion::fetchArray($query);
    }

    private static function obtenerEntregasConDetalles()
    {
        $query = "
            SELECT 
                e.entrega_id,
                e.fecha_entrega,
                p.personal_nombre,
                p.personal_cui,
                td.tipo_nombre,
                t.talla_etiqueta,
                s.cantidad,
                u.usu_nombre as usuario_entrega
            FROM morataya_entregas_dotacion e
            INNER JOIN morataya_solicitudes_dotacion s ON e.solicitud_id = s.solicitud_id
            INNER JOIN morataya_personal p ON s.personal_id = p.personal_id
            INNER JOIN morataya_tipos_dotacion td ON s.tipo_id = td.tipo_id
            INNER JOIN morataya_tallas t ON s.talla_id = t.talla_id
            INNER JOIN morataya_usuario u ON e.usuario_id = u.usu_id
            WHERE e.entrega_situacion = 1
            ORDER BY e.fecha_entrega DESC
        ";

        return EntregaDotacion::fetchArray($query);
    }

    private static function buscarInventarioExistentePorSQL($tipo_id, $talla_id)
    {
        $query = "
            SELECT * FROM morataya_inventario_dotacion 
            WHERE tipo_id = {$tipo_id} AND talla_id = {$talla_id} AND inv_situacion = 1
        ";

        return InventarioDotacion::fetchFirst($query);
    }

    private static function buscarSolicitudPorSQL($solicitud_id)
    {
        $query = "
            SELECT * FROM morataya_solicitudes_dotacion 
            WHERE solicitud_id = {$solicitud_id} AND solicitud_situacion = 1
        ";

        return SolicitudDotacion::fetchFirst($query);
    }

    private static function contarEntregasAnualesPorSQL($personal_id, $año)
    {
        $query = "
            SELECT COUNT(*) as total
            FROM morataya_entregas_dotacion e
            INNER JOIN morataya_solicitudes_dotacion s ON e.solicitud_id = s.solicitud_id
            WHERE s.personal_id = {$personal_id} 
            AND YEAR(e.fecha_entrega) = {$año}
            AND e.entrega_situacion = 1
        ";

        $resultado = EntregaDotacion::fetchFirst($query);
        return $resultado['total'] ?? 0;
    }

    private static function verificarSolicitudPendientePorSQL($personal_id, $tipo_id)
    {
        $query = "
            SELECT COUNT(*) as total FROM morataya_solicitudes_dotacion 
            WHERE personal_id = {$personal_id} 
            AND tipo_id = {$tipo_id} 
            AND estado_entrega = 0 
            AND solicitud_situacion = 1
        ";

        $resultado = SolicitudDotacion::fetchFirst($query);
        return ($resultado['total'] ?? 0) > 0;
    }

    public static function actualizarInventarioAPI()
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

        $inv_id = isset($_POST['inv_id']) ? filter_var($_POST['inv_id'], FILTER_VALIDATE_INT) : 0;
        $cantidad = isset($_POST['cantidad']) ? filter_var($_POST['cantidad'], FILTER_VALIDATE_INT) : 0;

        if (!$inv_id || $inv_id <= 0) {
            $errores[] = 'ID de inventario no válido';
        }

        if ($cantidad < 0 || $cantidad > 9999) {
            $errores[] = 'La cantidad debe ser entre 0 y 9999';
        }

        if (empty($errores)) {
            try {
                $sqlUpdate = "UPDATE morataya_inventario_dotacion SET cantidad = {$cantidad} WHERE inv_id = {$inv_id}";
                $resultado = InventarioDotacion::SQL($sqlUpdate);

                if ($resultado) {
                    try {
                        $auditoria = new Auditoria([
                            'usu_id' => $_SESSION['usuario_id'],
                            'aud_modulo' => 'Inventario',
                            'aud_accion' => "Actualización de inventario ID: {$inv_id} - nueva cantidad: {$cantidad}",
                            'aud_ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                            'aud_navegador' => substr($_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido', 0, 100)
                        ]);
                        $auditoria->guardar();
                    } catch (Exception $e) {
                        error_log("Error en auditoría: " . $e->getMessage());
                    }

                    echo json_encode([
                        'resultado' => true,
                        'mensaje' => 'Inventario actualizado correctamente'
                    ], JSON_UNESCAPED_UNICODE);
                    exit;
                } else {
                    $errores[] = 'Error al actualizar en la base de datos';
                }
            } catch (Exception $e) {
                $errores[] = 'Error al procesar: ' . $e->getMessage();
            }
        }

        echo json_encode([
            'resultado' => false,
            'mensaje' => 'Errores de validación',
            'errores' => $errores
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function actualizarSolicitudAPI()
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

        $solicitud_id = isset($_POST['solicitud_id']) ? filter_var($_POST['solicitud_id'], FILTER_VALIDATE_INT) : 0;
        $personal_id = isset($_POST['personal_id']) ? (int)$_POST['personal_id'] : 0;
        $tipo_id = isset($_POST['tipo_id']) ? (int)$_POST['tipo_id'] : 0;
        $talla_id = isset($_POST['talla_id']) ? (int)$_POST['talla_id'] : 0;
        $cantidad = isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : 1;

        if (!$solicitud_id || $solicitud_id <= 0) {
            $errores[] = 'ID de solicitud no válido';
        }

        if (!$personal_id) $errores[] = 'Debe seleccionar el personal';
        if (!$tipo_id) $errores[] = 'Debe seleccionar un tipo de dotación';
        if (!$talla_id) $errores[] = 'Debe seleccionar una talla';
        if ($cantidad < 1 || $cantidad > 50) $errores[] = 'La cantidad debe ser entre 1 y 50';

        $solicitudExistente = self::buscarSolicitudPorSQL($solicitud_id);
        if (!$solicitudExistente) {
            $errores[] = 'La solicitud no existe';
        } elseif ($solicitudExistente['estado_entrega'] == 1) {
            $errores[] = 'No se puede editar una solicitud ya entregada';
        }

        if ($tipo_id && $talla_id && $cantidad) {
            $inventario = self::buscarInventarioExistentePorSQL($tipo_id, $talla_id);
            if (!$inventario || $inventario['cantidad'] < $cantidad) {
                $disponible = $inventario ? $inventario['cantidad'] : 0;
                $errores[] = "Stock insuficiente. Disponible: {$disponible}, Solicitado: {$cantidad}";
            }
        }

        if (empty($errores)) {
            try {
                $sqlUpdate = "UPDATE morataya_solicitudes_dotacion SET 
                personal_id = {$personal_id},
                tipo_id = {$tipo_id},
                talla_id = {$talla_id},
                cantidad = {$cantidad}
                WHERE solicitud_id = {$solicitud_id}";

                $resultado = SolicitudDotacion::SQL($sqlUpdate);

                if ($resultado) {
                    try {
                        $auditoria = new Auditoria([
                            'usu_id' => $_SESSION['usuario_id'],
                            'aud_modulo' => 'Solicitudes',
                            'aud_accion' => "Actualización de solicitud ID: {$solicitud_id} - cantidad: {$cantidad}",
                            'aud_ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                            'aud_navegador' => substr($_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido', 0, 100)
                        ]);
                        $auditoria->guardar();
                    } catch (Exception $e) {
                        error_log("Error en auditoría: " . $e->getMessage());
                    }

                    echo json_encode([
                        'resultado' => true,
                        'mensaje' => 'Solicitud actualizada correctamente'
                    ], JSON_UNESCAPED_UNICODE);
                    exit;
                } else {
                    $errores[] = 'Error al actualizar en la base de datos';
                }
            } catch (Exception $e) {
                $errores[] = 'Error al procesar: ' . $e->getMessage();
            }
        }

        echo json_encode([
            'resultado' => false,
            'mensaje' => 'Errores de validación',
            'errores' => $errores
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function eliminarSolicitudAPI()
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

        $solicitud_id = isset($_POST['solicitud_id']) ? filter_var($_POST['solicitud_id'], FILTER_VALIDATE_INT) : 0;

        if (!$solicitud_id || $solicitud_id <= 0) {
            $errores[] = 'ID de solicitud no válido';
        }

        $solicitudExistente = self::buscarSolicitudPorSQL($solicitud_id);
        if (!$solicitudExistente) {
            $errores[] = 'La solicitud no existe';
        } elseif ($solicitudExistente['estado_entrega'] == 1) {
            $errores[] = 'No se puede eliminar una solicitud ya entregada';
        }

        if (empty($errores)) {
            try {
                $sqlUpdate = "UPDATE morataya_solicitudes_dotacion SET solicitud_situacion = 0 WHERE solicitud_id = {$solicitud_id}";
                $resultado = SolicitudDotacion::SQL($sqlUpdate);

                if ($resultado) {
                    try {
                        $auditoria = new Auditoria([
                            'usu_id' => $_SESSION['usuario_id'],
                            'aud_modulo' => 'Solicitudes',
                            'aud_accion' => "Eliminación de solicitud ID: {$solicitud_id}",
                            'aud_ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                            'aud_navegador' => substr($_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido', 0, 100)
                        ]);
                        $auditoria->guardar();
                    } catch (Exception $e) {
                        error_log("Error en auditoría: " . $e->getMessage());
                    }

                    echo json_encode([
                        'resultado' => true,
                        'mensaje' => 'Solicitud eliminada correctamente'
                    ], JSON_UNESCAPED_UNICODE);
                    exit;
                } else {
                    $errores[] = 'Error al eliminar en la base de datos';
                }
            } catch (Exception $e) {
                $errores[] = 'Error al procesar: ' . $e->getMessage();
            }
        }

        echo json_encode([
            'resultado' => false,
            'mensaje' => 'Errores de validación',
            'errores' => $errores
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function obtenerTallasDisponiblesAPI()
    {
        if (ob_get_level()) {
            ob_clean();
        }

        header('Content-Type: application/json; charset=utf-8');
        AuthController::verificarLogin();

        $tipo_id = isset($_GET['tipo_id']) ? (int)$_GET['tipo_id'] : 0;

        if (!$tipo_id) {
            echo json_encode([
                'resultado' => false,
                'mensaje' => 'Tipo de dotación requerido'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        try {
            $query = "
                SELECT 
                    t.talla_id,
                    t.talla_etiqueta,
                    i.cantidad as stock_disponible
                FROM morataya_tallas t
                INNER JOIN morataya_inventario_dotacion i ON t.talla_id = i.talla_id
                WHERE i.tipo_id = {$tipo_id} 
                AND i.cantidad > 0 
                AND i.inv_situacion = 1 
                AND t.talla_situacion = 1
                ORDER BY t.talla_etiqueta
            ";

            $tallasDisponibles = Talla::fetchArray($query);
            echo json_encode($tallasDisponibles, JSON_UNESCAPED_UNICODE);
            exit;
        } catch (Exception $e) {
            echo json_encode([
                'resultado' => false,
                'mensaje' => 'Error al obtener tallas disponibles: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
}
