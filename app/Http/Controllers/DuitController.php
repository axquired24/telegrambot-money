<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MoneyTrack;
use App\Models\TelegramUpdate;
use Exception;
use Telegram\Bot\Laravel\Facades\Telegram;
use Illuminate\Support\Str;


class DuitController extends Controller
{
    protected $bot;
    function __construct()
    {
        $this->bot = Telegram::bot('mybot');
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

    public function recordDailyMessages()
    {
        $messages = $this->bot->getUpdates();
        collect($messages)
        ->filter(function ($msg) {
            return ! empty($msg->message);
        })
        ->each(function ($msg) {
            $row = [
                'update_id' => $msg->update_id,
                'message' =>json_encode($msg->message)
            ];
            TelegramUpdate::firstOrCreate($row);
        })->toArray();
        return $messages;
    }

    public function sendReport()
    {
        $chatId = -941429400;
        $lines = collect([
            'Laporan Bulan Ini:',
            '*Saldo: Rp 3.000*',
            'Pengeluaran: Rp 10.000',
            'Pemasukan: Rp 8.000'
        ]);

        $send = $this->bot->sendMessage([
            'chat_id' => $chatId,
            'text' => $lines->join(PHP_EOL),
            'parse_mode' => 'markdown'
        ]);
        return $send->getMessageId();
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

            $parseAll = collect($multiData)->map(function ($item) use ($msg, &$hasEmptyAmount) {
                $line = explode(' ', $item);
                $description = collect($line)->filter(function ($val, $key) {
                    return $key > 0;
                })->join(' ');
                $amount = $this->parseShortCurrency($line[0]);

                $prepare = [
                    'description' => $description,
                    'trx_date' => date('Y-m-d', $msg->date),
                    'created_at' => date('Y-m-d H:i:s', $msg->date),
                    'amount' => $amount,
                    'is_expense' => $amount < 0
                ];

                if ($amount === 0) {
                    $hasEmptyAmount = true;
                } // endif

                return $prepare;
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
        $rows = TelegramUpdate::where('parsed_at', null)->get();
        return $rows->map(function ($row) {
            $result = ' > 0 rows parsed, failed process';
            $parseResult = $this->parseTelegramMsg($row);
            if (! $parseResult->has_error) {
                $result = ' > ' . $parseResult->parsed_count . ' rows parsed.';
            } // endif

            return $row->update_id . $result;
        });
    }

    public function editMoneyTrack(Request $request)
    {
        $track = MoneyTrack::find($request->id);
        $isExpense = $request->is_expense === "1";
        $amount = intval($request->amount);
        $amount = $isExpense ? abs($amount) * -1 : $amount;
        $updateArr = [
            'amount' => $amount,
            'description' => $request->description,
            'trx_date' => $request->trx_date
        ];
        $track->update($updateArr);
        return redirect()->back();

    }

    public function deleteMoneyTrack(Request $request)
    {
        $track = MoneyTrack::find($request->id);
        $track->delete();
        return redirect()->back();
    }
}
