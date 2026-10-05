<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use SoftDeletes;

    protected $fillable = ['code', 'name', 'pic', 'phone', 'email', 'address', 'status', 'note'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
