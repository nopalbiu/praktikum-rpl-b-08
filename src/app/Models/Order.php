<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    public const STATUS_MENUNGGU_PEMBAYARAN = 'menunggu_pembayaran';
    public const STATUS_SUDAH_DIBAYAR       = 'sudah_dibayar';
    public const STATUS_DALAM_PERJALANAN    = 'dalam_perjalanan';
    public const STATUS_SELESAI             = 'selesai';
    public const STATUS_DIBATALKAN          = 'dibatalkan';

    /** Daftar status yang valid beserta label tampilannya. */
    public const STATUS_LABELS = [
        self::STATUS_MENUNGGU_PEMBAYARAN => 'Menunggu Pembayaran',
        self::STATUS_SUDAH_DIBAYAR       => 'Sudah Dibayar',
        self::STATUS_DALAM_PERJALANAN    => 'Dalam Perjalanan',
        self::STATUS_SELESAI             => 'Selesai',
        self::STATUS_DIBATALKAN          => 'Dibatalkan',
    ];

    protected $primaryKey = 'id_order';

    protected $fillable = [
        'id_user',
        'tanggal_order',
        'alamat_pengiriman',
        'kurir',
        'total_harga',
        'status_pesanan',
    ];

    protected $casts = [
        'tanggal_order' => 'datetime',
        'total_harga'   => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'id_order', 'id_order');
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class, 'id_order', 'id_order');
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status_pesanan] ?? ucwords(str_replace('_', ' ', $this->status_pesanan));
    }

    public function isPaid(): bool
    {
        return $this->payment?->status_pembayaran === 'lunas';
    }
}
