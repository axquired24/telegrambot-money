<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chatroom extends Model
{
    protected $table = 'chatrooms';
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'id', 'type', 'title'
    ];

    public function topics(): HasMany
    {
        return $this->hasMany(Topic::class);
    }

    public $timestamps = false;
}
