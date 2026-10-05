<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WarehouseLocation extends Model
{
    use SoftDeletes;

    protected $fillable = ['warehouse_id', 'code', 'name', 'description', 'status'];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'warehouse_location_id');
    }
}
