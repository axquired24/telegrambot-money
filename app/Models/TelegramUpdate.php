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
    protected $appends = ['human_parsed_at'];
    protected $casts = [
        'parsed_at' => 'datetime'
    ];
    protected $hidden = [
        'parsed_at'
    ];

    public $timestamps = false;

    public function getHumanParsedAtAttribute() {
        if(empty($this->parsed_at)) {
            return null;
        } // endif
        return $this->parsed_at->format('d F Y H:i:s');
    }

    public static function unsolvedErrors() {
        return self::where([
            ['has_error', 1],
            ['error_solved', 0]
        ]);
    }
}
