<?php

namespace App\Jobs;

use App\Models\PlaybackEvent;
use App\Services\ProofOfPlayService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessPlaybackEventJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $playbackEventId) {}

    public function handle(ProofOfPlayService $pop): void
    {
        $event = PlaybackEvent::find($this->playbackEventId);
        if ($event && ! $event->processed) {
            $pop->validate($event);
        }
    }
}
