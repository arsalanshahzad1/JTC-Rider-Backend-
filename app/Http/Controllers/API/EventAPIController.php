<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EventType;
use App\Models\Event;
use Illuminate\Support\Facades\Validator;

class EventAPIController extends Controller
{
    public function index(Request $request)
    {
        $lastCount=$request->last_count-1 ?? 0;
        $events = Event::with('event_type');
        
        if($request->has('search')){
            $events = $events->where('title', 'like', '%'.$request->search.'%');
        }

        $events = $events->orderBy('created_at', 'desc')->skip($lastCount)->take(10)->get();

        if(!empty($events)){
            $data = [
                'status' => true,
                'message' => "fetch all events successfully!",
                'data' => $events
            ];

            return response()->json($data, 200);
        }else{
            $data = [
                'status' => false,
                'message' => "events not found"
            ];

            return response()->json($data, 404);
        }
    }

    public function store(Request $request, Event $event)
    {
        $input = $request->all();

    	$validator = Validator::make($input, [
            'title' => 'required',
            'event_type_id' => 'required',
            'main_image' => 'required',
            'capacity' => 'required',
            'price' => 'required'
        ]);
 
        if ($validator->fails()) {
        	$data = [
	    		'status' => false,
	    		'message' => $validator->errors()->first()
	    	];

        	return response()->json($data, 403);
        }

        $event_type = EventType::find($request->event_type_id);
        if($event_type){

            $add_event = $event->create($input);
            if($request->hasFile('main_image')){
                $add_event->addMedia($request->main_image)->toMediaCollection(Event::PATH);
            }

            if($request->hasFile('gallery_images')){
                foreach ($request->file('gallery_images') as $image) {
                    $add_event->addMedia($image)->toMediaCollection(Event::PATH_GALLERY);
                }
            }

            if($add_event){
                $data = [
                    'status' => true,
                    'message' => "create event successfully!"
                ];

                return response()->json($data, 201);
            }else{
                $data = [
                    'status' => false,
                    'message' => "event not created"
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

    public function update(Request $request, $id)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'title' => 'required',
            'event_type_id' => 'required',
            'main_image' => 'required',
            'capacity' => 'required',
            'price' => 'required'
        ]);
 
        if ($validator->fails()) {
        	$data = [
	    		'status' => false,
	    		'message' => $validator->errors()->first()
	    	];

        	return response()->json($data, 403);
        }

        $event = Event::find($id);

        if($event){
            if($request->hasFile('main_image')){
                if(isset($event->media)){
                    foreach($event->media as $media){
                        if($media->collection_name == Event::PATH){
                            $media->delete();
                        }
                    }
                }

                $event->addMedia($request->main_image)->toMediaCollection(Event::PATH);
            }

            if($request->hasFile('gallery_images')){
                if(isset($event->media)){
                    foreach($event->media as $media){
                        if($media->collection_name == Event::PATH_GALLERY){
                            $media->delete();
                        }
                    }
                }

                foreach ($request->file('gallery_images') as $image) {
                    $event->addMedia($image)->toMediaCollection(Event::PATH_GALLERY);
                }
            }

            $update_event = $event->update($input);

            if($update_event){
                $data = [
                    'status' => true,
                    'message' => "update event successfully!"
                ];

                return response()->json($data, 201);
            }else{
                $data = [
                    'status' => false,
                    'message' => "event not updating"
                ];

                return response()->json($data, 422);
            }
        }else{
            $data = [
                'status' => false,
                'message' => "event not found"
            ];

            return response()->json($data, 404);
        }
    }

    public function destroy($id)
    {
        $event = Event::find($id);

        if($event){

            if(isset($event->media)){
                foreach($event->media as $media){
                    $media->delete();
                }
            }

            $delete_event = $event->delete();

            if($delete_event){
                $data = [
                    'status' => true,
                    'message' => "delete event successfully!"
                ];

                return response()->json($data, 201);
            }else{
                $data = [
                    'status' => false,
                    'message' => "event not deleting"
                ];

                return response()->json($data, 422);
            }
        }else{
            $data = [
                'status' => false,
                'message' => "event not found"
            ];

            return response()->json($data, 404);
        }
    }
}
