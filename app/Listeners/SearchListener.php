<?php

namespace App\Listeners;

use App\Events\SearchEvent;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SearchListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(SearchEvent $event)
    {
        // if($event->rider_id == 11){
        $data = $event->searchResults;
        $data = json_decode($data, true);

        \Log::info($data);
        // }
    }
}
