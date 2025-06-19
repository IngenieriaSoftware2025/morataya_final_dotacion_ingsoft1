<?php

namespace Model;

use Model\ActiveRecord;

class Auditoria extends ActiveRecord {
    
    public static $tabla = 'morataya_auditoria';
    public static $idTabla = 'aud_id';
    public static $columnasDB = 
    [
        'usu_id',
        'aud_usuario_nombre',
        'aud_modulo',
        'aud_accion',
        'aud_descripcion',
        'aud_ruta',
        'aud_ip',
        'aud_navegador',
        'aud_situacion'
    ];
    
    public $aud_id;
    public $usu_id;
    public $aud_usuario_nombre;
    public $aud_modulo;
    public $aud_accion;
    public $aud_descripcion;
    public $aud_ruta;
    public $aud_ip;
    public $aud_navegador;
    public $aud_fecha;
    public $aud_fecha_creacion;
    public $aud_situacion;
    
    public function __construct($args = [])
    {
        $this->aud_id = $args['aud_id'] ?? null;
        $this->usu_id = $args['usu_id'] ?? '';
        $this->aud_usuario_nombre = $args['aud_usuario_nombre'] ?? '';
        $this->aud_modulo = $args['aud_modulo'] ?? '';
        $this->aud_accion = $args['aud_accion'] ?? '';
        $this->aud_descripcion = $args['aud_descripcion'] ?? '';
        $this->aud_ruta = $args['aud_ruta'] ?? '';
        $this->aud_ip = $args['aud_ip'] ?? '';
        $this->aud_navegador = $args['aud_navegador'] ?? '';
        $this->aud_fecha = $args['aud_fecha'] ?? '';
        $this->aud_fecha_creacion = $args['aud_fecha_creacion'] ?? '';
        $this->aud_situacion = $args['aud_situacion'] ?? 1;
    }
}