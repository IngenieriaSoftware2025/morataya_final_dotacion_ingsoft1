<?php

function debuguear($variable) {
    echo "<pre>";
    var_dump($variable);
    echo "</pre>";
    exit;
}

function s($html) {
    $s = htmlspecialchars($html);
    return $s;
}

function isAuth() {
    session_start();
    if(!isset($_SESSION['login'])) {
        header('Location: /');
    }
}

function isAuthApi() {
    getHeadersApi();
    session_start();
    if(!isset($_SESSION['auth_user'])) {
        echo json_encode([    
            "mensaje" => "No esta autenticado",
            "codigo" => 4,
        ]);
        exit;
    }
}

function isNotAuth(){
    session_start();
    if(isset($_SESSION['auth'])) {
        header('Location: /auth/');
    }
}

function hasPermission(array $permisos){
    $comprobaciones = [];
    foreach ($permisos as $permiso) {
        $comprobaciones[] = !isset($_SESSION[$permiso]) ? false : true;
    }

    if(array_search(true, $comprobaciones) !== false){}else{
        header('Location: /');
    }
}

function hasPermissionApi(array $permisos){
    getHeadersApi();
    $comprobaciones = [];
    foreach ($permisos as $permiso) {
        $comprobaciones[] = !isset($_SESSION[$permiso]) ? false : true;
    }

    if(array_search(true, $comprobaciones) !== false){}else{
        echo json_encode([     
            "mensaje" => "No tiene permisos",
            "codigo" => 4,
        ]);
        exit;
    }
}

function getHeadersApi(){
    return header("Content-type:application/json; charset=utf-8");
}

function asset($ruta){
    return "/". $_ENV['APP_NAME']."/public/" . $ruta;
}

function validarTexto($texto, $minimo = 1, $maximo = 255) {
    if (!is_string($texto)) {
        return false;
    }
    
    $texto = trim($texto);
    $longitud = strlen($texto);
    return $longitud >= $minimo && $longitud <= $maximo;
}

function validarNumero($numero, $minimo = null, $maximo = null) {
    if (!is_numeric($numero)) {
        return false;
    }
    
    $numero = (float)$numero;
    
    if ($minimo !== null && $numero < $minimo) {
        return false;
    }
    
    if ($maximo !== null && $numero > $maximo) {
        return false;
    }
    
    return true;
}

function validarCorreo($correo) {
    return filter_var($correo, FILTER_VALIDATE_EMAIL) !== false;
}

function subirFotografia($archivo, $destino) {
    try {
        // Verificar que no hay errores
        if (!isset($archivo['error']) || $archivo['error'] !== UPLOAD_ERR_OK) {
            return false;
        }
        
        // Verificar que el archivo fue subido
        if (!isset($archivo['tmp_name']) || !is_uploaded_file($archivo['tmp_name'])) {
            return false;
        }
        
        // Verificar tamaño (máximo 5MB)
        if ($archivo['size'] > 5 * 1024 * 1024) {
            return false;
        }
        
        // Verificar tipo de archivo
        $tiposPermitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $archivo['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $tiposPermitidos)) {
            return false;
        }
        
        // Crear directorio si no existe
        if (!file_exists($destino)) {
            if (!mkdir($destino, 0755, true)) {
                return false;
            }
        }
        
        // Generar nombre único
        $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
        $nombreArchivo = uniqid('foto_', true) . '.' . strtolower($extension);
        $rutaCompleta = $destino . $nombreArchivo;
        
        // Mover archivo
        if (move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
            return $nombreArchivo;
        }
        
        return false;
    } catch (Exception $e) {
        error_log("Error al subir fotografía: " . $e->getMessage());
        return false;
    }
}

function validarFecha($fecha) {
    $d = DateTime::createFromFormat('Y-m-d', $fecha);
    return $d && $d->format('Y-m-d') === $fecha;
}

function validarCUI($cui) {
    // Debe ser exactamente 13 dígitos
    if (!preg_match('/^\d{13}$/', $cui)) {
        return false;
    }
    
    // Algoritmo básico de validación de CUI
    $suma = 0;
    for ($i = 0; $i < 12; $i++) {
        $suma += intval($cui[$i]) * (13 - $i);
    }
    
    $digitoVerificador = $suma % 11;
    $ultimoDigito = intval($cui[12]);
    
    return ($digitoVerificador < 2 && $ultimoDigito == $digitoVerificador) || 
           ($digitoVerificador >= 2 && $ultimoDigito == (11 - $digitoVerificador));
}


function generarPasswordHash($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}


function verificarPassword($password, $hash) {
    return password_verify($password, $hash);
}