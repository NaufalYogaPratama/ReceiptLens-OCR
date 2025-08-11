<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseItem extends Model
{
    protected $fillable = [
        'expenses_id',
        'name',
        'qty',
        'price',
        'subtotal',
    ];

    public function expense()
    {
        return $this->belongsTo(Expense::class, 'expenses_id');
    }
}
