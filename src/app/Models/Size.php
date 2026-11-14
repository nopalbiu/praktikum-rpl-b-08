<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Size extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_size';

    protected $fillable = [
        'nama_size',
    ];

    public const ALL_SIZE_NAME = 'ALL Size';

    /** id_size untuk produk tanpa pilihan ukuran ("ALL Size"). */
    public static function allSizeId(): int
    {
        return static::where('nama_size', self::ALL_SIZE_NAME)->value('id_size')
            ?? static::create(['nama_size' => self::ALL_SIZE_NAME])->id_size;
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class, 'id_size', 'id_size');
    }
}
