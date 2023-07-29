<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MoneyTrack;
use App\Models\From;
use App\Models\Chatroom;
use App\Models\MoneyCategory;
use App\Models\TelegramUpdate;
use App\Models\Topic;
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

        $list = $list->each(function($item) use ($froms, $chatrooms) {
            $item->amount_format = $this->rupiahFormat($item->amount, true);
            $item->trx_date_format = Carbon::parse($item->trx_date)->format('d F Y');
            $item->from = $froms->find($item->from_id);
            return $item;
        });
        $list_json = $list->toJson();

        $viewData = compact(
            'list', 'list_json', 'expense', 'income',
            'balance', 'failed_parsed', 'req_month',
            'chatrooms', 'froms'
        );

        return view('dashboard', $viewData);
    }

    public function v2(Request $request, $slug=null)
    {
        return view('v2');
    }

    public function getMasterData(Request $request)
    {
        // $chatrooms = Chatroom::all();
        // $froms = From::all();
        // $topics = Topic::all();
        // $bulan = date('Y-m');
        // return compact('chatrooms', 'categories', 'froms', 'bulan');

        $categories = MoneyCategory::all();
        return compact('categories');
    }

    public function getMoneyData(Request $request)
    {
        $bulan = empty($request->bulan) ? date('Y-m') : $request->bulan;
        $list = MoneyTrack::listByMonth($bulan);

        if (! empty($request->fromID)) {
            $list = $list->where('from_id', $request->fromID);
        } // endif
        if (! empty($request->topicID)) {
            $list = $list->where('topic_id', $request->topicID);
        } // endif
        $list = $list->get();

        $summary = MoneyTrack::summaryByMonth($bulan, $list);
        $balance = $this->rupiahFormat($summary['balance'], true);
        $income = $this->rupiahFormat($summary['income'], true);
        $expense = $this->rupiahFormat($summary['expense'], true);

        $failedParsed = TelegramUpdate::unsolvedErrors()->count();
        $topics = Topic::all();
        $froms = From::all();
        $categories = MoneyCategory::all();

        $list = $list->each(function($item) use ($froms, $topics, $categories) {
            $item->amount_format = $this->rupiahFormat($item->amount, true);
            $item->trx_date_format = Carbon::parse($item->trx_date)->format('d F Y');
            $item->from = $froms->find($item->from_id);
            $item->topic = $topics->find($item->topic_id);
            $item->category = $categories->find($item->money_category_id);
            return $item;
        });

        return [
            'summary' => compact('expense', 'balance', 'income', 'failedParsed'),
            'list' => $list
        ];
    }

    public function getInvalidChat(Request $request)
    {
        return TelegramUpdate::unsolvedErrors()
            ->get()
            ->toJson();
    }

    public function updateInvalidChat(Request $request)
    {
        $chat = TelegramUpdate::find($request->update_id);
        $chat->error_solved = 1;
        $chat->save();

        return $chat;
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
