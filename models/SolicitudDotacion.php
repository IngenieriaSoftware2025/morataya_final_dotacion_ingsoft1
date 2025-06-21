<?php

namespace Model;

class SolicitudDotacion extends ActiveRecord {
    
    public static $tabla = 'morataya_solicitudes_dotacion';
    public static $columnasDB = [
        'solicitud_id',
        'personal_id',
        'tipo_id',
        'talla_id',
        'cantidad',
        'fecha_solicitud',
        'estado_entrega',
        'solicitud_situacion'
    ];
    
    public static $idTabla = 'solicitud_id';
    
    public $solicitud_id;
    public $personal_id;
    public $tipo_id;
    public $talla_id;
    public $cantidad;
    public $fecha_solicitud;
    public $estado_entrega;
    public $solicitud_situacion;
    
    public function __construct($args = []) {
        $this->solicitud_id = $args['solicitud_id'] ?? null;
        $this->personal_id = $args['personal_id'] ?? null;
        $this->tipo_id = $args['tipo_id'] ?? null;
        $this->talla_id = $args['talla_id'] ?? null;
        $this->cantidad = $args['cantidad'] ?? 1;
        $this->fecha_solicitud = $args['fecha_solicitud'] ?? date('Y-m-d');
        $this->estado_entrega = $args['estado_entrega'] ?? 0;
        $this->solicitud_situacion = $args['solicitud_situacion'] ?? 1;
    }
}