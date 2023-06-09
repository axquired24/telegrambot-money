<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MoneyTrack;
use App\Models\From;
use App\Models\Chatroom;
use App\Models\TelegramUpdate;
use Illuminate\Support\Carbon;
use Telegram\Bot\Laravel\Facades\Telegram;

class ViewController extends Controller
{
    private function rupiahFormat($angka, $minusValue=false)
    {

        $final = "Rp" . number_format(abs($angka), 0,',','.');
        if($minusValue && ($angka < 0)) {
            $final = "-" . $final;
        } // endif
        return $final;
    }

    public function index(Request $request)
    {
        $req_month = empty($request->bulan) ? date('Y-m') : $request->bulan;
        $list = MoneyTrack::listByMonth($req_month);

        if ($request->from) {
            $list = $list->where('from_id', $request->from);
        } // endif
        if ($request->chatroom) {
            $list = $list->where('chatroom_id', $request->chatroom);
        } // endif
        $list = $list->get();

        $summary = MoneyTrack::summaryByMonth($req_month, $list);
        $balance = $this->rupiahFormat($summary['balance'], true);
        $income = $this->rupiahFormat($summary['income'], true);
        $expense = $this->rupiahFormat($summary['expense'], true);

        $failed_parsed = TelegramUpdate::unsolvedErrors()->count();
        $chatrooms = Chatroom::all();
        $froms = From::all();

        $from = From::all();
        $chatroom = Chatroom::all();

        $list = $list->each(function($item) use ($from, $chatroom) {
            $item->amount_format = $this->rupiahFormat($item->amount, true);
            $item->trx_date_format = Carbon::parse($item->trx_date)->format('d F Y');
            $item->from = $from->find($item->from_id);
            return $item;
        });
        $list_json = $list->toJson();

        return view('dashboard', compact(
            'list', 'list_json', 'expense', 'income',
            'balance', 'failed_parsed', 'req_month',
            'chatrooms', 'froms'
        ));
    }

    public function invalidChat()
    {
        $chats = TelegramUpdate::unsolvedErrors()
            ->get()
            ->toJson();

        return view('invalidchat', compact(
            'chats'
        ));
    }

    public function solveInvalidChat(Request $request)
    {
        $chat = TelegramUpdate::find($request->update_id);
        $chat->error_solved = 1;
        $chat->save();
        return redirect()->back();
    }
}
