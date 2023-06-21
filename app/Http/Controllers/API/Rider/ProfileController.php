<?php

namespace App\Http\Controllers\API\Rider;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use DB;

use App\Events\LocationUpdated;
use App\Events\TestEvent;

class ProfileController extends Controller
{
    public function viewProfile(Request $request)
    {
        $user = User::where('id', $request->user()->id)->first();

        if($user){
        	$data = [
    			'status' => true,
    			'message' => "rider show successfully!",
    			"user" => $user
    		];

    		return response()->json($data, 200);
        }else{
        	$data = [
    			'status' => false,
    			'message' => "rider not found"
    		];

    		return response()->json($data, 404);
    	}
    }

    public function updateProfile(Request $request)
    {
    	$input = $request->all();

    	$validator = Validator::make($input, [
            'first_name' => 'required',
            'phone' => 'required',
            'image' => 'image|mimes:jpg,png,jpeg,gif,svg|max:2048',
        ]);
 
        if ($validator->fails()) {
        	$data = [
	    		'status' => false,
	    		'message' => $validator->errors()->first()
	    	];

        	return response()->json($data, 403);
        }

        $user = User::find($request->user()->id);

    	if($user){
    		if($request->has('first_name')){
    			$user->first_name = $input['first_name'];
    		}

    		if($request->has('last_name')){
    			$user->last_name = $input['last_name'];
    		}

    		if($request->hasFile('image')){
    			$imageName = 'rider_'.uniqid().time().'.'.$request->image->extension();  
        		$request->image->move(public_path('images/rider'), $imageName);

        		$fileName = '/images/rider/'.$imageName;

        		$user->image = $fileName;
    		}

    		if($request->has('phone')){
    			$user->phone = $input['phone'];
    		}

    		if($request->has('language')){
    			$user->language = $input['language'];
    		}

    		if($user->save()){
    			$user = User::where('id', $request->user()->id)->first();
	    		$data = [
		    		'status' => true,
		    		'message' => "rider profile updated successfully!",
		    		'user' => $user
		    	];

		    	return response()->json($data, 201);
	    	}else{
	    		$data = [
		    		'status' => false,
		    		'message' => "Error accur on updating rider profile"
		    	];

		    	return response()->json($data, 422);
	    	}
    	}else{
    		$data = [
	    		'status' => false,
	    		'message' => "Rider not found"
	    	];

	    	return response()->json($data, 404);
    	}
    }

	public function updateLocation(Request $request)
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

        $user = User::find($request->user()->id);

    	if($user){
			$user->latitude = $request->latitude;
			$user->longitude = $request->longitude;
			if($user->save()){
				$user_updated = User::find($request->user()->id);
				$data = [
					'latitude' => $user_updated->latitude,
					'longitude' => $user_updated->longitude
				];
				event(new LocationUpdated(json_encode($data)));
				$data = [
					'status' => true,
					'message' => "Rider location updated successfully!"
				];
	
				return response()->json($data, 201);
			}else{
				$data = [
					'status' => false,
					'message' => "Error accur on updating rider location"
				];
	
				return response()->json($data, 422);
			}
		}else{
			$data = [
                'status' => false,
                'message' => "rider not found"
            ];

            return response()->json($data, 404);
		}
	}

	public function fireEvent(Request $request)
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
			'user_id' => $request->user()->id,
			'latitude' => $request->latitude,
			'longitude' => $request->longitude
		];

		event(new TestEvent(json_encode($data)));

		return response()->json("success", 201);
	}
}
