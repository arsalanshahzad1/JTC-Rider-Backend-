<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EventType;
use Illuminate\Support\Facades\Validator;

class EventTypeAPIController extends Controller
{
    public function index(Request $request)
    {
        $lastCount=$request->last_count-1 ?? 0;
        $eventTypes = EventType::orderBy('created_at', 'desc');
        
        if($request->has('search')){
            $eventTypes = $eventTypes->where('title', 'like', '%'.$request->search.'%');
        }

        $eventTypes = $eventTypes->skip($lastCount)->take(10)->get();

        if(!empty($eventTypes)){
            $data = [
                'status' => true,
                'message' => "fetch all event types successfully!",
                'data' => $eventTypes
            ];

            return response()->json($data, 200);
        }else{
            $data = [
                'status' => false,
                'message' => "event types not found"
            ];

            return response()->json($data, 404);
        }
    }

    public function store(Request $request, EventType $event_type)
    {
        $input = $request->all();

    	$validator = Validator::make($input, [
            'title' => 'required'
        ]);
 
        if ($validator->fails()) {
        	$data = [
	    		'status' => false,
	    		'message' => $validator->errors()->first()
	    	];

        	return response()->json($data, 403);
        }

        $eventType = $event_type->create($input);

        if($eventType){
            $data = [
                'status' => true,
                'message' => "create event type successfully!"
            ];

            return response()->json($data, 201);
        }else{
            $data = [
                'status' => false,
                'message' => "event type not created"
            ];

            return response()->json($data, 422);
        }
    }

    public function update(Request $request, $id)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'title' => 'required'
        ]);
 
        if ($validator->fails()) {
        	$data = [
	    		'status' => false,
	    		'message' => $validator->errors()->first()
	    	];

        	return response()->json($data, 403);
        }

        $eventType = EventType::find($id);

        if($eventType){
            $update_event_type = $eventType->update($input);

            if($update_event_type){
                $data = [
                    'status' => true,
                    'message' => "update event type successfully!"
                ];

                return response()->json($data, 201);
            }else{
                $data = [
                    'status' => false,
                    'message' => "event type not updating"
                ];

                return response()->json($data, 422);
            }
        }else{
            $data = [
                'status' => false,
                'message' => "event type not found"
            ];

            return response()->json($data, 404);
        }
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        $eventType = EventType::find($id);

        if($eventType){
            $delete_event_type = $eventType->delete();

            if($delete_event_type){
                $data = [
                    'status' => true,
                    'message' => "delete event type successfully!"
                ];

                return response()->json($data, 201);
            }else{
                $data = [
                    'status' => false,
                    'message' => "event type not deleting"
                ];

                return response()->json($data, 422);
            }
        }else{
            $data = [
                'status' => false,
                'message' => "event type not found"
            ];

            return response()->json($data, 404);
        }
    }
}
