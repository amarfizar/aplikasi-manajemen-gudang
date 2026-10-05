<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockTransactionItem extends Model
{
    protected $fillable = [
        'stock_transaction_id', 'product_id', 'quantity', 'unit_id',
        'purchase_price', 'subtotal', 'stock_before', 'stock_after', 'note',
    ];

    public function transaction()
    {
        return $this->belongsTo(StockTransaction::class, 'stock_transaction_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
