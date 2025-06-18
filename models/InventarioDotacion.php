<?php

namespace Model;

class InventarioDotacion extends ActiveRecord {
    
    public static $tabla = 'morataya_inventario_dotacion';
    public static $columnasDB = [
        'tipo_id',
        'talla_id',
        'cantidad',
        'fecha_ingreso',
        'inv_situacion'
    ];
    
    public static $idTabla = 'inv_id';
    public $inv_id;
    public $tipo_id;
    public $talla_id;
    public $cantidad;
    public $fecha_ingreso;
    public $inv_situacion;
    
    public function __construct($args = []) {
        $this->inv_id = $args['inv_id'] ?? null;
        $this->tipo_id = $args['tipo_id'] ?? null;
        $this->talla_id = $args['talla_id'] ?? null;
        $this->cantidad = $args['cantidad'] ?? 0;
        $this->fecha_ingreso = $args['fecha_ingreso'] ?? date('Y-m-d');
        $this->inv_situacion = $args['inv_situacion'] ?? 1;
    }
}
