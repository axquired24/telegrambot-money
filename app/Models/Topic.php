<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Topic extends Model
{
    protected $table = 'topics';
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'id', 'chatroom_id', 'topic_id', 'name'
    ];

    public $timestamps = false;

    public function chatroom(): BelongsTo
    {
        return $this->belongsTo(Chatroom::class);
    }
}
