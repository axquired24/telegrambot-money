<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramUpdate extends Model
{
    protected $table = 'telegram_updates';
    protected $primaryKey = 'update_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'update_id', 'message', 'parsed_at', 'has_error', 'error_solved'
    ];

    public $timestamps = false;

    public static function unsolvedErrors() {
        return self::where([
            ['has_error', 1],
            ['error_solved', 0]
        ]);
    }
}
