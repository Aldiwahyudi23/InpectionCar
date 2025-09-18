<?php

namespace App\Models\Finance;

use App\Models\Customer;
use App\Models\DataInspection\Inspection;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
   use HasFactory, SoftDeletes;

    protected $fillable = [
        'inspection_id',
        'amount',
        'status',
        'payment_method',
        'paid_by' 
,        'payment_date',
        'due_date',
        'transaction_reference',
        'invoice_number',
        'notes',
        'payment_proof',
        'metadata'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'datetime',
        'due_date' => 'datetime',
        'metadata' => 'array',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';
    const STATUS_FAILED = 'failed';
    const STATUS_REFUNDED = 'refunded';
    const STATUS_EXPIRED = 'expired';

    // Payment method constants
    const METHOD_TRANSFER = 'transfer';
    const METHOD_CASH = 'cash';
    const METHOD_CREDIT_CARD = 'credit_card';
    const METHOD_DEBIT_CARD = 'debit_card';
    const METHOD_QRIS = 'qris';

    /**
     * Get the inspection that owns the transaction.
     */
    public function inspection()
    {
        return $this->belongsTo(Inspection::class);
    }

    /**
     * Get the customer through inspection.
     */
    public function customer()
    {
        return $this->hasOneThrough(Customer::class, Inspection::class, 'id', 'id', 'inspection_id', 'customer_id');
    }

     // Relasi ke user yang membayar
    public function payer()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

 public function distributions()
    {
        return $this->hasMany(TransactionDistribution::class);
    }

    /**
     * Scope a query to only include pending transactions.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope a query to only include paid transactions.
     */
    public function scopePaid($query)
    {
        return $query->where('status', self::STATUS_PAID);
    }

    /**
     * Scope a query to only include failed transactions.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Check if transaction is paid.
     */
    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    /**
     * Check if transaction is pending.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if transaction is expired.
     */
    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED;
    }

    /**
     * Generate invoice number automatically.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            if (empty($transaction->invoice_number)) {
                $transaction->invoice_number = static::generateInvoiceNumber();
            }
        });
    }

    /**
     * Generate unique invoice number.
     */
    public static function generateInvoiceNumber(): string
    {
        $prefix = 'INV';
        $date = now()->format('Ymd');
        $lastInvoice = static::where('invoice_number', 'like', "{$prefix}-{$date}-%")
            ->orderBy('invoice_number', 'desc')
            ->first();

        $sequence = 1;
        if ($lastInvoice) {
            $lastSequence = (int) substr($lastInvoice->invoice_number, -4);
            $sequence = $lastSequence + 1;
        }

        return "{$prefix}-{$date}-" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get status options for forms.
     */
    public static function getStatusOptions(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_PAID => 'Paid',
            self::STATUS_FAILED => 'Failed',
            self::STATUS_REFUNDED => 'Refunded',
            self::STATUS_EXPIRED => 'Expired',
        ];
    }

    /**
     * Get payment method options for forms.
     */
    public static function getPaymentMethodOptions(): array
    {
        return [
            self::METHOD_TRANSFER => 'Transfer',
            self::METHOD_CASH => 'Cash',
            self::METHOD_CREDIT_CARD => 'Credit Card',
            self::METHOD_DEBIT_CARD => 'Debit Card',
            self::METHOD_QRIS => 'QRIS',
        ];
    }


}

