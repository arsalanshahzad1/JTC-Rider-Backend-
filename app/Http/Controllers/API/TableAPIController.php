<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\TableType;
use App\Models\Table;

class TableAPIController extends Controller
{
    public function index(Request $request)
    {
        $lastCount=$request->last_count-1 ?? 0;
        $table = Table::with('table_type');
        
        if($request->has('search')){
            $table = $table->where('title', 'like', '%'.$request->search.'%');
        }

        $table = $table->orderBy('created_at', 'desc')->skip($lastCount)->take(10)->get();

        if(!empty($table)){
            $data = [
                'status' => true,
                'message' => "fetch all table successfully!",
                'data' => $table
            ];

            return response()->json($data, 200);
        }else{
            $data = [
                'status' => false,
                'message' => "table not found"
            ];

            return response()->json($data, 404);
        }
    }

    public function store(Request $request, Table $table)
    {
        $input = $request->all();

    	$validator = Validator::make($input, [
            'title' => 'required',
            'table_type_id' => 'required'
        ]);
 
        if ($validator->fails()) {
        	$data = [
	    		'status' => false,
	    		'message' => $validator->errors()->first()
	    	];

        	return response()->json($data, 403);
        }

        $table_type = TableType::find($request->table_type_id);
        if($table_type){
            $add_table = $table->create($input);

            if($add_table){
                $data = [
                    'status' => true,
                    'message' => "create table successfully!"
                ];

                return response()->json($data, 201);
            }else{
                $data = [
                    'status' => false,
                    'message' => "table not created"
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

    public function update(Request $request, $id)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'title' => 'required',
            'table_type_id' => 'required'
        ]);
 
        if ($validator->fails()) {
        	$data = [
	    		'status' => false,
	    		'message' => $validator->errors()->first()
	    	];

        	return response()->json($data, 403);
        }

        $table = Table::find($id);

        if($table){
            $update_table = $table->update($input);

            if($update_table){
                $data = [
                    'status' => true,
                    'message' => "update table successfully!"
                ];

                return response()->json($data, 201);
            }else{
                $data = [
                    'status' => false,
                    'message' => "table not updating"
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

    /**
     * @param $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        $table = Table::find($id);

        if($table){
            $delete_table = $table->delete();

            if($delete_table){
                $data = [
                    'status' => true,
                    'message' => "delete table successfully!"
                ];

                return response()->json($data, 201);
            }else{
                $data = [
                    'status' => false,
                    'message' => "table not deleting"
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
}
