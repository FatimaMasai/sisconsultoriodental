<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'auditable_type',
        'auditable_id',
        'description',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function auditable()
    {
        return $this->morphTo();
    }

    /**
     * Registra una entrada de auditoría asociada al usuario logueado.
     * Uso típico dentro de un controlador:
     *
     *   AuditLog::record('sale.cancelled', $sale, "Anuló la venta {$sale->numero}");
     */
    public static function record(string $action, Model $auditable, string $description, array $metadata = []): self
    {
        return static::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'auditable_type' => get_class($auditable),
            'auditable_id' => $auditable->getKey(),
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }
}
