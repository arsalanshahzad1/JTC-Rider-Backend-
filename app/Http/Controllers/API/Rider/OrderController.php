<?php

namespace App\Http\Controllers\Api\Rider;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\CustomerRequest;
use App\Models\RiderOrder;
use App\Models\Warehouse;
use App\Models\Role;
use App\Models\User;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\TrackingRider;

use App\Events\SearchEvent;
use App\Events\LocationUpdated;
use Carbon\Carbon;

class OrderController extends Controller
{
	public function distance($lat1, $lng1, $lat2, $lng2) {
        $theta = $lng1 - $lng2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $kilometers = $miles * 1.60934;
        return $kilometers;
    }

    public function calculateDistance($originLat, $originLng, $destinationLat, $destinationLng, $mode)
    {
        // $originLat = '40.7480245';
        // $originLng = '-73.989467';
        // $destinationLat = '40.7453979';
        // $destinationLng = '-73.9903243';
        // $mode = 'walking'; // e.g., "driving", "walking", "transit", "bicycling"

        $apiKey = env('GOOGLE_MAPS_API_KEY');

        $response = Http::get('https://maps.googleapis.com/maps/api/directions/json', [
            'origin' => $originLat . ',' . $originLng,
            'destination' => $destinationLat . ',' . $destinationLng,
            'mode' => $mode,
            'key' => $apiKey,
        ]);

        $data = $response->json();

        if ($response->ok() && $data['status'] === 'OK') {
            // distance
            $distance = $data['routes'][0]['legs'][0]['distance']['value'];
            $distance = $distance / 1000;

            // time
            $duration = $data['routes'][0]['legs'][0]['duration']['text'];
            preg_match('/(\d+)/', $duration, $matches);
            $duration = (int) $matches[0];

            $data = [
                'distance' => round($distance, 1),
                'distanceKM' => round($distance, 1). ' km',
                'durationMIN' => $duration. ' mins',
            ];

            return $data;
        }

        $error = [
            'error' => 'Unable to calculate the distance and duration.',
        ];

        return $error;
    }

    public function checkDis(Request $request){
        return $this->calculateDistance();
    }

    // public function saleToRiders(Request $request, $sale_id)
    // {
    //     $sale = Sale::find($sale_id);

    //     if($sale){
    //         if($sale->order_type == "delivery"){

    //             if($sale->latitude == '' && $sale->longitude == ''){
    //                 $sale->address = 'default address';
    //                 $sale->latitude = '40.7480245';
    //                 $sale->longitude = '-73.989467';
    //             }

    //             $warehouse = Warehouse::find($sale->warehouse_id);

    //             if($warehouse){
    //                 $start_lat = $warehouse->latitude;
    //                 $start_lng = $warehouse->longitude;

    //                 $kilometers = 8;

    //                 $riderRole = Role::where('name', 'rider')->first();
    //                 $users = $riderRole->users()->where('status', 1)->get();

    //                 foreach ($users as $key => $value) {
    //                     $rider = User::find($value->id);
    //                     if($rider){
    //                         $end_lat = $rider->latitude;
    //                         $end_lng = $rider->longitude;

    //                         $rider_distance = $this->distance($start_lat, $start_lng, $end_lat, $end_lng);

    //                         if($rider_distance <= $kilometers){
    //                             $rider_time = ($rider_distance / 60) * 60;
    //                             $rider_time = $rider_time + 0.5;

    //                             $check_order = RiderOrder::where('order_id', $sale->id)->first();

    //                             if(!$check_order){
    //                                 $check_request = CustomerRequest::where('rider_id', $value->id)
    //                                 ->where('order_id', $sale->id)
    //                                 ->first();

    //                                 if(!$check_request){
    //                                     $rider = [
    //                                         'rider_id' => $value->id,
    //                                         'order_id' => $sale->id,
    //                                         'customer_id' => $sale->customer_id,
    //                                         'warehouse_id' => $sale->warehouse_id,
    //                                         'created_at' => now(),
    //                                         'updated_at' => now()
    //                                     ];

    //                                     DB::table('customer_requests')->insert($rider);
    //                                 }
    //                             }
    //                         }else{
    //                             $check_request = CustomerRequest::where('rider_id', $value->id)->first();

    //                             if($check_request){
    //                                 CustomerRequest::where('rider_id', $value->id)->delete();
    //                             }
    //                         }
    //                     }
    //                 }

    //                 $upcoming_orders = CustomerRequest::get();

    //                 if(!empty($upcoming_orders)){
    //                     $check_rider = [];
    //                     foreach ($upcoming_orders as $key => $value) {
    //                         if(!in_array($value->rider_id, $check_rider)){
    //                             array_push($check_rider, $value->rider_id);
    //                             $rider = User::find($value->rider_id);

    //                             if($rider){
    //                                 $end_lat = $rider->latitude;
    //                                 $end_lng = $rider->longitude;

    //                                 $rider_distance = $this->distance($start_lat, $start_lng, $end_lat, $end_lng);
    //                                 $rider_time = ($rider_distance / 60) * 60;
    //                                 $rider_time = $rider_time + 0.5;

    //                                 $riders = CustomerRequest::where('rider_id', $rider->id)->get();

    //                                 if(!empty($riders)){
    //                                     $data_order_detail = [];
    //                                     foreach ($riders as $key1 => $value1) {
    //                                         $order = Sale::find($value1->order_id);
    //                                         if($order){
    //                                             $order_distance = $this->distance($start_lat, $start_lng, $order->latitude, $order->longitude);
    //                                             $order_time = ($order_distance / 60) * 60;
    //                                             $order_time = $order_time + 0.5;

    //                                             $customer = Customer::find($order->customer_id);

    //                                             $data_order_detail[] = [
    //                                                 'pickup' => [
    //                                                     'distance' => number_format((float)$rider_distance, 1, '.', ''),
    //                                                     'time' => number_format((float)$rider_time, 1, '.', ''),
    //                                                     'restaurent' => $warehouse->name,
    //                                                     'address' => $warehouse->address
    //                                                 ],
    //                                                 'dropoff' => [
    //                                                     'distance' => number_format((float)$order_distance, 1, '.', ''),
    //                                                     'time' => number_format((float)$order_time, 1, '.', ''),
    //                                                     'username' => $customer->name,
    //                                                     'address' => $sale->address
    //                                                 ],
    //                                                 'total_amount' => $order->grand_total
    //                                             ];
    //                                         }
    //                                     }

    //                                     $total_order = $riders->count();
    //                                     $rider_data = [
    //                                         'rider_id' => $rider->id,
    //                                         'total_order' => $total_order,
    //                                         'acceptance_rate' => 0,
    //                                         'orders' => $data_order_detail
    //                                     ];
    //                                 }
    //                             }

    //                             event(new SearchEvent($rider->id, json_encode($rider_data)));
    //                         }
    //                     }
    //                 }

    //                 $data = [
    //                     'status' => true,
    //                     'message' => "send order request to all nearby riders successfully!"
    //                 ];

    //                 return response()->json($data, 201);
    //             }else{
    //                 $data = [
    //                     'status' => false,
    //                     'message' => "warehouse not found"
    //                 ];
        
    //                 return response()->json($data, 404);
    //             }
    //         }else{
    //             $data = [
    //                 'status' => false,
    //                 'message' => "order type is not delivery"
    //             ];
    
    //             return response()->json($data, 422);
    //         }
    //     }else{
    //         $data = [
    //             'status' => false,
    //             'message' => "Sale not found"
    //         ];

    //         return response()->json($data, 404);
    //     }
    // }

    public function runForTwoMinutes($warehouse, $start_lat, $start_lng)
    {
        $endTime = Carbon::now()->addMinutes(2);
        // $endTime = Carbon::now()->addSeconds(30);
    
        while (Carbon::now()->lt($endTime)) {
            $upcoming_orders = CustomerRequest::get();

            if(!empty($upcoming_orders)){
                $check_rider = [];
                foreach ($upcoming_orders as $key => $value) {
                    if(!in_array($value->rider_id, $check_rider)){
                        array_push($check_rider, $value->rider_id);
                        $rider = User::find($value->rider_id);

                        if($rider){
                            $end_lat = $rider->latitude;
                            $end_lng = $rider->longitude;

                            $mode = 'bicycling';

                            // $rider_distance = $this->distance($start_lat, $start_lng, $end_lat, $end_lng);
                            $rider_distance = $this->calculateDistance($start_lat, $start_lng, $end_lat, $end_lng, $mode);

                            $riders = CustomerRequest::where('rider_id', $rider->id)->get();

                            if(!empty($riders)){
                                $data_order_detail = [];
                                foreach ($riders as $key1 => $value1) {
                                    $orderr = Order::with('payment')->find($value1->order_id);
                                    if($orderr){
                                        $order_distance = $this->calculateDistance($start_lat, $start_lng, $orderr->latitude, $orderr->longitude, $mode);
                                        $customer = Customer::find($orderr->user_id);

                                        if($orderr->payment->payment_status == 1){
                                            $p_s = "Paid";
                                        }else{
                                            $p_s = "Un paid";
                                        }
                                        

                                        $data_order_detail[] = [
                                            'pickup' => [
                                                'distance' => $rider_distance['distanceKM'],
                                                'time' => $rider_distance['durationMIN'],
                                                'restaurent' => $warehouse->name,
                                                'address' => $warehouse->address
                                            ],
                                            'dropoff' => [
                                                'distance' => $order_distance['distanceKM'],
                                                'time' => $order_distance['durationMIN'],
                                                'username' => $customer->name,
                                                'address' => $orderr->address
                                            ],
                                            'payment_status' => $p_s,
                                            'payment_type' => $orderr->payment->payment_type,
                                            'total_amount' => $orderr->payment->total_amount
                                        ];
                                    }
                                }

                                $total_order = $riders->count();
                                $rider_data = [
                                    'rider_id' => $rider->id,
                                    'total_order' => $total_order,
                                    'acceptance_rate' => 0,
                                    'orders' => $data_order_detail
                                ];
                            }
                        }

                        event(new SearchEvent($rider->id, json_encode($rider_data)));
                    }
                }
            }

            sleep(5);
        }

        return "timeup";
    }

    public function orderToRiders(Request $request, $order_id)
    {
        $order = Order::find($order_id);

        if($order){
            if($order->order_type == "delivery"){

                if($order->latitude == '' && $order->longitude == ''){
                    $order->address = 'default address';
                    $order->latitude = '40.7480245';
                    $order->longitude = '-73.989467';
                }

                $warehouse = Warehouse::find($order->warehouse_id);

                if($warehouse){
                    $start_lat = $warehouse->latitude;
                    $start_lng = $warehouse->longitude;

                    $kilometers = 8;

                    $riderRole = Role::where('name', 'rider')->first();
                    $users = $riderRole->users()->where('status', 1)->get();

                    foreach ($users as $key => $value) {
                        $rider = User::find($value->id);
                        if($rider){
                            $end_lat = $rider->latitude;
                            $end_lng = $rider->longitude;
                            $mode = 'bicycling';

                            // $rider_distance = $this->distance($start_lat, $start_lng, $end_lat, $end_lng);
                            $rider_distance = $this->calculateDistance($start_lat, $start_lng, $end_lat, $end_lng, $mode);

                            if($rider_distance['distance'] <= $kilometers){

                                $check_order = Order::where('id', $order->id)->where('rider_id', null)->first();

                                if($check_order){
                                    $check_request = CustomerRequest::where('rider_id', $value->id)
                                    ->where('order_id', $order->id)
                                    ->first();

                                    if(!$check_request){
                                        $rider = [
                                            'rider_id' => $value->id,
                                            'order_id' => $order->id,
                                            'customer_id' => $order->user_id,
                                            'warehouse_id' => $order->warehouse_id,
                                            'created_at' => now(),
                                            'updated_at' => now()
                                        ];

                                        DB::table('customer_requests')->insert($rider);
                                    }
                                }
                            }else{
                                $check_request = CustomerRequest::where('rider_id', $value->id)->first();

                                if($check_request){
                                    CustomerRequest::where('rider_id', $value->id)->delete();
                                }
                            }
                        }
                    }

                    $check_time = $this->runForTwoMinutes($warehouse, $start_lat, $start_lng);

                    if($check_time == "timeup"){
                        $data = [
                            'status' => true,
                            'message' => "send order request to all nearby riders successfully!"
                        ];

                        return response()->json($data, 201);
                    }
                }else{
                    $data = [
                        'status' => false,
                        'message' => "warehouse not found"
                    ];
        
                    return response()->json($data, 404);
                }
            }else{
                $data = [
                    'status' => false,
                    'message' => "order type is not delivery"
                ];
    
                return response()->json($data, 422);
            }
        }else{
            $data = [
                'status' => false,
                'message' => "Sale not found"
            ];

            return response()->json($data, 404);
        }
    }

    // public function orderToRiders(Request $request, $order_id)
    // {
    //     $order = Order::find($order_id);

    //     if($order){
    //         if($order->order_type == "delivery"){

    //             if($order->latitude == '' && $order->longitude == ''){
    //                 $order->address = 'default address';
    //                 $order->latitude = '40.7480245';
    //                 $order->longitude = '-73.989467';
    //             }

    //             $warehouse = Warehouse::find($order->warehouse_id);

    //             if($warehouse){
    //                 $start_lat = $warehouse->latitude;
    //                 $start_lng = $warehouse->longitude;

    //                 $kilometers = 8;

    //                 $riderRole = Role::where('name', 'rider')->first();
    //                 $users = $riderRole->users()->where('status', 1)->get();

    //                 foreach ($users as $key => $value) {
    //                     $rider = User::find($value->id);
    //                     if($rider){
    //                         $end_lat = $rider->latitude;
    //                         $end_lng = $rider->longitude;
    //                         $mode = 'bicycling';

    //                         // $rider_distance = $this->distance($start_lat, $start_lng, $end_lat, $end_lng);
    //                         $rider_distance = $this->calculateDistance($start_lat, $start_lng, $end_lat, $end_lng, $mode);

    //                         if($rider_distance['distance'] <= $kilometers){

    //                             $check_order = Order::where('id', $order->id)->where('rider_id', null)->first();

    //                             if($check_order){
    //                                 $check_request = CustomerRequest::where('rider_id', $value->id)
    //                                 ->where('order_id', $order->id)
    //                                 ->first();

    //                                 if(!$check_request){
    //                                     $rider = [
    //                                         'rider_id' => $value->id,
    //                                         'order_id' => $order->id,
    //                                         'customer_id' => $order->user_id,
    //                                         'warehouse_id' => $order->warehouse_id,
    //                                         'created_at' => now(),
    //                                         'updated_at' => now()
    //                                     ];

    //                                     DB::table('customer_requests')->insert($rider);
    //                                 }
    //                             }
    //                         }else{
    //                             $check_request = CustomerRequest::where('rider_id', $value->id)->first();

    //                             if($check_request){
    //                                 CustomerRequest::where('rider_id', $value->id)->delete();
    //                             }
    //                         }
    //                     }
    //                 }

    //                 $upcoming_orders = CustomerRequest::get();

    //                 if(!empty($upcoming_orders)){
    //                     $check_rider = [];
    //                     foreach ($upcoming_orders as $key => $value) {
    //                         if(!in_array($value->rider_id, $check_rider)){
    //                             array_push($check_rider, $value->rider_id);
    //                             $rider = User::find($value->rider_id);

    //                             if($rider){
    //                                 $end_lat = $rider->latitude;
    //                                 $end_lng = $rider->longitude;

    //                                 $mode = 'bicycling';

    //                                 // $rider_distance = $this->distance($start_lat, $start_lng, $end_lat, $end_lng);
    //                                 $rider_distance = $this->calculateDistance($start_lat, $start_lng, $end_lat, $end_lng, $mode);

    //                                 $riders = CustomerRequest::where('rider_id', $rider->id)->get();

    //                                 if(!empty($riders)){
    //                                     $data_order_detail = [];
    //                                     foreach ($riders as $key1 => $value1) {
    //                                         $orderr = Order::with('payment')->find($value1->order_id);
    //                                         if($orderr){
    //                                             $order_distance = $this->calculateDistance($start_lat, $start_lng, $orderr->latitude, $orderr->longitude, $mode);
    //                                             $customer = Customer::find($orderr->user_id);

    //                                             if($orderr->payment->payment_status == 1){
    //                                                 $p_s = "Paid";
    //                                             }else{
    //                                                 $p_s = "Un paid";
    //                                             }
                                                

    //                                             $data_order_detail[] = [
    //                                                 'pickup' => [
    //                                                     'distance' => $rider_distance['distanceKM'],
    //                                                     'time' => $rider_distance['durationMIN'],
    //                                                     'restaurent' => $warehouse->name,
    //                                                     'address' => $warehouse->address
    //                                                 ],
    //                                                 'dropoff' => [
    //                                                     'distance' => $order_distance['distanceKM'],
    //                                                     'time' => $order_distance['durationMIN'],
    //                                                     'username' => $customer->name,
    //                                                     'address' => $orderr->address
    //                                                 ],
    //                                                 'payment_status' => $p_s,
    //                                                 'payment_type' => $orderr->payment->payment_type,
    //                                                 'total_amount' => $orderr->payment->total_amount
    //                                             ];
    //                                         }
    //                                     }

    //                                     $total_order = $riders->count();
    //                                     $rider_data = [
    //                                         'rider_id' => $rider->id,
    //                                         'total_order' => $total_order,
    //                                         'acceptance_rate' => 0,
    //                                         'orders' => $data_order_detail
    //                                     ];
    //                                 }
    //                             }

    //                             event(new SearchEvent($rider->id, json_encode($rider_data)));
    //                         }
    //                     }
    //                 }

    //                 $data = [
    //                     'status' => true,
    //                     'message' => "send order request to all nearby riders successfully!"
    //                 ];

    //                 return response()->json($data, 201);
    //             }else{
    //                 $data = [
    //                     'status' => false,
    //                     'message' => "warehouse not found"
    //                 ];
        
    //                 return response()->json($data, 404);
    //             }
    //         }else{
    //             $data = [
    //                 'status' => false,
    //                 'message' => "order type is not delivery"
    //             ];
    
    //             return response()->json($data, 422);
    //         }
    //     }else{
    //         $data = [
    //             'status' => false,
    //             'message' => "Sale not found"
    //         ];

    //         return response()->json($data, 404);
    //     }
    // }

    // public function saleToRiders(Request $request, $sale_id)
    // {
    //     $sale = Sale::find($sale_id);

    //     if($sale){
    //         if($sale->order_type == "delivery"){

    //             if($sale->latitude == '' && $sale->longitude == ''){
    //                 $sale->address = 'default address';
    //                 $sale->latitude = '40.7480245';
    //                 $sale->longitude = '-73.989467';
    //             }

    //             $warehouse = Warehouse::find($sale->warehouse_id);

    //             if($warehouse){
    //                 $start_lat = $warehouse->latitude;
    //                 $start_lng = $warehouse->longitude;

    //                 $kilometers = 8;

    //                 $riderRole = Role::where('name', 'rider')->first();
    //                 $users = $riderRole->users()->where('status', 1)->get();

    //                 $rider_array = [];
    //                 foreach ($users as $key => $value) {
    //                     $rider = User::find($value->id);
    //                     if($rider){
    //                         $end_lat = $rider->latitude;
    //                         $end_lng = $rider->longitude;

    //                         $rider_distance = $this->distance($start_lat, $start_lng, $end_lat, $end_lng);

    //                         if($rider_distance <= $kilometers){
    //                             $rider_time = ($rider_distance / 60) * 60;
    //                             $rider_time = $rider_time + 0.5;

    //                             $check_order = RiderOrder::where('order_id', $sale->id)->first();

    //                             if(!$check_order){
    //                                 $check_request = CustomerRequest::where('rider_id', $value->id)
    //                                 ->where('order_id', $sale->id)
    //                                 ->first();

    //                                 if(!$check_request){
    //                                     $rider = [
    //                                         'rider_id' => $value->id,
    //                                         'order_id' => $sale->id,
    //                                         'customer_id' => $sale->customer_id,
    //                                         'warehouse_id' => $sale->warehouse_id,
    //                                         'created_at' => now(),
    //                                         'updated_at' => now()
    //                                     ];

    //                                     DB::table('customer_requests')->insert($rider);
    //                                 }
    //                             }

    //                             $get_riders = CustomerRequest::where('rider_id', $value->id)->get();

    //                             if(!empty($get_riders)){
    //                                 foreach ($get_riders as $key2 => $value2) {
    //                                     $order_distance = $this->distance($start_lat, $start_lng, $sale->latitude, $sale->longitude);
    //                                     $order_time = ($order_distance / 60) * 60;
    //                                     $order_time = $order_time + 0.5;

    //                                     $customer = Customer::find($sale->customer_id);

    //                                     if(isset($rider_array[$value2->rider_id])) {
    //                                         $rider_array[$value2->rider_id][] = [
    //                                             'total_order' => $get_riders->count(),
    //                                             'acceptance_rate' => 0,
    //                                             'pickup' => [
    //                                                 'distance' => number_format((float)$rider_distance, 1, '.', ''),
    //                                                 'time' => number_format((float)$rider_time, 1, '.', ''),
    //                                                 'restaurent' => $warehouse->name,
    //                                                 'address' => $warehouse->address
    //                                             ],
    //                                             'dropoff' => [
    //                                                 'distance' => number_format((float)$order_distance, 1, '.', ''),
    //                                                 'time' => number_format((float)$order_time, 1, '.', ''),
    //                                                 'username' => $customer->name,
    //                                                 'address' => $sale->address
    //                                             ],
    //                                             'total_amount' => $sale->grand_total
    //                                         ];
    //                                     }else{
    //                                         $rider_array[$value2->rider_id][] = [
    //                                             'total_order' => $get_riders->count(),
    //                                             'acceptance_rate' => 0,
    //                                             'pickup' => [
    //                                                 'distance' => number_format((float)$rider_distance, 1, '.', ''),
    //                                                 'time' => number_format((float)$rider_time, 1, '.', ''),
    //                                                 'restaurent' => $warehouse->name,
    //                                                 'address' => $warehouse->address
    //                                             ],
    //                                             'dropoff' => [
    //                                                 'distance' => number_format((float)$order_distance, 1, '.', ''),
    //                                                 'time' => number_format((float)$order_time, 1, '.', ''),
    //                                                 'username' => $customer->name,
    //                                                 'address' => $sale->address
    //                                             ],
    //                                             'total_amount' => $sale->grand_total
    //                                         ];
    //                                     }
    //                                 }
    //                             }

    //                             // event(new SearchEvent(json_encode($data_order_detail)));
    //                         }else{
    //                             $check_request = CustomerRequest::where('rider_id', $value->id)->first();

    //                             if($check_request){
    //                                 CustomerRequest::where('rider_id', $value->id)->delete();
    //                             }
    //                         }
    //                     }
    //                 }

    //                 return $rider_array;

    //                 $data = [
    //                     'status' => true,
    //                     'message' => "send order request to all nearby riders successfully!"
    //                 ];

    //                 return response()->json($data, 201);
    //             }else{
    //                 $data = [
    //                     'status' => false,
    //                     'message' => "warehouse not found"
    //                 ];
        
    //                 return response()->json($data, 404);
    //             }
    //         }else{
    //             $data = [
    //                 'status' => false,
    //                 'message' => "order type is not delivery"
    //             ];
    
    //             return response()->json($data, 422);
    //         }
    //     }else{
    //         $data = [
    //             'status' => false,
    //             'message' => "Sale not found"
    //         ];

    //         return response()->json($data, 404);
    //     }
    // }

    // public function saleToRiders(Request $request, $sale_id)
    // {
    //     $sale = Sale::find($sale_id);

    //     if($sale){
    //         if($sale->order_type == "delivery"){

    //             if($sale->latitude == '' && $sale->longitude == ''){
    //                 $sale->address = 'default address';
    //                 $sale->latitude = '40.7480245';
    //                 $sale->longitude = '-73.989467';
    //             }

    //             $warehouse = Warehouse::find($sale->warehouse_id);

    //             if($warehouse){
    //                 $start_lat = $warehouse->latitude;
    //                 $start_lng = $warehouse->longitude;

    //                 $kilometers = 8;

    //                 $riderRole = Role::where('name', 'rider')->first();
    //                 $users = $riderRole->users()->where('status', 1)->get();

    //                 foreach ($users as $key => $value) {
    //                     $rider = User::find($value->id);
    //                     if($rider){
    //                         $end_lat = $rider->latitude;
    //                         $end_lng = $rider->longitude;

    //                         $rider_distance = $this->distance($start_lat, $start_lng, $end_lat, $end_lng);

    //                         if($rider_distance <= $kilometers){
    //                             $rider_time = ($rider_distance / 60) * 60;
    //                             $rider_time = $rider_time + 0.5;

    //                             $check_order = RiderOrder::where('order_id', $sale->id)->first();

    //                             if(!$check_order){
    //                                 $check_request = CustomerRequest::where('rider_id', $value->id)
    //                                 ->where('order_id', $sale->id)
    //                                 ->first();

    //                                 if(!$check_request){
    //                                     $rider = [
    //                                         'rider_id' => $value->id,
    //                                         'order_id' => $sale->id,
    //                                         'customer_id' => $sale->customer_id,
    //                                         'warehouse_id' => $sale->warehouse_id,
    //                                         'created_at' => now(),
    //                                         'updated_at' => now()
    //                                     ];

    //                                     DB::table('customer_requests')->insert($rider);
    //                                 }
    //                             }

    //                             $order_distance = $this->distance($start_lat, $start_lng, $sale->latitude, $sale->longitude);
    //                             $order_time = ($order_distance / 60) * 60;
    //                             $order_time = $order_time + 0.5;

    //                             $customer = Customer::find($sale->customer_id);

    //                             $data_total_order = CustomerRequest::where('rider_id', $value->rider_id)->count();
    //                             $data_order_detail = [
    //                                 'total_order' => $data_total_order,
    //                                 'acceptance_rate' => 0,
    //                                 'pickup' => [
    //                                     'distance' => number_format((float)$rider_distance, 1, '.', ''),
    //                                     'time' => number_format((float)$rider_time, 1, '.', ''),
    //                                     'restaurent' => $warehouse->name,
    //                                     'address' => $warehouse->address
    //                                 ],
    //                                 'dropoff' => [
    //                                     'distance' => number_format((float)$order_distance, 1, '.', ''),
    //                                     'time' => number_format((float)$order_time, 1, '.', ''),
    //                                     'username' => $customer->name,
    //                                     'address' => $sale->address
    //                                 ],
    //                                 'total_amount' => $sale->grand_total
    //                             ];

    //                             event(new SearchEvent($value->id, json_encode($data_order_detail)));
    //                         }else{
    //                             $check_request = CustomerRequest::where('rider_id', $value->id)->first();

    //                             if($check_request){
    //                                 CustomerRequest::where('rider_id', $value->id)->delete();
    //                             }
    //                         }
    //                     }
    //                 }

    //                 $data = [
    //                     'status' => true,
    //                     'message' => "send order request to all nearby riders successfully!"
    //                 ];

    //                 return response()->json($data, 201);
    //             }else{
    //                 $data = [
    //                     'status' => false,
    //                     'message' => "warehouse not found"
    //                 ];
        
    //                 return response()->json($data, 404);
    //             }
    //         }else{
    //             $data = [
    //                 'status' => false,
    //                 'message' => "order type is not delivery"
    //             ];
    
    //             return response()->json($data, 422);
    //         }
    //     }else{
    //         $data = [
    //             'status' => false,
    //             'message' => "Sale not found"
    //         ];

    //         return response()->json($data, 404);
    //     }
    // }

	// public function saleToRiders(Request $request, $sale_id)
    // {
    //     $sale = Sale::find($sale_id);

    //     if($sale){
    //         if($sale->order_type == "delivery"){

    //         	$warehouse = Warehouse::find($sale->warehouse_id);

    //             if($warehouse){
    //                 $start_lat = $warehouse->latitude;
    //                 $start_lng = $warehouse->longitude;

    //                 $kilometers = 8;

	//                 $riderRole = Role::where('name', 'rider')->first();
	//                 $users = $riderRole->users()->where('status', 1)->get();

	//                 foreach ($users as $key => $value) {
	//                 	$rider = User::find($value->id);
    //                     if($rider){
    //                         $end_lat = $rider->latitude;
    //                         $end_lng = $rider->longitude;

    //                         $rider_distance = $this->distance($start_lat, $start_lng, $end_lat, $end_lng);

    //                         if($rider_distance <= $kilometers){

	// 		                    $check_order = RiderOrder::where('order_id', $sale->id)->first();

	// 		                    if(!$check_order){
	// 		                        $check_request = CustomerRequest::where('rider_id', $value->id)
	// 		                        ->where('order_id', $sale->id)
	// 		                        ->first();

	// 		                        if(!$check_request){
	// 		                            $rider = [
	// 		                                'rider_id' => $value->id,
	// 		                                'order_id' => $sale->id,
	// 		                                'customer_id' => $sale->customer_id,
	// 		                                'warehouse_id' => $sale->warehouse_id,
	// 		                                'created_at' => now(),
	// 		                                'updated_at' => now()
	// 		                            ];

	// 		                            DB::table('customer_requests')->insert($rider);
	// 		                        }
	// 		                    }
	// 		                }else{
	// 		                	$check_request = CustomerRequest::where('rider_id', $value->id)->first();

	// 	                        if($check_request){
	// 	                        	CustomerRequest::where('rider_id', $value->id)->delete();
	// 	                        }
	// 		                }
	// 		            }
	//                 }

	//                 $data = [
    //                     'status' => true,
    //                     'message' => "send order request to all nearby riders successfully!"
    //                 ];

    //                 return response()->json($data, 201);
	//             }else{
	//             	$data = [
	//                     'status' => false,
	//                     'message' => "warehouse not found"
	//                 ];
	    
	//                 return response()->json($data, 404);
	//             }
    //         }else{
    //             $data = [
    //                 'status' => false,
    //                 'message' => "order type is not delivery"
    //             ];
    
    //             return response()->json($data, 422);
    //         }
    //     }else{
    //         $data = [
    //             'status' => false,
    //             'message' => "Sale not found"
    //         ];

    //         return response()->json($data, 404);
    //     }
    // }

    // public function orderToRider(Request $request, $rider_id)
    // {
    //     $rider_orders = CustomerRequest::where('rider_id', $rider_id)->get();

    //     if(!empty($rider_orders)){
    //         $order_detail = [];
    //         foreach ($rider_orders as $key => $value) {
    //         	$warehouse = Warehouse::find($value->warehouse_id);
    //         	$start_lat = $warehouse->latitude;
    //             $start_lng = $warehouse->longitude;

    //             $kilometers = 8;

    //         	$rider = User::find($value->rider_id);
    //             $end_lat = $rider->latitude;
    //             $end_lng = $rider->longitude;

    //         	$rider_distance = $this->distance($start_lat, $start_lng, $end_lat, $end_lng);
    //         	$rider_time = ($rider_distance / 60) * 60;
    //     		$rider_time = $rider_time + 0.5;

    //             $order = Sale::find($value->order_id);

    //             if($order){
    //                 if($order->latitude == '' && $order->longitude == ''){
    //                     $order->address = 'default address';
    //                     $order->latitude = '40.7480245';
    //                     $order->longitude = '-73.989467';
    //                 }

    //                 $order_distance = $this->distance($start_lat, $start_lng, $order->latitude, $order->longitude);
    //                 $order_time = ($order_distance / 60) * 60;
    //                 $order_time = $order_time + 0.5;

    //                 $customer = Customer::find($value->customer_id);
    //                 $order_detail[] = [
    //                     'pickup' => [
    //                         'distance' => number_format((float)$rider_distance, 1, '.', ''),
    //                         'time' => number_format((float)$rider_time, 1, '.', ''),
    //                         'restaurent' => $warehouse->name,
    //                         'address' => $warehouse->address
    //                     ],
    //                     'dropoff' => [
    //                         'distance' => number_format((float)$order_distance, 1, '.', ''),
    //                         'time' => number_format((float)$order_time, 1, '.', ''),
    //                         'username' => $customer->name,
    //                         'address' => $order->address
    //                     ],
    //                     'total_amount' => $order->grand_total
    //                 ];
    //             }
    //         }

    //         $total_order = $rider_orders->count();
    //         $rider_data[] = [
    //             'total_order' => $total_order,
    //             'acceptance_rate' => 0,
    //             'order_list' => $order_detail
    //         ];

    //         if(!empty($rider_data)){
    //             $data = [
    //                 'status' => true,
    //                 'message' => "view all new orders successfully!",
    //                 'data' => $rider_data
    //             ];

    //             return response()->json($data, 201);
    //         }else{
    //             $data = [
    //                 'status' => false,
    //                 'message' => "order list is empty"
    //             ];
    
    //             return response()->json($data, 404);
    //         }
    //     }
    // }

    // public function acceptOrder(Request $request)
    // {
    // 	$input = $request->all();

    // 	$validator = Validator::make($input, [
    //         'order_id' => 'required',
    //         'rider_id' => 'required'
    //     ]);
 
    //     if ($validator->fails()) {
    //     	$data = [
	//     		'status' => false,
	//     		'message' => $validator->errors()->first()
	//     	];

    //     	return response()->json($data, 403);
    //     }
    	
    //     $check_order = RiderOrder::where('order_id', $request->order_id)->first();

    //     if(!$check_order){
    //     	$customer_request = CustomerRequest::where('order_id', $request->order_id)
    //     	->where('rider_id', $request->rider_id)
    //     	->first();

    //     	if($customer_request){
    //     		$accept_order = new RiderOrder();
    //     		$accept_order->rider_id = $customer_request->rider_id;
    //     		$accept_order->order_id = $customer_request->order_id;
    //     		$accept_order->customer_id = $customer_request->customer_id;
    //     		$accept_order->warehouse_id = $customer_request->warehouse_id;
    //     		$accept_order->status = "accepted";
    //     		$accept_order->save();

    //     		if($accept_order){
    //     			$delete_request = CustomerRequest::where('order_id', $accept_order->order_id)->delete();

    //     			if($delete_request){
    //     				// $order_details = RiderOrder::with('rider', 'customer', 'order', 'warehouse')
    //     				// ->where('id', $accept_order->id)
    //     				// ->first();

    //     				$data = [
	// 		                'status' => true,
	// 		                'message' => "order acepted by rider successfully!"
	// 		                // 'data' => $order_details
	// 		            ];

	// 		            return response()->json($data, 201);
    //     			}else{
    //     				$data = [
	// 		                'status' => false,
	// 		                'message' => "something went wrong on deleting all other requests"
	// 		            ];

	// 		            return response()->json($data, 422);
    //     			}
    //     		}else{
    //     			$data = [
	// 	                'status' => false,
	// 	                'message' => "something went wrong on accepting order"
	// 	            ];

	// 	            return response()->json($data, 422);
    //     		}
    //     	}else{
    //     		$data = [
	//                 'status' => false,
	//                 'message' => "sorry order not found"
	//             ];

	//             return response()->json($data, 404);
    //     	}
    //     }else{
    //     	$data = [
    //             'status' => false,
    //             'message' => "sorry this order is already accepted by another rider"
    //         ];

    //         return response()->json($data, 422);
    //     }
    // }

    public function acceptOrder(Request $request)
    {
    	$input = $request->all();

    	$validator = Validator::make($input, [
            'order_id' => 'required',
            'rider_id' => 'required'
        ]);
 
        if ($validator->fails()) {
        	$data = [
	    		'status' => false,
	    		'message' => $validator->errors()->first()
	    	];

        	return response()->json($data, 403);
        }
    	
        $check_order = Order::where('id', $request->order_id)->where('rider_id', null)->first();

        if($check_order){
        	$customer_request = CustomerRequest::where('order_id', $request->order_id)
        	->where('rider_id', $request->rider_id)
        	->first();

        	if($customer_request){
        		$accept_order = Order::find($check_order->id);
        		$accept_order->rider_id = $request->rider_id;
                $accept_order->status = "accepted";
        		$accept_order->save();

        		if($accept_order){
        			$delete_request = CustomerRequest::where('order_id', $accept_order->id)->delete();

        			if($delete_request){
        				$data = [
			                'status' => true,
			                'message' => "order acepted by rider successfully!"
			            ];

			            return response()->json($data, 201);
        			}else{
        				$data = [
			                'status' => false,
			                'message' => "something went wrong on deleting all other requests"
			            ];

			            return response()->json($data, 422);
        			}
        		}else{
        			$data = [
		                'status' => false,
		                'message' => "something went wrong on accepting order"
		            ];

		            return response()->json($data, 422);
        		}
        	}else{
        		$data = [
	                'status' => false,
	                'message' => "sorry order not found"
	            ];

	            return response()->json($data, 404);
        	}
        }else{
        	$data = [
                'status' => false,
                'message' => "sorry this order is already accepted by another rider"
            ];

            return response()->json($data, 422);
        }
    }

    public function orderDetail(Request $request, $id)
    {
        // $sale = Sale::select('id', 'customer_id', 'warehouse_id', 'address', 'order_type', 'grand_total', 'customer_instruction')
        // ->with('SaleItems:id,sale_id,product_id','SaleItems.product:id,name,product_price','warehouse:id,name,phone,address','customer:id,name,phone')->withCount('SaleItems as total_items')->find($id);

        $order = Order::select('id', 'sale_id', 'user_id', 'warehouse_id', 'address', 'description', 'order_through', 'order_type', 'status')
        ->with('payment:id,payment_type,payment_status,total_amount', 'OrderItems:id,sale_id,product_id','OrderItems.product:id,name,product_price','warehouse:id,name,phone,address','customer:id,name,phone')->withCount('OrderItems as total_items')->find($id);

        if($order){
        	$data = [
    			'status' => true,
    			'message' => "view order detail successfully!",
    			"order" => $order
    		];

    		return response()->json($data, 200);
        }else{
        	$data = [
    			'status' => false,
    			'message' => "order not found"
    		];

    		return response()->json($data, 404);
    	}
    }

	// public function orderToRider(Request $request)
    // {
    //     $rider_orders = CustomerRequest::where('rider_id', $request->user()->id)->get();

    //     if(!empty($rider_orders)){
    //         $order_detail = [];
    //         foreach ($rider_orders as $key => $value) {
    //         	$warehouse = Warehouse::find($value->warehouse_id);
    //         	$start_lat = $warehouse->latitude;
    //             $start_lng = $warehouse->longitude;

    //             $kilometers = 8;

    //         	$rider = User::find($value->rider_id);
    //             $end_lat = $rider->latitude;
    //             $end_lng = $rider->longitude;

    //         	$rider_distance = $this->distance($start_lat, $start_lng, $end_lat, $end_lng);
    //         	$rider_time = ($rider_distance / 60) * 60;
    //     		$rider_time = $rider_time + 0.5;

    //             $order = Sale::find($value->order_id);

    //             if($order){
    //                 if($order->latitude == '' && $order->longitude == ''){
    //                     $order->address = 'default address';
    //                     $order->latitude = '40.7480245';
    //                     $order->longitude = '-73.989467';
    //                 }

    //                 $order_distance = $this->distance($start_lat, $start_lng, $order->latitude, $order->longitude);
    //                 $order_time = ($order_distance / 60) * 60;
    //                 $order_time = $order_time + 0.5;

    //                 $customer = Customer::find($value->customer_id);

    //                 $order_detail[] = [
    //                     'pickup' => [
    //                         'distance' => number_format((float)$rider_distance, 1, '.', ''),
    //                         'time' => number_format((float)$rider_time, 1, '.', ''),
    //                         'restaurent' => $warehouse->name,
    //                         'address' => $warehouse->address
    //                     ],
    //                     'dropoff' => [
    //                         'distance' => number_format((float)$order_distance, 1, '.', ''),
    //                         'time' => number_format((float)$order_time, 1, '.', ''),
    //                         'username' => $customer->name,
    //                         'address' => $order->address
    //                     ],
    //                     'total_amount' => $order->grand_total
    //                 ];

	// 				$data_total_order = CustomerRequest::where('rider_id', $value->rider_id)->count();
	// 				$data_order_detail = [
	// 					'total_order' => $data_total_order,
    //             		'acceptance_rate' => 0,
    //                     'pickup' => [
    //                         'distance' => number_format((float)$rider_distance, 1, '.', ''),
    //                         'time' => number_format((float)$rider_time, 1, '.', ''),
    //                         'restaurent' => $warehouse->name,
    //                         'address' => $warehouse->address
    //                     ],
    //                     'dropoff' => [
    //                         'distance' => number_format((float)$order_distance, 1, '.', ''),
    //                         'time' => number_format((float)$order_time, 1, '.', ''),
    //                         'username' => $customer->name,
    //                         'address' => $order->address
    //                     ],
    //                     'total_amount' => $order->grand_total
    //                 ];

	// 				event(new SearchEvent(json_encode($data_order_detail)));
    //             }
    //         }

    //         $total_order = $rider_orders->count();
    //         $rider_data[] = [
    //             'total_order' => $total_order,
    //             'acceptance_rate' => 0,
    //             'order_list' => $order_detail
    //         ];

    //         if(!empty($rider_data)){
    //             $data = [
    //                 'status' => true,
    //                 'message' => "view all new orders successfully!",
    //                 'data' => $rider_data
    //             ];

    //             return response()->json($data, 201);
    //         }else{
    //             $data = [
    //                 'status' => false,
    //                 'message' => "order list is empty"
    //             ];
    
    //             return response()->json($data, 404);
    //         }
    //     }
    // }

    // public function orderHistory(Request $request)
    // {
    //     if($request->get('status') != null || $request->get('status') != ''){
    //         $orders = RiderOrder::with(['order' => function($query){
    //             $query->select('id', 'customer_id', 'warehouse_id', 'address', 'order_type', 'grand_total', 'customer_instruction')
    //             ->with(['SaleItems' => function($q2){
    //                 $q2->select('id', 'sale_id', 'product_id')
    //                 ->with(['product' => function($q3){
    //                     $q3->select('id', 'name', 'product_price');
    //                 }]);
    //             }])
    //             ->with(['warehouse' => function($q2){
    //                 $q2->select('id', 'name', 'phone', 'address');
    //             }])
    //             ->with(['customer' => function($q2){
    //                 $q2->select('id', 'name', 'phone');
    //             }])
    //             ->withCount('SaleItems as total_items');
    //         }])
    //         ->where('rider_id', $request->user()->id)
    //         ->where('status', $request->get('status'))
    //         ->get();

    //         if(!$orders->isEmpty()){
    //             $data = [
    //                 'status' => true,
    //                 'message' => "view all ".$request->get('status')." orders successfully!",
    //                 "orders" => $orders
    //             ];

    //             return response()->json($data, 200);
    //         }else{
    //             $data = [
    //                 'status' => false,
    //                 'message' => $request->get('status')." orders list is empty"
    //             ];

    //             return response()->json($data, 404);
    //         }
    //     }else{
    //         $data = [
    //             'status' => false,
    //             'message' => "status pharam is not provide or is empty"
    //         ];

    //         return response()->json($data, 422);
    //     }
    // }

    // public function orderHistory(Request $request)
    // {
    //     if($request->get('status') != null || $request->get('status') != ''){
    //         $orders = Order::select('id', 'user_id', 'warehouse_id', 'sale_id', 'address', 'description', 'order_through', 'order_type', 'status')
    //         ->with(['OrderItems' => function($q2){
    //             $q2->select('id', 'sale_id', 'product_id')
    //             ->with(['product' => function($q3){
    //                 $q3->select('id', 'name', 'product_price');
    //             }]);
    //         }])
    //         ->with(['warehouse' => function($q2){
    //             $q2->select('id', 'name', 'phone', 'address');
    //         }])
    //         ->with(['customer' => function($q2){
    //             $q2->select('id', 'name', 'phone');
    //         }])
    //         ->withCount('OrderItems as total_items')
    //         ->where('rider_id', $request->user()->id)
    //         ->where('status', $request->get('status'))
    //         ->get();

    //         if(!$orders->isEmpty()){
    //             $data = [
    //                 'status' => true,
    //                 'message' => "view all ".$request->get('status')." orders successfully!",
    //                 "orders" => $orders
    //             ];

    //             return response()->json($data, 200);
    //         }else{
    //             $data = [
    //                 'status' => false,
    //                 'message' => $request->get('status')." orders list is empty"
    //             ];

    //             return response()->json($data, 404);
    //         }
    //     }else{
    //         $data = [
    //             'status' => false,
    //             'message' => "status pharam is not provide or is empty"
    //         ];

    //         return response()->json($data, 422);
    //     }
    // }

    public function orderHistory(Request $request)
    {
        if($request->get('status') != null || $request->get('status') != ''){
            $orders = Order::select('id', 'user_id', 'warehouse_id', 'sale_id', 'address', 'description', 'order_through', 'order_type', 'status')
            ->with(['OrderItems' => function($q2){
                $q2->select('id', 'sale_id', 'product_id')
                ->with(['product' => function($q3){
                    $q3->select('id', 'name', 'product_price');
                }]);
            }])
            ->with(['warehouse' => function($q2){
                $q2->select('id', 'name', 'phone', 'address');
            }])
            ->with(['customer' => function($q2){
                $q2->select('id', 'name', 'phone');
            }])
            ->withCount('OrderItems as total_items')
            ->where('rider_id', $request->user()->id)
            ->where('status', $request->get('status'));

            if($request->get('filter_by') != null || $request->get('filter_by') != ''){
                if($request->get('filter_by') == 'today'){
                    $orders = $orders->whereDate('created_at', Carbon::today());
                }
                else
                if($request->get('filter_by') == 'week'){
                    $orders = $orders->whereDate('created_at', '>=', Carbon::today()->subWeek());
                }
                else
                if($request->get('filter_by') == 'half month'){
                    $orders = $orders->whereDate('created_at', '>=', Carbon::today()->subDays(15));
                }
                else
                if($request->get('filter_by') == 'month'){
                    $orders = $orders->whereDate('created_at', '>=', Carbon::today()->subMonth());
                }
            }

            if($request->get('search_by_orderId') != null || $request->get('search_by_orderId') != ''){
                $orders = $orders->where('id', 'LIKE', '%' . $request->get('search_by_orderId') . '%');
            }

            $orders = $orders->get();

            if(!$orders->isEmpty()){
                $data = [
                    'status' => true,
                    'message' => "view all ".$request->get('status')." orders successfully!",
                    "orders" => $orders
                ];

                return response()->json($data, 200);
            }else{
                $data = [
                    'status' => false,
                    'message' => $request->get('status')." orders list is empty"
                ];

                return response()->json($data, 404);
            }
        }else{
            $data = [
                'status' => false,
                'message' => "status pharam is not provide or is empty"
            ];

            return response()->json($data, 422);
        }
    }

    // public function changeOrderStatus(Request $request)
    // {
    // 	$input = $request->all();

    // 	$validator = Validator::make($input, [
    //         'order_id' => 'required',
    //         'order_status' => 'required'
    //     ]);
 
    //     if ($validator->fails()) {
    //     	$data = [
	//     		'status' => false,
	//     		'message' => $validator->errors()->first()
	//     	];

    //     	return response()->json($data, 403);
    //     }

    //     $order = RiderOrder::where('order_id', $request->order_id)->where('rider_id', $request->user()->id)->first();

    // 	if($order){
    //         if($order->status == "completed"){
    //             $data = [
    //                 'status' => false,
    //                 'message' => "this order is already completed"
    //             ];
    
    //             return response()->json($data, 422);
    //         }
    //         else
    //         if($order->status == "cancelled"){
    //             $data = [
    //                 'status' => false,
    //                 'message' => "this order is already cancelled"
    //             ];
    
    //             return response()->json($data, 422);
    //         }
    //         else
    //         if($order->status == "refund"){
    //             $data = [
    //                 'status' => false,
    //                 'message' => "this order is already refunded"
    //             ];
    
    //             return response()->json($data, 422);
    //         }
    //         else{
    //             $update_status = RiderOrder::find($order->id);
    //             $update_status->status = $request->order_status;
    //             if($update_status->save()){
    //                 $get_order = RiderOrder::find($update_status->id);

    //                 if($get_order){
    //                     $data = [
    //                         'status' => true,
    //                         'message' => "Your order is now ".$request->order_status
    //                     ];
            
    //                     return response()->json($data, 201);
    //                 }else{
    //                     $data = [
    //                         'status' => false,
    //                         'message' => "Error accur on view order detail"
    //                     ];
            
    //                     return response()->json($data, 422);
    //                 }
    //             }else{
    //                 $data = [
    //                     'status' => false,
    //                     'message' => "Error accur on updating order status"
    //                 ];
        
    //                 return response()->json($data, 422);
    //             }
    //         }
	// 	}else{
	// 		$data = [
    //             'status' => false,
    //             'message' => "rider not found"
    //         ];

    //         return response()->json($data, 404);
	// 	}
	// }

    public function changeOrderStatus(Request $request)
    {
    	$input = $request->all();

    	$validator = Validator::make($input, [
            'order_id' => 'required',
            'order_status' => 'required'
        ]);
 
        if ($validator->fails()) {
        	$data = [
	    		'status' => false,
	    		'message' => $validator->errors()->first()
	    	];

        	return response()->json($data, 403);
        }

        $order = Order::where('id', $request->order_id)->where('rider_id', $request->user()->id)->first();

    	if($order){
            if($order->status == "completed"){
                $data = [
                    'status' => false,
                    'message' => "this order is already completed"
                ];
    
                return response()->json($data, 422);
            }
            else
            if($order->status == "cancelled"){
                $data = [
                    'status' => false,
                    'message' => "this order is already cancelled"
                ];
    
                return response()->json($data, 422);
            }
            else
            if($order->status == "refund"){
                $data = [
                    'status' => false,
                    'message' => "this order is already refunded"
                ];
    
                return response()->json($data, 422);
            }
            else{
                $update_status = Order::find($order->id);
                $update_status->status = $request->order_status;
                if($update_status->save()){
                    $get_order = Order::find($update_status->id);

                    if($get_order){
                        $data = [
                            'status' => true,
                            'message' => "Your order is now ".$request->order_status
                        ];
            
                        return response()->json($data, 201);
                    }else{
                        $data = [
                            'status' => false,
                            'message' => "Error accur on view order detail"
                        ];
            
                        return response()->json($data, 422);
                    }
                }else{
                    $data = [
                        'status' => false,
                        'message' => "Error accur on updating order status"
                    ];
        
                    return response()->json($data, 422);
                }
            }
		}else{
			$data = [
                'status' => false,
                'message' => "order not found"
            ];

            return response()->json($data, 404);
		}
	}

    public function riderLocation(Request $request)
    {
    	$input = $request->all();

    	$validator = Validator::make($input, [
            'latitude' => 'required',
            'longitude' => 'required'
        ]);
 
        if ($validator->fails()) {
        	$data = [
	    		'status' => false,
	    		'message' => $validator->errors()->first()
	    	];

        	return response()->json($data, 403);
        }

        $data = [
            'latitude' => $request->latitude,
            'longitude' => $request->longitude
        ];

        event(new LocationUpdated($request->user()->id, json_encode($data)));

        $data = [
            'status' => true,
            'message' => "Rider location updated successfully!"
        ];

        return response()->json($data, 201);

    	// if(!$rider){
        //     $get_rider = Order::with('warehouse')->where('rider_id', $request->user()->id)->where('status', 'accepted')->first();
        //     if($get_rider){
        //         $user = new TrackingRider();
        //         $user->rider_id = $request->user()->id;
        //         $user->start_lat = $get_rider->warehouse->latitude;
        //         $user->start_lng = $get_rider->warehouse->longitude;
        //         $user->current_lat = $request->latitude;
        //         $user->current_lng = $request->longitude;
        //         $user->end_lat = $request->latitude;
        //         $user->end_lng = $request->longitude;
        //         if($user->save()){
        //             $user_updated = User::find($request->user()->id);
        //             $data = [
        //                 'latitude' => $user_updated->latitude,
        //                 'longitude' => $user_updated->longitude
        //             ];
        //             // event(new LocationUpdated(json_encode($data)));
        //             $data = [
        //                 'status' => true,
        //                 'message' => "Rider location updated successfully!"
        //             ];
        
        //             return response()->json($data, 201);
        //         }else{
        //             $data = [
        //                 'status' => false,
        //                 'message' => "Error accur on updating rider location"
        //             ];
        
        //             return response()->json($data, 422);
        //         }
        //     }else{
        //         $data = [
        //             'status' => false,
        //             'message' => "Order not found"
        //         ];
    
        //         return response()->json($data, 404);
        //     }
		// }else{
        //     $user = TrackingRider::find($rider->id);
		// 	$user->current_lat = $request->latitude;
		// 	$user->current_lng = $request->longitude;
		// 	if($user->save()){
		// 		$user_updated = User::find($request->user()->id);
		// 		$data = [
		// 			'latitude' => $user_updated->latitude,
		// 			'longitude' => $user_updated->longitude
		// 		];
		// 		event(new LocationUpdated(json_encode($data)));
		// 		$data = [
		// 			'status' => true,
		// 			'message' => "Rider location updated successfully!"
		// 		];
	
		// 		return response()->json($data, 201);
		// 	}else{
		// 		$data = [
		// 			'status' => false,
		// 			'message' => "Error accur on updating rider location"
		// 		];
	
		// 		return response()->json($data, 422);
		// 	}
		// }
	}
}
