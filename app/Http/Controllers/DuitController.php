<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MoneyTrack;
use App\Models\From;
use App\Models\Chatroom;
use App\Models\TelegramUpdate;
use Exception;
use Telegram\Bot\Laravel\Facades\Telegram;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class DuitController extends Controller
{
    protected $bot;
    function __construct()
    {
        $this->bot = Telegram::bot('mybot');
    }

    private function rupiahFormat($angka, $minusValue=false)
    {

        $final = "Rp" . number_format(abs($angka), 0,',','.');
        if($minusValue && ($angka < 0)) {
            $final = "-" . $final;
        } // endif
        return $final;
    }

    public function index()
    {
        return MoneyTrack::all();
    }

    public function duitAdd(Request $request)
    {
        MoneyTrack::create([
            'amount' => $request->amount,
            'is_expense' => $request->is_expense
        ]);
        return $request;
    }

    public function botCheck() {
        return $this->bot->getMe();
    }

    public function setWebHook() {
        $response = Telegram::setWebhook(['url' => url('api/webhook/callback')]);
        return $response;
    }

    public function unsetWebHook() {
        $response = Telegram::removeWebhook();
        return $response;
    }

    private function recordTelegramUpdate($msg)
    {
        $row = [
            'update_id' => $msg->update_id,
            'message' =>json_encode($msg->message)
        ];
        return TelegramUpdate::firstOrCreate($row);
    }

    public function recordDailyMessages()
    {
        // use 1st option if no webhook set
        $messages = $this->bot->getUpdates();

        // $messages = $this->bot->getWebhookUpdate();
        collect($messages)
        ->filter(function ($msg) {
            return ! empty($msg->message);
        })
        ->each(function ($msg) {
            $this->recordTelegramUpdate($msg);
        })->toArray();
        return $messages;
    }

    private function doSendReport($fromParam, $chatroomParam, $monthParam=null)
    {
        if (empty($monthParam)) {
            $monthParam = date('Y-m');
        } // endif

        $list = MoneyTrack::listByMonth($monthParam)
            ->where('from_id', $fromParam)
            ->where('chatroom_id', $chatroomParam)
            ->get();

        $from = From::find($fromParam);

        $summary = MoneyTrack::summaryByMonth($monthParam, $list);
        $balance = $this->rupiahFormat($summary['balance'], true);
        $income = $this->rupiahFormat($summary['income'], true);
        $expense = $this->rupiahFormat($summary['expense'], true);
        $fullDate = Carbon::createFromFormat('Y-m', $monthParam)->format('F Y');

        $lines = collect([
            "Hi, *{$from->username}*",
            "Laporan Bulan *{$fullDate}*:",
            "",
            "*Saldo: {$balance}*",
            "Pengeluaran: {$expense}",
            "Pemasukan: {$income}"
        ]);

        $send = $this->bot->sendMessage([
            'chat_id' => $chatroomParam,
            'text' => $lines->join(PHP_EOL),
            'parse_mode' => 'markdown'
        ]);
        return $send->getMessageId();
    }

    public function sendReport(Request $request)
    {
        $monthParam = empty($request->bulan) ? date('Y-m') : $request->bulan;
        $fromParam = intval($request->from);
        $chatroomParam = intval($request->chatroom);

        return $this->doSendReport($fromParam, $chatroomParam, $monthParam);
    }

    private function parseShortCurrency($str='10k')
    {
        $final = Str::replace(',', '.', $str);
        $isThousand = Str::contains($final, 'k', true);
        $final = (float) $final;
        if ($isThousand) {
            $final = $final * 1000;
        } // endif

        return intval($final);
    }

    private function parseTelegramMsg(TelegramUpdate $row)
    {
        try {
            $msg = json_decode($row->message);
            $multiData = explode(PHP_EOL, $msg->text);
            $hasEmptyAmount = false;

            $from = From::updateOrCreate([
                'id' => $msg->from->id
            ], [
                'username' => $msg->from->username,
                'first_name' => $msg->from->first_name ?? null,
                'last_name' => $msg->from->last_name ?? null,
            ]);
            $chatroom = Chatroom::updateOrCreate([
                'id' => $msg->chat->id,
            ], [
                'type' => $msg->chat->type,
                'title' => $msg->chat->title
            ]);

            $parseAll = collect($multiData)->map(function ($item) use (
                $msg,
                &$hasEmptyAmount,
                $from,
                $chatroom
            ) {
                $line = explode(' ', $item);
                $description = collect($line)->filter(function ($val, $key) {
                    return $key > 0;
                })->join(' ');
                $amount = $this->parseShortCurrency($line[0]);

                $moneyTrack = [
                    'description' => $description,
                    'trx_date' => date('Y-m-d', $msg->date),
                    'created_at' => date('Y-m-d H:i:s', $msg->date),
                    'amount' => $amount,
                    'is_expense' => $amount < 0,
                    'from_id' => $from->id,
                    'chatroom_id' => $chatroom->id,
                ];

                if ($amount === 0) {
                    $hasEmptyAmount = true;
                } // endif

                return $moneyTrack;
            });

            if ($hasEmptyAmount) {
                $row->has_error = 1;
            } else {
                $parseAll->each(function ($item) {
                    MoneyTrack::create($item);
                });
            } // endif

            $row->parsed_at = now();
            $row->save();
            return (object)[
                'from_id' => $from->id,
                'chatroom_id' => $chatroom->id,
                'parsed_count' => $parseAll->count(),
                'has_error' => $hasEmptyAmount
            ];
        } catch (Exception $e) {
            $row->has_error = 1;
            $row->parsed_at = now();
            $row->save();

            return (object)[
                'parsed_count' => 0,
                'has_error' => 1,
            ];
        }
    }

    public function parseDailyUpdate()
    {
        // MoneyTrack::truncate();
        // $rows = TelegramUpdate::all();

        $rows = TelegramUpdate::where('parsed_at', null)->get();
        return $rows->map(function ($row) {
            $result = ' > 0 rows parsed, failed process';
            $parseResult = $this->parseTelegramMsg($row);
            $from_id = null;
            $chatroom_id = null;
            $msg = $row->update_id . $result;
            $has_error = $parseResult->has_error;

            if (! $parseResult->has_error) {
                $result = ' > ' . $parseResult->parsed_count . ' rows parsed.';
                $from_id = $parseResult->from_id;
                $chatroom_id = $parseResult->chatroom_id;
            } // endif

            return (object) compact('msg', 'from_id', 'chatroom_id', 'has_error', 'result');
        });
    }

    public function updateMoneyTrack(Request $request, $id=null)
    {
        if (! empty($id)) {
            $track = MoneyTrack::find($id);
        } else {
            $track = new MoneyTrack();
        } // endif

        $isExpense = $request->is_expense === "1";
        $amount = intval($request->amount);
        $amount = $isExpense ? abs($amount) * -1 : $amount;
        $updateArr = [
            'amount' => $amount,
            'description' => $request->description,
            'trx_date' => $request->trx_date
        ];
        collect($updateArr)->each(function ($item, $key) use ($track) {
            $track->{$key} = $item;
        });
        $track->save();
        return redirect()->back();

    }

    public function editMoneyTrack(Request $request)
    {
        $id = $request->id;
        return $this->updateMoneyTrack($request, $id);
    }

    public function addMoneyTrack(Request $request)
    {
        return $this->updateMoneyTrack($request, null);
    }

    public function deleteMoneyTrack(Request $request)
    {
        $track = MoneyTrack::find($request->id);
        $track->delete();
        return redirect()->back();
    }

    public function webhookCallback(Request $request)
    {
        $this->recordTelegramUpdate($request);
        $parsedMessages = $this->parseDailyUpdate();

        if ($parsedMessages->count() < 1) {
            return false;
        } // endif
        $parsedMessage = $parsedMessages[0];

        if ($parsedMessage->has_error) {
            return false;
        } else {
            return $this->doSendReport($parsedMessage->from_id, $parsedMessage->chatroom_id);
        } // endif

    }
}
