<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'title',
        'amount',
        'receipt_image',
        'note',
        'parsed_data',
        'date_shopping',
        'change',
    ];

    public function items()
    {
        return $this->hasMany(ExpenseItem::class, 'expenses_id');
    }
}
