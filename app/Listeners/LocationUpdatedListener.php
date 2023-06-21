<?php

namespace App\Listeners;

use App\Events\LocationUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Warehouse;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\TrackingRider;

class LocationUpdatedListener implements ShouldQueue
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
     * @param  \App\Events\LocationUpdated  $event
     * @return void
     */
    public function handle(LocationUpdated $event)
    {
        if($event->rider_id != null || $event->rider_id != ''){
            $data = json_decode($event->data, true);
            $current_lat = $data['latitude'];
            $current_lng = $data['longitude'];

            $rider = TrackingRider::where('rider_id', $event->rider_id)->first();
            // \Log::info($event->rider_id);
            if(!$rider){
                $get_rider = Order::with('warehouse')->where('rider_id', $event->rider_id)->where('status', 'accepted')->first();
                if($get_rider){
                    $user = new TrackingRider();
                    $user->rider_id = $event->rider_id;
                    $user->start_lat = $get_rider->warehouse->latitude;
                    $user->start_lng = $get_rider->warehouse->longitude;
                    $user->current_lat = $current_lat;
                    $user->current_lng = $current_lng;
                    $user->end_lat = $current_lat;
                    $user->end_lng = $current_lng;
                    if($user->save()){
                        event(new LocationUpdated($event->rider_id, json_encode($user)));
                    }
                }
            }else{
                $user = TrackingRider::find($rider->id);
                $user->current_lat = $current_lat;
                $user->current_lng = $current_lng;
                if($user->save()){
                    event(new LocationUpdated($event->rider_id, json_encode($user)));
                }
            }
        }
    }
}
