<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class From extends Model
{
    protected $table = 'froms';
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'id', 'username', 'first_name', 'last_name'
    ];

    public $timestamps = false;
}
