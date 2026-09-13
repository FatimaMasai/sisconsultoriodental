<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Un campo dentro de una sección de la plantilla. `type` es uno de las
 * TYPES de abajo; `options` guarda, según el tipo, las alternativas de
 * selección o las columnas de una tabla.
 */
class FormField extends Model
{
    public const TYPE_TEXTO = 'texto';
    public const TYPE_NUMERO = 'numero';
    public const TYPE_FECHA = 'fecha';
    public const TYPE_SELECCION_UNICA = 'seleccion_unica';
    public const TYPE_SELECCION_MULTIPLE = 'seleccion_multiple';
    public const TYPE_SI_NO = 'si_no';
    public const TYPE_TABLA_FILA_UNICA = 'tabla_fila_unica';
    public const TYPE_TABLA_REPETIBLE = 'tabla_repetible';

    /**
     * Tipos válidos y su etiqueta legible, en el orden en que se muestran
     * al armar una plantilla.
     */
    public const TYPES = [
        self::TYPE_TEXTO => 'Texto',
        self::TYPE_NUMERO => 'Número',
        self::TYPE_FECHA => 'Fecha',
        self::TYPE_SELECCION_UNICA => 'Selección única',
        self::TYPE_SELECCION_MULTIPLE => 'Selección múltiple',
        self::TYPE_SI_NO => 'Sí / No',
        self::TYPE_TABLA_FILA_UNICA => 'Tabla (una opción por fila)',
        self::TYPE_TABLA_REPETIBLE => 'Tabla repetible',
    ];

    /**
     * Tipos que necesitan una lista de opciones para funcionar (selección
     * única/múltiple, y las columnas de las tablas).
     */
    public const TYPES_CON_OPCIONES = [
        self::TYPE_SELECCION_UNICA,
        self::TYPE_SELECCION_MULTIPLE,
        self::TYPE_TABLA_FILA_UNICA,
        self::TYPE_TABLA_REPETIBLE,
    ];

    protected $fillable = [
        'form_section_id',
        'label',
        'type',
        'options',
        'required',
        'help_text',
        'order',
    ];

    protected $casts = [
        'options' => 'array',
        'required' => 'boolean',
    ];

    public function section()
    {
        return $this->belongsTo(FormSection::class, 'form_section_id');
    }
}
