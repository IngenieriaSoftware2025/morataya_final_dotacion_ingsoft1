<?php

namespace Model;

class Usuario extends ActiveRecord {
    
    public static $tabla = 'morataya_usuario';
    public static $columnasDB = [
        'usu_nombre',
        'usu_codigo',
        'usu_password',
        'usu_correo',
        'usu_fotografia',
        'usu_situacion'
    ];
    
    public static $idTabla = 'usu_id';
    public $usu_id;
    public $usu_nombre;
    public $usu_codigo;
    public $usu_password;
    public $usu_correo;
    public $usu_fotografia;
    public $usu_situacion;
    
    public function __construct($args = []) {
        $this->usu_id = $args['usu_id'] ?? null;
        $this->usu_nombre = $args['usu_nombre'] ?? '';
        $this->usu_codigo = $args['usu_codigo'] ?? null;
        $this->usu_password = $args['usu_password'] ?? '';
        $this->usu_correo = $args['usu_correo'] ?? '';
        $this->usu_fotografia = $args['usu_fotografia'] ?? '';
        $this->usu_situacion = $args['usu_situacion'] ?? 1;
    }
}