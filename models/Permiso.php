<?php


namespace Model;

class Permiso extends ActiveRecord {
    
    public static $tabla = 'morataya_permiso';
    public static $columnasDB = [
        'permiso_usuario',
        'permiso_rol',
        'permiso_situacion'
    ];
    
    public static $idTabla = 'permiso_id';
    public $permiso_id;
    public $permiso_usuario;
    public $permiso_rol;
    public $permiso_situacion;
    
    public function __construct($args = []) {
        $this->permiso_id = $args['permiso_id'] ?? null;
        $this->permiso_usuario = $args['permiso_usuario'] ?? null;
        $this->permiso_rol = $args['permiso_rol'] ?? null;
        $this->permiso_situacion = $args['permiso_situacion'] ?? 1;
    }
}