<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateProductCategoryRequest;
use App\Http\Requests\UpdateProductCategoryRequest;
use App\Http\Resources\ProductCategoryCollection;
use App\Http\Resources\ProductCategoryResource;
use App\Models\ProductCategory;
use App\Models\ProductSubCategory;
use App\Repositories\ProductCategoryRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductSubCategoryAPIController extends Controller
{
    public function index(Request $request)
    {
        $lastCount=$request->last_count-1 ?? 0;
        $productSubCategory = ProductSubCategory::with('category');
        
        if($request->has('search')){
            $productSubCategory = $productSubCategory->where('name', 'like', '%'.$request->search.'%');
        }

        $productSubCategory = $productSubCategory->orderBy('created_at', 'desc')->skip($lastCount)->take(10)->get();

        if(!empty($productSubCategory)){
            $data = [
                'status' => true,
                'message' => "fetch all product sub categories successfully!",
                'data' => $productSubCategory
            ];

            return response()->json($data, 200);
        }else{
            $data = [
                'status' => false,
                'message' => "product sub categories not found"
            ];

            return response()->json($data, 404);
        }
    }

    public function store(Request $request, ProductSubCategory $sub_category)
    {
        $input = $request->all();

    	$validator = Validator::make($input, [
            'name' => 'required',
            'product_category_id' => 'required'
        ]);
 
        if ($validator->fails()) {
        	$data = [
	    		'status' => false,
	    		'message' => $validator->errors()->first()
	    	];

        	return response()->json($data, 403);
        }

        $check_category = ProductCategory::find($request->product_category_id);
        if($check_category){
            $productCategory = $sub_category->create($input);

            if($productCategory){
                $data = [
                    'status' => true,
                    'message' => "create sub category successfully!"
                ];

                return response()->json($data, 201);
            }else{
                $data = [
                    'status' => false,
                    'message' => "product sub categories not created"
                ];

                return response()->json($data, 422);
            }
        }else{
            $data = [
                'status' => false,
                'message' => "product categories not found"
            ];

            return response()->json($data, 404);
        }
    }

    public function update(Request $request, $id)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => 'required',
            'product_category_id' => 'required'
        ]);
 
        if ($validator->fails()) {
        	$data = [
	    		'status' => false,
	    		'message' => $validator->errors()->first()
	    	];

        	return response()->json($data, 403);
        }

        $subcategory = ProductSubCategory::find($id);

        if($subcategory){
            $productSubCategory = $subcategory->update($input);

            if($productSubCategory){
                $data = [
                    'status' => true,
                    'message' => "update sub category successfully!"
                ];

                return response()->json($data, 201);
            }else{
                $data = [
                    'status' => false,
                    'message' => "product sub categories not updating"
                ];

                return response()->json($data, 422);
            }
        }else{
            $data = [
                'status' => false,
                'message' => "product sub categories not found"
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
        $subcategory = ProductSubCategory::find($id);

        if($subcategory){
            $productSubCategory = $subcategory->delete();

            if($productSubCategory){
                $data = [
                    'status' => true,
                    'message' => "delete sub category successfully!"
                ];

                return response()->json($data, 201);
            }else{
                $data = [
                    'status' => false,
                    'message' => "product sub categories not deleting"
                ];

                return response()->json($data, 422);
            }
        }else{
            $data = [
                'status' => false,
                'message' => "product sub categories not found"
            ];

            return response()->json($data, 404);
        }
    }
}
