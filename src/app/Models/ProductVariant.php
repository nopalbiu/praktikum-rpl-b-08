<?php

namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
 
class ProductVariant extends Model
{
    use HasFactory;
 
    protected $fillable = ['product_id', 'size', 'stock', 'price_modifier'];
 
    protected $casts = [
        'stock'          => 'integer',
        'price_modifier' => 'decimal:2',
    ];
 
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
 
    public function isInStock(): bool
    {
        return $this->stock > 0;
    }
 
    public function getFinalPrice(): float
    {
        return (float) ($this->product->price + $this->price_modifier);
    }
}