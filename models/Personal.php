<?php

namespace Model;

class Personal extends ActiveRecord {
    
    public static $tabla = 'morataya_personal';
    public static $columnasDB = [
        'personal_nombre',
        'personal_cui',
        'personal_puesto',
        'personal_fecha_ingreso',
        'personal_situacion'
    ];
    
    public static $idTabla = 'personal_id';
    public $personal_id;
    public $personal_nombre;
    public $personal_cui;
    public $personal_puesto;
    public $personal_fecha_ingreso;
    public $personal_situacion;
    
    public function __construct($args = []) {
        $this->personal_id = $args['personal_id'] ?? null;
        $this->personal_nombre = $args['personal_nombre'] ?? '';
        $this->personal_cui = $args['personal_cui'] ?? '';
        $this->personal_puesto = $args['personal_puesto'] ?? '';
        $this->personal_fecha_ingreso = $args['personal_fecha_ingreso'] ?? date('Y-m-d');
        $this->personal_situacion = $args['personal_situacion'] ?? 1;
    }
}