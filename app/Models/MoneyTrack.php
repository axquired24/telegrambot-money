<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'id', 'amount', 'is_expense',
        'trx_date', 'description',
        'from_id', 'topic_id',
        'money_category_id',
        'created_at', 'updated_at', 'deleted_at',
    ];

    protected $casts = [
        'from_id' => 'integer',
        'topic_id' => 'integer',
        'is_expense' => 'integer',
        'money_category_id' => 'integer'
    ];

    public $timestamps = true;

    public function MoneyCategory(): BelongsTo
    {
        return $this->belongsTo(MoneyCategory::class);
    }

    public static function listByMonth($yearMonth=null, $categoryId=null) {
        if (empty($yearMonth)) {
            $yearMonth = date('Y-m');
        } // endif

        $fullDate = Carbon::createFromFormat('Y-m', $yearMonth);
        $query = self::whereMonth('trx_date', $fullDate->month)->whereYear('trx_date', $fullDate->year);

        if(! empty($categoryId)) {
            $query = $query->where('money_category_id', $categoryId);
        } // endif

        $query = $query->orderBy('trx_date', 'desc')
            ->orderBy('id', 'desc');

        return $query;
    }

    public static function summaryByMonth($yearMonth=null, $list=null) {
        if (empty($list)) {
            $list = self::listByMonth($yearMonth)->get();
        } // endif

        $expense = $list->filter(function($item) {
            return $item->is_expense;
        })->pluck('amount')->sum();
        $income = $list->filter(function($item) {
            return ! $item->is_expense;
        })->pluck('amount')->sum();
        $balance = $income - abs($expense);

        return compact('expense', 'income', 'balance');
    }
}
