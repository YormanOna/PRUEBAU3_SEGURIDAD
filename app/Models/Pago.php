<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read \App\Models\Invoice $factura
 * @property-read \App\Models\Client $cliente
 * @property-read \App\Models\User $validador
 */
class Pago extends Model
{
    use HasFactory;

    protected $table = 'pagos';

    // Estados del pago
    const ESTADO_PENDIENTE = 'pendiente';
    const ESTADO_APROBADO = 'aprobado';
    const ESTADO_RECHAZADO = 'rechazado';

    // Tipos de pago
    const TIPO_EFECTIVO = 'efectivo';
    const TIPO_TARJETA = 'tarjeta';
    const TIPO_TRANSFERENCIA = 'transferencia';
    const TIPO_CHEQUE = 'cheque';

    protected $fillable = [
        'factura_id',
        'tipo_pago',
        'monto',
        'numero_transaccion',
        'observacion',
        'estado',
        'pagado_por',
        'validado_por',
        'validated_at',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'validated_at' => 'datetime',
    ];

    // Relaciones
    public function factura(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'factura_id');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'pagado_por');
    }

    public function validador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validado_por');
    }

    // Métodos de estado
    public function isPendiente(): bool
    {
        return $this->estado === self::ESTADO_PENDIENTE;
    }

    public function isAprobado(): bool
    {
        return $this->estado === self::ESTADO_APROBADO;
    }

    public function isRechazado(): bool
    {
        return $this->estado === self::ESTADO_RECHAZADO;
    }

    public function canBeValidated(): bool
    {
        return $this->isPendiente();
    }

    public function aprobar(User $validador, string $observacion = null): bool
    {
        if (!$this->canBeValidated()) {
            return false;
        }

        $this->update([
            'estado' => self::ESTADO_APROBADO,
            'validado_por' => $validador->id,
            'validated_at' => now(),
            'observacion' => $observacion ?? $this->observacion,
        ]);

        return true;
    }

    public function rechazar(User $validador, string $observacion): bool
    {
        if (!$this->canBeValidated()) {
            return false;
        }

        $this->update([
            'estado' => self::ESTADO_RECHAZADO,
            'validado_por' => $validador->id,
            'validated_at' => now(),
            'observacion' => $observacion,
        ]);

        return true;
    }

    // Scopes
    public function scopePendientes($query)
    {
        return $query->where('estado', self::ESTADO_PENDIENTE);
    }

    public function scopeAprobados($query)
    {
        return $query->where('estado', self::ESTADO_APROBADO);
    }

    public function scopeRechazados($query)
    {
        return $query->where('estado', self::ESTADO_RECHAZADO);
    }

    // Métodos estáticos de utilidad
    public static function getTiposPago(): array
    {
        return [
            self::TIPO_EFECTIVO => 'Efectivo',
            self::TIPO_TARJETA => 'Tarjeta',
            self::TIPO_TRANSFERENCIA => 'Transferencia',
            self::TIPO_CHEQUE => 'Cheque',
        ];
    }

    public static function getEstados(): array
    {
        return [
            self::ESTADO_PENDIENTE => 'Pendiente',
            self::ESTADO_APROBADO => 'Aprobado',
            self::ESTADO_RECHAZADO => 'Rechazado',
        ];
    }
}
