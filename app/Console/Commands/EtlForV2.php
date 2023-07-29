<?php

namespace App\Console\Commands;

use App\Models\From;
use App\Models\Chatroom;
use App\Models\MoneyTrack;
use App\Models\Topic;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class EtlForV2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:etl-v2';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert V1 data to V2';

    private function transformChatrooms()
    {
        $oldChatrooms = DB::connection('mysql.old')
            ->table('chatrooms')
            ->select('*')
            ->get();

        $prepareChatrooms = $oldChatrooms->map(function ($room) {
            $chatroom = Chatroom::updateOrCreate([
                'id' => $room->id
            ], [
                'title' => $room->title,
                'type' => $room->type
            ]);

            $topic = Topic::updateOrCreate([
                'topic_id' => null,
                'chatroom_id' => $room->id,
            ], [
                'name' => '#v1 ' . $room->title
            ]);

            return $chatroom;
        });
    }

    private function transformSenders()
    {
        $oldSenders = DB::connection('mysql.old')
            ->table('froms')
            ->select('*')
            ->get();

        $prepareSenders = $oldSenders->map(function ($sender) {
            $from = From::updateOrCreate([
                'id' => $sender->id
            ], [
                'username' => $sender->username,
                'first_name' => $sender->first_name,
                'last_name' => $sender->last_name
            ]);

            return $from;
        });
    }

    private function transformTracks()
    {
        $self = $this;
        DB::connection('mysql.old')
            ->table('money_tracks')
            ->select('*')
            ->limit(2)
            ->orderBy('id', 'asc')
            ->chunk(100, function ($tracks) use ($self) {
                $tracks->each(function ($track) use ($self) {
                    $topic = Topic::where([
                        ['topic_id', null],
                        ['chatroom_id', $track->chatroom_id ?? null]
                    ])->first();

                    if(empty($track->chatroom_id)) {
                        $self->error('Failed old.MoneyTrack ID: ' . $track->id);
                    } else {
                        $updateQuery = (array) $track;
                        unset($updateQuery['id']);
                        unset($updateQuery['chatroom_id']);
                        $updateQuery['topic_id'] = $topic->id;

                        MoneyTrack::create($updateQuery);
                        $self->info('Processed old.MoneyTrack ID: ' . $track->id);
                    } // endif

                });
            });
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info($this->description);
        $this->transformChatrooms();
        $this->transformSenders();
        $this->transformTracks();
    }
}
