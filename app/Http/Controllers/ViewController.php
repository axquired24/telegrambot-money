<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MoneyTrack;
use Illuminate\Support\Carbon;

class ViewController extends Controller
{
    private function rupiahFormat($angka, $minusValue=false)
    {

        $final = "Rp." . number_format(abs($angka), 0,',','.');
        if($minusValue && ($angka < 0)) {
            $final = "- " . $final;
        } // endif
        return $final;
    }

    public function index(Request $request)
    {
        $req_month = empty($request->bulan) ? date('Y-m') : $request->bulan;
        $fullDate = Carbon::createFromFormat('Y-m', $req_month);
        $list = MoneyTrack::whereMonth('trx_date', $fullDate->month)
            ->whereYear('trx_date', $fullDate->year)
            ->orderBy('id', 'desc')->get();
        $list = $list->map(function($item) {
            $item->amount_format = $this->rupiahFormat($item->amount, true);
            $item->trx_date_format = Carbon::parse($item->trx_date)->format('d F Y');
            return $item;
        });
        $expense = $list->filter(function($item) {
            return $item->is_expense;
        })->pluck('amount')->sum();
        $income = $list->filter(function($item) {
            return ! $item->is_expense;
        })->pluck('amount')->sum();
        $balance = $income - $expense;

        $balance = $this->rupiahFormat($balance, true);
        $income = $this->rupiahFormat($income, true);
        $expense = $this->rupiahFormat($expense, true);

        return view('dashboard', compact('list', 'expense', 'income', 'balance', 'req_month'));
    }
}
