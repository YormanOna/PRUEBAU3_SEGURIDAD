<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\InvoiceItem[] $items
 * @property string $status
 */

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    // Estados de la factura
    const STATUS_PENDIENTE = 'pendiente';
    const STATUS_PAGADA = 'pagada';
    const STATUS_ANULADA = 'anulada';

    protected $fillable = [
        'invoice_number',
        'client_id',
        'user_id',
        'issue_date',
        'due_date',
        'subtotal',
        'tax',
        'total',
        'status',
        'payment_date',
        'paid_by',
        'cancelled_at',
        'cancelled_by',
        'cancellation_reason',
        'deletion_reason',
        'deleted_by',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'issue_date' => 'date',
        'due_date' => 'date',
        'payment_date' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function paidBy(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'paid_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class, 'factura_id');
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_PENDIENTE;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_ANULADA;
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAGADA;
    }

    public function canBePaid(): bool
    {
        return $this->status === self::STATUS_PENDIENTE && !$this->isPaid();
    }

    public function canBePaidByClient(Client $client): bool
    {
        return $this->canBePaid() && $this->client_id === $client->id;
    }

    public function markAsPaid(Client $client): bool
    {
        if (!$this->canBePaidByClient($client)) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_PAGADA,
            'payment_date' => now(),
            'paid_by' => $client->id,
        ]);

        return true;
    }

    public function canBeCancelledBy(User $user): bool
    {
        return $this->user_id === $user->id || $user->hasRole('Administrador');
    }

    public function canBeDeletedBy(User $user): bool
    {
        // Solo el creador de la factura o un administrador pueden eliminarla
        return $this->user_id === $user->id || $user->hasRole('Administrador');
    }

    public static function generateInvoiceNumber(): string
    {
        $lastNumber = self::withTrashed()
            ->selectRaw("MAX(CAST(SUBSTRING(invoice_number FROM 5) AS INTEGER)) as max_number")
            ->value('max_number');

        $next = $lastNumber ? $lastNumber + 1 : 1;

        return 'INV-' . str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }
}
