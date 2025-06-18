<?php

namespace Model;

class EntregaDotacion extends ActiveRecord {
    
    public static $tabla = 'morataya_entregas_dotacion';
    public static $columnasDB = [
        'solicitud_id',
        'usuario_id',
        'fecha_entrega',
        'entrega_situacion'
    ];
    
    public static $idTabla = 'entrega_id';
    public $entrega_id;
    public $solicitud_id;
    public $usuario_id;
    public $fecha_entrega;
    public $entrega_situacion;
    
    public function __construct($args = []) {
        $this->entrega_id = $args['entrega_id'] ?? null;
        $this->solicitud_id = $args['solicitud_id'] ?? null;
        $this->usuario_id = $args['usuario_id'] ?? null;
        $this->fecha_entrega = $args['fecha_entrega'] ?? date('Y-m-d');
        $this->entrega_situacion = $args['entrega_situacion'] ?? 1;
    }
}