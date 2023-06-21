<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Table;
use App\Models\TableAvailability;

class TableAvailabilityAPIController extends Controller
{
    public function index(Request $request)
    {
        $lastCount=$request->last_count-1 ?? 0;
        $avail = TableAvailability::with('table')->whereHas('table', function($q) use($request){
            if($request->has('search')){
                $q->where('title', 'like', '%'.$request->search.'%');
            }
        })
        ->orderBy('created_at', 'desc')->skip($lastCount)->take(10)->get();

        if(!empty($avail)){
            $data = [
                'status' => true,
                'message' => "fetch all table availabilities successfully!",
                'data' => $avail
            ];

            return response()->json($data, 200);
        }else{
            $data = [
                'status' => false,
                'message' => "table availability not found"
            ];

            return response()->json($data, 404);
        }
    }

    public function store(Request $request, TableAvailability $avail)
    {
        $input = $request->all();

    	$validator = Validator::make($input, [
            'table_id' => 'required',
            'available_date' => 'required',
            'available_slot_from' => 'required',
            'available_slot_to' => 'required'
        ]);
 
        if ($validator->fails()) {
        	$data = [
	    		'status' => false,
	    		'message' => $validator->errors()->first()
	    	];

        	return response()->json($data, 403);
        }

        $table = Table::find($request->table_id);
        if($table){
            $add_avail = $avail->create($input);

            if($add_avail){
                $data = [
                    'status' => true,
                    'message' => "create table availability successfully!"
                ];

                return response()->json($data, 201);
            }else{
                $data = [
                    'status' => false,
                    'message' => "table availability not created"
                ];

                return response()->json($data, 422);
            }
        }else{
            $data = [
                'status' => false,
                'message' => "table not found"
            ];

            return response()->json($data, 404);
        }
    }

    public function update(Request $request, $id)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'table_id' => 'required',
            'available_date' => 'required',
            'available_slot_from' => 'required',
            'available_slot_to' => 'required'
        ]);
 
        if ($validator->fails()) {
        	$data = [
	    		'status' => false,
	    		'message' => $validator->errors()->first()
	    	];

        	return response()->json($data, 403);
        }

        $avail = TableAvailability::find($id);

        if($avail){
            $update_avail = $avail->update($input);

            if($update_avail){
                $data = [
                    'status' => true,
                    'message' => "update table availability successfully!"
                ];

                return response()->json($data, 201);
            }else{
                $data = [
                    'status' => false,
                    'message' => "table availability not updating"
                ];

                return response()->json($data, 422);
            }
        }else{
            $data = [
                'status' => false,
                'message' => "table availability not found"
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
        $avail = TableAvailability::find($id);

        if($avail){
            $delete_avail = $avail->delete();

            if($delete_avail){
                $data = [
                    'status' => true,
                    'message' => "delete table availability successfully!"
                ];

                return response()->json($data, 201);
            }else{
                $data = [
                    'status' => false,
                    'message' => "table availability not deleting"
                ];

                return response()->json($data, 422);
            }
        }else{
            $data = [
                'status' => false,
                'message' => "table availability not found"
            ];

            return response()->json($data, 404);
        }
    }
}
