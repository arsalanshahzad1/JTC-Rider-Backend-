<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TableType;
use Illuminate\Support\Facades\Validator;

class TableTypeAPIController extends Controller
{
    public function index(Request $request)
    {
        $lastCount=$request->last_count-1 ?? 0;
        $tableTypes = TableType::orderBy('created_at', 'desc');
        
        if($request->has('search')){
            $tableTypes = $tableTypes->where('title', 'like', '%'.$request->search.'%');
        }

        $tableTypes = $tableTypes->skip($lastCount)->take(10)->get();

        if(!empty($tableTypes)){
            $data = [
                'status' => true,
                'message' => "fetch all table types successfully!",
                'data' => $tableTypes
            ];

            return response()->json($data, 200);
        }else{
            $data = [
                'status' => false,
                'message' => "table types not found"
            ];

            return response()->json($data, 404);
        }
    }

    public function store(Request $request, TableType $table_type)
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

        $tableType = $table_type->create($input);

        if($tableType){
            $data = [
                'status' => true,
                'message' => "create table type successfully!"
            ];

            return response()->json($data, 201);
        }else{
            $data = [
                'status' => false,
                'message' => "table type not created"
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

        $tableType = TableType::find($id);

        if($tableType){
            $update_table_type = $tableType->update($input);

            if($update_table_type){
                $data = [
                    'status' => true,
                    'message' => "update table type successfully!"
                ];

                return response()->json($data, 201);
            }else{
                $data = [
                    'status' => false,
                    'message' => "table type not updating"
                ];

                return response()->json($data, 422);
            }
        }else{
            $data = [
                'status' => false,
                'message' => "table type not found"
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
        $tableType = TableType::find($id);

        if($tableType){
            $delete_table_type = $tableType->delete();

            if($delete_table_type){
                $data = [
                    'status' => true,
                    'message' => "delete table type successfully!"
                ];

                return response()->json($data, 201);
            }else{
                $data = [
                    'status' => false,
                    'message' => "table type not deleting"
                ];

                return response()->json($data, 422);
            }
        }else{
            $data = [
                'status' => false,
                'message' => "table type not found"
            ];

            return response()->json($data, 404);
        }
    }
}
