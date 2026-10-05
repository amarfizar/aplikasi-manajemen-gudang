<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use SoftDeletes;

    protected $fillable = ['code', 'name', 'address', 'pic', 'phone', 'status'];

    public function locations()
    {
        return $this->hasMany(WarehouseLocation::class);
    }
}
