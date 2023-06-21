<?php

namespace App\Http\Controllers\API\Rider;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\RiderDocument;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

use Event;
use App\Events\TestEvent;

use App\Events\LocationUpdated;

class DocumentController extends Controller
{
    public function addDocuments(Request $request)
    {
    	$input = $request->all();

    	$validator = Validator::make($input, [
            'first_name' => 'required',
            'phone' => 'required',
            'documents' => 'required'
        ]);
 
        if ($validator->fails()) {
        	$data = [
	    		'status' => false,
	    		'message' => $validator->errors()->first()
	    	];

        	return response()->json($data, 403);
        }

        $add_document = new RiderDocument();
    	$add_document->first_name = $input['first_name'];

		if($request->has('last_name')){
			$add_document->last_name = $input['last_name'];
		}

		$add_document->phone = $input['phone'];

		if($request->has('email')){
			$add_document->email = $input['email'];
		}

		if($request->hasFile('documents')){
			$arr = [];

			foreach ($input['documents'] as $key => $value) {
				$imageName = 'document_'.uniqid().time().'.'.$value->extension();  
	    		$value->move(public_path('images/rider/documents'), $imageName);

	    		$fileName = '/images/rider/documents/'.$imageName;

	    		array_push($arr, $fileName);
			}

			$add_document->documents = json_encode($arr);
		}

		if($add_document->save()){
    		$data = [
	    		'status' => true,
	    		'message' => "rider documents added successfully!"
	    	];

	    	return response()->json($data, 201);
    	}else{
    		$data = [
	    		'status' => false,
	    		'message' => "Error accur on adding rider documents"
	    	];

	    	return response()->json($data, 422);
    	}
    }

    public function viewDocumentList(Request $request)
    {
        $documents = RiderDocument::latest()->get();

        if(!empty($documents)){
        	$data = [
    			'status' => true,
    			'message' => "rider documents list show successfully!",
    			"user" => $documents
    		];

    		return response()->json($data, 200);
        }else{
        	$data = [
    			'status' => false,
    			'message' => "rider documents list are empty"
    		];

    		return response()->json($data, 404);
    	}
    }

    public function index()
    {
        // Event::dispatch(new TestEvent(7));
        $userId = 2;
        $latitude = "12345";
        $longitude = "54321";

        event(new LocationUpdated($userId, $latitude, $longitude));
        return response()->json("success", 200);
    }
}
