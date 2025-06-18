<?php

namespace Model;

class SolicitudDotacion extends ActiveRecord {
    
    public static $tabla = 'morataya_solicitudes_dotacion';
    public static $columnasDB = [
        'solicitud_id',
        'personal_id',
        'tipo_id',
        'talla_id',
        'fecha_solicitud',
        'estado_entrega',
        'solicitud_situacion'
    ];
    
    public static $idTabla = 'solicitud_id';
    
    // TODAS las propiedades públicas - ESTO ES CRÍTICO
    public $solicitud_id;
    public $personal_id;
    public $tipo_id;
    public $talla_id;
    public $fecha_solicitud;
    public $estado_entrega;
    public $solicitud_situacion;
    
    public function __construct($args = []) {
        $this->solicitud_id = $args['solicitud_id'] ?? null;
        $this->personal_id = $args['personal_id'] ?? null;
        $this->tipo_id = $args['tipo_id'] ?? null;
        $this->talla_id = $args['talla_id'] ?? null;
        $this->fecha_solicitud = $args['fecha_solicitud'] ?? date('Y-m-d');
        $this->estado_entrega = $args['estado_entrega'] ?? 0;
        $this->solicitud_situacion = $args['solicitud_situacion'] ?? 1;
    }
    
    // Método para buscar solicitudes con detalles
    public static function obtenerConDetalles() {
        $query = "
            SELECT 
                s.solicitud_id,
                s.personal_id,
                s.tipo_id,
                s.talla_id,
                s.fecha_solicitud,
                s.estado_entrega,
                s.solicitud_situacion,
                p.personal_nombre,
                p.personal_cui,
                td.tipo_nombre,
                t.talla_etiqueta
            FROM morataya_solicitudes_dotacion s
            INNER JOIN morataya_personal p ON s.personal_id = p.personal_id
            INNER JOIN morataya_tipos_dotacion td ON s.tipo_id = td.tipo_id
            INNER JOIN morataya_tallas t ON s.talla_id = t.talla_id
            WHERE s.solicitud_situacion = 1
            ORDER BY s.fecha_solicitud DESC
        ";
        
        return self::fetchArray($query);
    }
}