<?php

namespace Model;

class TipoDotacion extends ActiveRecord {
    
    public static $tabla = 'morataya_tipos_dotacion';
    public static $columnasDB = [
        'tipo_id',
        'tipo_nombre',
        'tipo_descripcion',
        'tipo_situacion'
    ];
    
    public static $idTabla = 'tipo_id';
    
    public $tipo_id;
    public $tipo_nombre;
    public $tipo_descripcion;
    public $tipo_situacion;
    
    public function __construct($args = []) {
        $this->tipo_id = $args['tipo_id'] ?? null;
        $this->tipo_nombre = $args['tipo_nombre'] ?? '';
        $this->tipo_descripcion = $args['tipo_descripcion'] ?? '';
        $this->tipo_situacion = $args['tipo_situacion'] ?? 1;
    }
}