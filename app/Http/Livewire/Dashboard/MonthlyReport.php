<?php

namespace App\Http\Livewire\Dashboard;

use Illuminate\Http\Request;
use App\Models\MoneyTrack;
use App\Models\From;
use App\Models\Chatroom;
use App\Models\TelegramUpdate;
use Exception;
use Illuminate\Support\Carbon;
use Livewire\Component;
use Telegram\Bot\Laravel\Facades\Telegram;

class MonthlyReport extends Component
{
    public $froms,
        $chatrooms,
        $failedParsed,
        $reqMonth,
        $fromID,
        $chatRoomID,
        $list,
        $expense,
        $income,
        $balance,
        $errMsg,
        $isSendingReport;

    protected $bot;

    function __construct()
    {
        $this->bot = Telegram::bot('mybot');
    }

    public function goto($path)
    {
        return redirect()->to($path);
    }

    public function setMonth($month=null)
    {
        if(empty($month)) {
            $month = date('Y-m');
        } // endif
        $this->reqMonth = $month;
    }

    public function setFromID($id=null)
    {
        $this->fromID = $id;
    }

    private function setErrorMsg($msg='')
    {
        $this->errMsg = $msg;
    }

    public function setChatRoomID($id=null)
    {
        $this->chatRoomID = $id;
    }

    private function isExpense($trx)
    {
        return $trx->is_expense == 1;
    }
    private function trxTextColor($trx)
    {
        return $this->isExpense($trx) ? 'text-danger' : 'text-success';
    }

    private function trxText($trx)
    {
        return $this->isExpense($trx) ? 'Pengeluaran' : 'Pemasukan';
    }

    public function generateList()
    {
        try {
            $this->setErrorMsg();
            $list = MoneyTrack::listByMonth($this->reqMonth);
            if(! empty($this->fromID)) {
                $list = $list->where('from_id', $this->fromID);
            } // endif
            if(! empty($this->chatRoomID)) {
                $list = $list->where('chatroom_id', $this->chatRoomID);
            } // endif
            $list = $list->get();

            $summary = MoneyTrack::summaryByMonth($this->reqMonth, $list);
            $this->balance = rupiahFormat($summary['balance'], true);
            $this->expense = rupiahFormat($summary['expense'], true);
            $this->income = rupiahFormat($summary['income'], true);

            $list = $list->map(function ($item) {
                $item->amount_rp = rupiahFormat($item->amount, true);
                $item->trx_date_str = Carbon::parse($item->trx_date)->format('d F Y');
                $item->trx_color = $this->trxTextColor($item);
                $item->trx_str = $this->trxText($item);
                $item->from = $this->froms->find($item->from_id);
                return $item;
            });

            $this->list = $list;
        } catch (Exception $e) {
            $this->errMsg = $e->getMessage();
        }
    }

    public function sendReport()
    {
        $this->isSendingReport = true;
        $this->setErrorMsg();

        $from = From::find($this->fromID);
        $fullDate = Carbon::createFromFormat('Y-m', $this->reqMonth)->format('F Y');

        if(empty($this->fromID) || empty($this->chatRoomID)) {
            $this->setErrorMsg('Pengirim & Group Chat tidak boleh kosong');
            $this->isSendingReport = false;
            return false;
        } // endif

        $this->generateList();

        $lines = collect([
            "Hi, *{$from->username}*",
            "Laporan Bulan *{$fullDate}*:",
            "",
            "*Saldo: {$this->balance}*",
            "Pengeluaran: {$this->expense}",
            "Pemasukan: {$this->income}"
        ]);

        $send = $this->bot->sendMessage([
            'chat_id' => $this->chatRoomID,
            'text' => $lines->join(PHP_EOL),
            'parse_mode' => 'markdown'
        ]);
        $send->getMessageId();
        $this->isSendingReport = false;
        return true;
    }

    public function mount()
    {
        $this->setMonth();
        $this->froms = From::all();
        $this->chatrooms = Chatroom::all();
        $this->failedParsed = TelegramUpdate::unsolvedErrors()->count();
        $this->generateList();
    }

    public function render()
    {
        return view('livewire.dashboard.monthly-report', [
            'list' => $this->list
        ]);
    }
}
