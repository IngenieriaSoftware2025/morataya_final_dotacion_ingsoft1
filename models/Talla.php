<?php

namespace Model;

class Talla extends ActiveRecord {
    
    public static $tabla = 'morataya_tallas';
    public static $columnasDB = [
        'talla_id',
        'talla_etiqueta',
        'talla_situacion'
    ];
    
    public static $idTabla = 'talla_id';
    
    public $talla_id;
    public $talla_etiqueta;
    public $talla_situacion;
    
    public function __construct($args = []) {
        $this->talla_id = $args['talla_id'] ?? null;
        $this->talla_etiqueta = $args['talla_etiqueta'] ?? '';
        $this->talla_situacion = $args['talla_situacion'] ?? 1;
    }
}