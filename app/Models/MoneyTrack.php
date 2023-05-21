<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MoneyTrack extends Model
{
    use SoftDeletes;
    protected $table = 'money_tracks';
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'id', 'amount', 'is_expense', 'created_at', 'updated_at', 'deleted_at', 'trx_date', 'description'
    ];

    public $timestamps = true;
}
