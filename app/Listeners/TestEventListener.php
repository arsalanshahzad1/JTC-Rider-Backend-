<?php

namespace App\Listeners;

use App\Events\TestEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

use DB;

class TestEventListener
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
     * @param  \App\Events\TestEvent  $event
     * @return void
     */
    public function handle(TestEvent $event)
    {
        $data = $event->data;
        $data = json_decode($data, true);
        // \Log::info($data);

        // $insert = DB::table('check_events')->insert([
		// 	'user_id' => $data['user_id'],
		// 	'lat' => $data['latitude'],
		// 	'lng' => $data['longitude']
		// ]);
    }
}
