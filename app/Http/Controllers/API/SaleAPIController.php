<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\CreateSaleRequest;
use App\Http\Requests\UpdateSaleRequest;
use App\Http\Resources\SaleCollection;
use App\Http\Resources\SaleResource;
use App\Models\Customer;
use App\Models\Hold;
use App\Models\Sale;
use App\Models\Setting;
use App\Models\Warehouse;
use App\Repositories\SaleRepository;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

    /**
     * Class SaleAPIController
     */
    class SaleAPIController extends AppBaseController
    {
        /** @var saleRepository */
        private $saleRepository;

        public function __construct(SaleRepository $saleRepository)
        {
            $this->saleRepository = $saleRepository;
        }

        /**
         * @param  Request  $request
         * @return SaleCollection
         */
        public function index(Request $request)
        {
            $perPage = getPageSize($request);
            $search = $request->filter['search'] ?? '';
            $customer = (Customer::where('name', 'LIKE', "%$search%")->get()->count() != 0);
            $warehouse = (Warehouse::where('name', 'LIKE', "%$search%")->get()->count() != 0);

            $sales = $this->saleRepository;
            if ($customer || $warehouse) {
                $sales->whereHas('customer', function (Builder $q) use ($search, $customer) {
                    if ($customer) {
                        $q->where('name', 'LIKE', "%$search%");
                    }
                })->whereHas('warehouse', function (Builder $q) use ($search, $warehouse) {
                    if ($warehouse) {
                        $q->where('name', 'LIKE', "%$search%");
                    }
                });
            }

            if ($request->get('start_date') && $request->get('end_date')) {
                $sales->whereBetween('date', [$request->get('start_date'), $request->get('end_date')]);
            }

            if ($request->get('warehouse_id')) {
                $sales->where('warehouse_id', $request->get('warehouse_id'));
            }

            if ($request->get('customer_id')) {
                $sales->where('customer_id', $request->get('customer_id'));
            }

            if ($request->get('status') && $request->get('status') != 'null') {
                $sales->Where('status', $request->get('status'));
            }

            if ($request->get('payment_status') && $request->get('payment_status') != 'null') {
                $sales->where('payment_status', $request->get('payment_status'));
            }

            if ($request->get('payment_type') && $request->get('payment_type') != 'null') {
                $sales->where('payment_type', $request->get('payment_type'));
            }

            $sales = $sales->paginate($perPage);

            SaleResource::usingWithCollection();

            return new SaleCollection($sales);
        }


        /**
         * @param Request $request
         * @return SaleCollection
         */
        public function kitchenIndex(Request $request)
        {
            $perPage = getPageSize($request);
            $search = $request->filter['search'] ?? '';
            $customer = (Customer::where('name', 'LIKE', "%$search%")->get()->count() != 0);
            $warehouse = (Warehouse::where('name', 'LIKE', "%$search%")->get()->count() != 0);

            $sales = $this->saleRepository;
            if ($customer || $warehouse) {
                $sales->whereHas('customer', function (Builder $q) use ($search, $customer) {
                    if ($customer) {
                        $q->where('name', 'LIKE', "%$search%");
                    }
                })->whereHas('warehouse', function (Builder $q) use ($search, $warehouse) {
                    if ($warehouse) {
                        $q->where('name', 'LIKE', "%$search%");
                    }
                });
            }

            if ($request->get('start_date') && $request->get('end_date')) {
                $sales->whereBetween('date', [$request->get('start_date'), $request->get('end_date')]);
            }

            if ($request->get('warehouse_id')) {
                $sales->where('warehouse_id', $request->get('warehouse_id'));
            }

            if ($request->get('customer_id')) {
                $sales->where('customer_id', $request->get('customer_id'));
            }

            if ($request->get('status') && $request->get('status') != 'null') {
                $sales->Where('status', $request->get('status'));
            }

            if ($request->get('payment_status') && $request->get('payment_status') != 'null') {
                $sales->where('payment_status', $request->get('payment_status'));
            }

            if ($request->get('payment_type') && $request->get('payment_type') != 'null') {
                $sales->where('payment_type', $request->get('payment_type'));
            }

            $sales = $sales->paginate($perPage);

            SaleResource::usingWithCollection();

            return new SaleCollection($sales);
        }

        /**
         * @param  CreateSaleRequest  $request
         * @return SaleResource
         */
        public function store(CreateSaleRequest $request)
        {
            if (isset($request->hold_ref_no)) {
                $holdExist = Hold::whereReferenceCode($request->hold_ref_no)->first();
                if (! empty($holdExist)) {
                    $holdExist->delete();
                }
            }
            $input = $request->all();
            $sale = $this->saleRepository->storeSale($input);

            return new SaleResource($sale);
        }

        /**
         * @param $id
         * @return SaleResource
         */
        public function show($id)
        {
            $sale = $this->saleRepository->find($id);

            return new SaleResource($sale);
        }

        /**
         * @param  Sale  $sale
         * @return SaleResource
         */
        public function edit(Sale $sale)
        {
            $sale = $sale->load('saleItems.product.stocks', 'warehouse');

            return new SaleResource($sale);
        }

        /**
         * @param  UpdateSaleRequest  $request
         * @param $id
         * @return SaleResource
         */
        public function update(UpdateSaleRequest $request, $id)
        {
            $input = $request->all();
            $sale = $this->saleRepository->updateSale($input, $id);

            return new SaleResource($sale);
        }

        /**
         * @param $id
         * @return JsonResponse
         */
        public function destroy($id)
        {
            try {
                DB::beginTransaction();
                $sale = $this->saleRepository->with('saleItems')->where('id', $id)->first();
                foreach ($sale->saleItems as $saleItem) {
                    manageStock($sale->warehouse_id, $saleItem['product_id'], $saleItem['quantity']);
                }
                if (File::exists(Storage::path('sales/barcode-'.$sale->reference_code.'.png'))) {
                    File::delete(Storage::path('sales/barcode-'.$sale->reference_code.'.png'));
                }
                $this->saleRepository->delete($id);
                DB::commit();

                return $this->sendSuccess('Sale Deleted successfully');
            } catch (Exception $e) {
                DB::rollBack();
                throw new UnprocessableEntityHttpException($e->getMessage());
            }
        }

        /**
         * @param  Sale  $sale
         * @return JsonResponse
         *
         * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist
         * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig
         */
        public function pdfDownload(Sale $sale): JsonResponse
        {
            $sale = $sale->load('customer', 'saleItems.product', 'payments');
            $data = [];
            if (Storage::exists('pdf/Sale-'.$sale->reference_code.'.pdf')) {
                Storage::delete('pdf/Sale-'.$sale->reference_code.'.pdf');
            }
            $companyLogo = getLogoUrl();
            $pdf = PDF::loadView('pdf.sale-pdf', compact('sale', 'companyLogo'))->setOptions([
                'tempDir' => public_path(),
                'chroot' => public_path(),
            ]);
            Storage::disk(config('app.media_disc'))->put('pdf/Sale-'.$sale->reference_code.'.pdf', $pdf->output());
            $data['sale_pdf_url'] = Storage::url('pdf/Sale-'.$sale->reference_code.'.pdf');

            return $this->sendResponse($data, 'pdf retrieved Successfully');
        }

        /**
         * @param  Sale  $sale
         * @return JsonResponse
         */
        public function saleInfo(Sale $sale)
        {
            $sale = $sale->load('saleItems.product', 'warehouse', 'customer');
            $keyName = [
                'email', 'company_name', 'phone', 'address',
            ];
            $sale['company_info'] = Setting::whereIn('key', $keyName)->pluck('value', 'key')->toArray();

            return $this->sendResponse($sale, 'Sale information retrieved successfully');
        }

        /**
         * @param  Request  $request
         * @return SaleCollection
         */
        public function getSaleProductReport(Request $request): SaleCollection
        {
            $perPage = getPageSize($request);
            $productId = $request->get('product_id');
            $sales = $this->saleRepository->whereHas('saleItems', function ($q) use ($productId) {
                $q->where('product_id', '=', $productId);
            })->with(['saleItems.product', 'customer']);

            $sales = $sales->paginate($perPage);

            SaleResource::usingWithCollection();

            return new SaleCollection($sales);
        }

        // public function distance($lat1, $lon1, $lat2, $lon2) {
        //     $theta = $lon1 - $lon2;
        //     $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        //     $dist = acos($dist);
        //     $dist = rad2deg($dist);
        //     $miles = $dist * 60 * 1.1515;
        //     $kilometers = $miles * 1.60934;
        //     return $kilometers;
        // }

        // public function riderDistance($lat1, $lon1, $lat2, $lon2) {
        //     $theta = $lon1 - $lon2;
        //     $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        //     $dist = acos($dist);
        //     $dist = rad2deg($dist);
        //     $miles = $dist * 60 * 1.1515;
        //     $kilometers = $miles * 1.60934;
        //     return $kilometers;
        // }

        // public function saleToRiders(Request $request, $sale_id)
        // {
        //     $sale = Sale::find($sale_id);

        //     if($sale){
        //         if($sale->order_type == "delivery"){

        //             $warehouse = Warehouse::find($sale->warehouse_id);

        //             if($warehouse){
        //                 $start_lat = $warehouse->latitude;
        //                 $start_lng = $warehouse->longitude;

        //                 $kilometers = 8;

        //                 $riderRole = Role::where('name', 'rider')->first();
        //                 $users = $riderRole->users()->where('status', 1)->get();

        //                 $datas = [];
        //                 $flag = false;

        //                 foreach ($users as $key => $value) {
        //                     $check_order = RiderOrder::where('order_id', $sale->id)->first();

        //                     if(!$check_order){
        //                         $end_lat = $value->latitude;
        //                         $end_lng = $value->longitude;

        //                         $rider_distance = $this->distance($start_lat, $start_lng, $end_lat, $end_lng);

        //                         if($rider_distance <= $kilometers){
        //                             $rider_time = ($rider_distance / 60) * 60;
        //                             $rider_time = $rider_time + 0.5;
        //                             // notification send to specific rider one by one

        //                             $check_request = CustomerRequest::where('rider_id', $value->id)
        //                             ->where('order_id', $sale->id)
        //                             ->first();

        //                             if(!$check_request){
        //                                 $rider = [
        //                                     'rider_id' => $value->id,
        //                                     'order_id' => $sale->id,
        //                                     'customer_id' => $sale->customer_id,
        //                                     'warehouse_id' => $sale->warehouse_id,
        //                                     'created_at' => now(),
        //                                     'updated_at' => now()
        //                                 ];

        //                                 DB::table('customer_requests')->insert($rider);
        //                             }

        //                             $rider_orders = CustomerRequest::where('rider_id', $value->id)->count();

        //                             // $customerRole = Role::where('name', 'rider')->first();
        //                             // $users = $riderRole->users()->where('status', 1)->get();

        //                             // $rider_distanceCalculate = $this->riderDistance($start_lat, $start_lng, $end_lat, $end_lng);

        //                             $datas[] = [
        //                                 'total_order' => $rider_orders,
        //                                 'acceptance_rate' => 0,
        //                                 'total_amount' => $sale->grand_total,

        //                                 'pickup' => [
        //                                     'distance' => (float)number_format((float)$rider_distance, 1, '.', ''),
        //                                     'time' => (float)number_format((float)$rider_time, 1, '.', ''),
        //                                     'restaurent' => $warehouse->name,
        //                                     'address' => $warehouse->address
        //                                 ],

        //                                 'dropoff' => [
        //                                     'distance' => 1.1,
        //                                     'time' => 2,
        //                                     'address' => '34 Street New York, USA'
        //                                 ]
        //                             ];

        //                             // event(new SearchEvent($data));
        //                         }
        //                     }

        //                     $flag = true;
        //                 }

        //                 if($flag == true){
        //                     $upcoming_orders = CustomerRequest::get();

        //                     if(!empty($upcoming_orders)){
        //                         foreach ($upcoming_orders as $key => $value) {
                                    
        //                         }

        //                         $data = [
        //                             'status' => true,
        //                             'message' => "send order request to all nearby riders successfully!",
        //                             'data' => $datas
        //                         ];

        //                         return response()->json($data, 201);
        //                     }
        //                 }else{
        //                     $data = [
        //                         'status' => false,
        //                         'message' => "riders are not available right now"
        //                     ];
                
        //                     return response()->json($data, 404);
        //                 }
        //             }else{
        //                 $data = [
        //                     'status' => false,
        //                     'message' => "Warehouse not found"
        //                 ];
            
        //                 return response()->json($data, 404);
        //             }
        //         }else{
        //             $data = [
        //                 'status' => false,
        //                 'message' => "this sale type is not drive through"
        //             ];
        
        //             return response()->json($data, 404);
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

        //             $riderRole = Role::where('name', 'rider')->first();
        //             $users = $riderRole->users()->where('status', 1)->get();

        //             foreach ($users as $key => $value) {
        //                 $check_order = RiderOrder::where('order_id', $sale->id)->first();

        //                 if(!$check_order){
        //                     $check_request = CustomerRequest::where('rider_id', $value->id)
        //                     ->where('order_id', $sale->id)
        //                     ->first();

        //                     if(!$check_request){
        //                         $rider = [
        //                             'rider_id' => $value->id,
        //                             'order_id' => $sale->id,
        //                             'customer_id' => $sale->customer_id,
        //                             'warehouse_id' => $sale->warehouse_id,
        //                             'created_at' => now(),
        //                             'updated_at' => now()
        //                         ];

        //                         DB::table('customer_requests')->insert($rider);
        //                     }
        //                 }
        //             }

        //             $upcoming_orders = CustomerRequest::get();

        //             if(!empty($upcoming_orders)){

        //                 $rider_arr = [];
        //                 foreach ($upcoming_orders as $key => $value) {
        //                     $warehouse = Warehouse::find($value->warehouse_id);

        //                     if($warehouse){
        //                         $start_lat = $warehouse->latitude;
        //                         $start_lng = $warehouse->longitude;

        //                         $kilometers = 8;

        //                         $rider = User::find($value->rider_id);

        //                         if($rider){
        //                             $end_lat = $rider->latitude;
        //                             $end_lng = $rider->longitude;

        //                             $rider_distance = $this->distance($start_lat, $start_lng, $end_lat, $end_lng);

        //                             if($rider_distance <= $kilometers){
        //                                 $rider_time = ($rider_distance / 60) * 60;
        //                                 $rider_time = $rider_time + 0.5;

        //                                 $riders = CustomerRequest::where('rider_id', $rider->id)->get();

        //                                 if(!empty($riders)){
        //                                     foreach ($riders as $key1 => $value1) {
        //                                         $order = Sale::find($value->order_id);
        //                                         if($order){
        //                                             $order_detail = [
        //                                                 'pickup' => [
        //                                                     'distance' => (float)number_format((float)$rider_distance, 1, '.', ''),
        //                                                     'time' => (float)number_format((float)$rider_time, 1, '.', ''),
        //                                                     'restaurent' => $warehouse->name,
        //                                                     'address' => $warehouse->address
        //                                                 ],
        //                                                 'dropoff' => [
        //                                                     'distance' => 1.1,
        //                                                     'time' => 2,
        //                                                     'address' => '34 Street New York, USA'
        //                                                 ],
        //                                                 'total_amount' => $order->grand_total
        //                                             ];
        //                                         }
        //                                     }

        //                                     $total_order = $riders->count();
        //                                     $rider_data = [
        //                                         'total_order' => $total_order,
        //                                         'acceptance_rate' => 0
        //                                     ];
        //                                 }
        //                             }
        //                         }
        //                     }
        //                 }

        //                 return $datas;
        //             }
        //         }else{
        //             $data = [
        //                 'status' => false,
        //                 'message' => "order type is not delivery"
        //             ];
        
        //             return response()->json($data, 404);
        //         }
        //     }else{
        //         $data = [
        //             'status' => false,
        //             'message' => "Sale not found"
        //         ];
    
        //         return response()->json($data, 404);
        //     }
        // }
    }
