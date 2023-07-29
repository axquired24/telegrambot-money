<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MoneyCategory extends Model
{
    use HasFactory;

    protected $table = 'money_categories';
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'id', 'name', 'is_expense', 'color'
    ];

    protected $casts = [
        'is_expense' => 'integer'
    ];

    public $timestamps = false;
}
