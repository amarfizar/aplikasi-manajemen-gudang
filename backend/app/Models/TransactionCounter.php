<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionCounter extends Model
{
    protected $fillable = ['type', 'date', 'last_number'];
}
