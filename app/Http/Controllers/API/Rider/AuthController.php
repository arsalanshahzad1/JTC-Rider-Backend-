<?php

namespace App\Http\Controllers\Api\Rider;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Mail\OtpMail;
use Illuminate\Support\Facades\Mail;
use App\Models\UserLog;
use Illuminate\Support\Facades\Http;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

use App\Events\LocationUpdated;

class AuthController extends Controller
{
	public function register(Request $request)
    {
    	$input = $request->all();

    	$validator = Validator::make($input, [
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'first_name' => 'required',
            'phone' => 'required'
        ]);
 
        if ($validator->fails()) {
        	$data = [
	    		'status' => false,
	    		'message' => $validator->errors()->first()
	    	];

        	return response()->json($data, 403);
        }

        $first_name = $input['first_name'];

        if($request->has('last_name')){
			$last_name = $input['last_name'];
		}else{
			$last_name = null;
		}

        $phone = $input['phone'];

		$email = $input['email'];

		$password = bcrypt($input['password']);

		$user = new User();
		$user->first_name = $first_name;
		$user->email = $email;
		$user->password = $password;

		if($request->has('last_name')){
			$user->last_name = $last_name;
		}

		if(isset($input['image']) && ! empty($input['image'])) {
            $user->addMedia($input['image'])->toMediaCollection(User::PATH, config('filesystems.default'));
        }

		// if($request->hasFile('image')){

		// 	$imageName = 'rider_'.uniqid().time().'.'.$request->image->extension();
  //   		$request->image->move(public_path('images/rider'), $imageName);

  //   		$fileName = '/images/rider/'.$imageName;

  //   		$image = $fileName;
		// }else{
		// 	$image = null;
		// }

		$user->phone = $phone;

		$user->language = "en";

		$user->user_fundraiser_id = $input['user_fundraiser_id'];

		$user->status = 0;

		$user->save();

    	if($user){
	        $user->assignRole('rider');

    		$data = [
	    		'status' => true,
	    		'message' => "User registered successfully!",
	    		'user' => $user
	    	];

	    	return response()->json($data, 201);
    	}else{
    		$data = [
	    		'status' => false,
	    		'message' => "Error accur on user registration"
	    	];

	    	return response()->json($data, 422);
    	}
    }

    public function verify(Request $request)
    {
    	$input = $request->all();

    	$validator = Validator::make($input, [
            'user_fundraiser_id' => 'required'
        ]);
 
        if ($validator->fails()) {
        	$data = [
	    		'status' => false,
	    		'message' => $validator->errors()->first()
	    	];

        	return response()->json($data, 403);
        }

        $user_id = $request->get('user_fundraiser_id');

        $check_user = User::where('user_fundraiser_id', $user_id)->first();

        if($check_user){
	        $response = Http::get('https://investin-api.javatimescaffe.com/api/admin/verified/'.$user_id);

	        $jsonData = $response->json();

	        if(isset($jsonData['status'])){
	        	if($jsonData['status'] == true){
	        		$fundraisor_walletId = $jsonData['walletId'];

	        		$update_wallet = User::find($check_user->id);
	        		$update_wallet->status = 1;
	        		$update_wallet->wallet_id = $fundraisor_walletId;
	        		if($update_wallet->save()){
	        			$user = User::find($check_user->id);
	        			$data = [
				    		'status' => true,
				    		'message' => "Rider verified successfully!",
				    		'user' => $user
				    	];

				    	return response()->json($data, 201);
	        		}else{
	        			$data = [
				    		'status' => false,
				    		'message' => "Error accur on creating rider wallet"
				    	];

				    	return response()->json($data, 422);
	        		}
	        	}else{
	        		$data = [
			    		'status' => false,
			    		'message' => $jsonData['message']
			    	];

			    	return response()->json($data, 422);
	        	}
	        }else{
	        	$data = [
		    		'status' => false,
		    		'message' => "Error accur on rider verification"
		    	];

		    	return response()->json($data, 422);
	        }
	    }else{
	    	$data = [
	    		'status' => false,
	    		'message' => "User not found"
	    	];

	    	return response()->json($data, 404);
	    }
    }

    public function verifyByPos(Request $request, $user_fundraiser_id)
    {
    	if($request->has('wallet_id')){
    		if(!empty($request->get('wallet_id'))){
		        $user_id = $user_fundraiser_id;

		        $check_user = User::where('user_fundraiser_id', $user_id)->first();

		        if($check_user){
		        	if(empty($check_user->wallet_id)){
			    		$update_wallet = User::find($check_user->id);
			    		$update_wallet->status = 1;
			    		$update_wallet->wallet_id = $request->get('wallet_id');
			    		if($update_wallet->save()){
			    			$user = User::find($check_user->id);
			    			$data = [
					    		'status' => true,
					    		'message' => "Rider verified successfully!",
					    		'user' => $user
					    	];

					    	return response()->json($data, 201);
			    		}else{
			    			$data = [
					    		'status' => false,
					    		'message' => "Error accur on verified rider"
					    	];

					    	return response()->json($data, 422);
			    		}
			    	}else{
			    		$data = [
				    		'status' => false,
				    		'message' => "rider already verified"
				    	];

				    	return response()->json($data, 422);
			    	}
			    }else{
			    	$data = [
			    		'status' => false,
			    		'message' => "User not found"
			    	];

			    	return response()->json($data, 404);
			    }
			}else{
				$data = [
		    		'status' => false,
		    		'message' => "wallet id is empty"
		    	];

		    	return response()->json($data, 422);
			}
		}else{
			$data = [
	    		'status' => false,
	    		'message' => "Wallet id not found"
	    	];

	    	return response()->json($data, 404);
		}
    }

    public function login(Request $request)
    {
    	$input = $request->all();

    	$validator = Validator::make($input, [
            'email' => 'required|email',
            'password' => 'required|min:6'
        ]);
 
        if ($validator->fails()) {
        	$data = [
	    		'status' => false,
	    		'message' => $validator->errors()->first()
	    	];

        	return response()->json($data, 403);
        }

        $email = $request->get('email');
        $password = $request->get('password');

    	if (empty($email) or empty($password)) {
            $data = [
	    		'status' => false,
	    		'message' => "email and password required"
	    	];

	    	return response()->json($data, 422);
        }
        $user = User::whereRaw('lower(email) = ?', [$email])->first();

        if (empty($user)) {
            $data = [
	    		'status' => false,
	    		'message' => "Invalid email or password"
	    	];

	    	return response()->json($data, 422);
        }

        if (! Hash::check($password, $user->password)) {
            $data = [
	    		'status' => false,
	    		'message' => "Invalid email or password"
	    	];

	    	return response()->json($data, 422);
        }

        if($user->status == 1){
        	$response = Http::get('https://investin-api.javatimescaffe.com/api/rider/wallet/'.$user->wallet_id);
        	$jsonData = $response->json();

        	if(isset($jsonData['status'])){
        		if($jsonData['status'] == true){
	    			$rider_wallet = $jsonData['rider'];

	    			$userPermissions = $user->getAllPermissions()->pluck('name')->toArray();
			        unset($user->roles);
			        unset($user->permissions);
			        $token = $user->createToken('token')->plainTextToken;
			        $user->last_name = $user->last_name ?? '';

					$data = [
			    		'status' => true,
			    		'message' => "Logged in successfully!",
			    		'user' => $user,
			    		'token' => $token,
			    		'wallet' => $rider_wallet
			    	];

			    	return response()->json($data, 201);
			    }else{
			    	$data = [
			    		'status' => false,
			    		'message' => $jsonData['message']
			    	];

			    	return response()->json($data, 422);
			    }
			}else{
				$data = [
		    		'status' => false,
		    		'message' => "Error accur show wallet"
		    	];

		    	return response()->json($data, 422);
			}
        }else{
        	$data = [
	    		'status' => false,
	    		'message' => "Error accur due to rider is unverified"
	    	];

	    	return response()->json($data, 422);
        }
    }

    // public function register(Request $request)
 //    {
 //    	$input = $request->all();

 //    	$validator = Validator::make($input, [
 //            'email' => 'required|email|unique:users',
 //            'password' => 'required|min:6',
 //            'first_name' => 'required',
 //            'phone' => 'required'
 //        ]);
 
 //        if ($validator->fails()) {
 //        	$data = [
	//     		'status' => false,
	//     		'message' => $validator->errors()->first()
	//     	];

 //        	return response()->json($data, 403);
 //        }

 //        $first_name = $input['first_name'];

 //        if($request->has('last_name')){
	// 		$last_name = $input['last_name'];
	// 	}else{
	// 		$last_name = null;
	// 	}

 //        $phone = $input['phone'];

	// 	$email = $input['email'];

	// 	$password = $input['password'];

	// 	// if($request->hasFile('image')){
	// 	// 	$imageName = 'rider_'.uniqid().time().'.'.$request->image->extension();
 //  //   		$request->image->move(public_path('images/rider'), $imageName);

 //  //   		$fileName = '/images/rider/'.$imageName;

 //  //   		$image = $fileName;
	// 	// }else{
	// 	// 	$image = null;
	// 	// }

 //    	$response = Http::post('https://investin-api.javatimescaffe.com/api/users', [
 //            'name' => $first_name,
 //            'nickName' => $last_name,
 //            'phone' => $phone,
 //            'email' => $email,
 //            'password' => $password
 //            // 'image' => $image
 //        ]);
  
 //        $jsonData = $response->json();

 //    	if(isset($jsonData['status'])){
 //        	if($jsonData['status'] == true){
	//     		$fundraisor_userId = $jsonData['user']['_id'];
	//     		$password = bcrypt($password);

	//     		$user = new User();
	//     		$user->first_name = $first_name;
	//     		$user->email = $email;
	//     		$user->password = $password;

	//     		if($request->has('last_name')){
	//     			$user->last_name = $last_name;
	//     		}

	//     		if($request->hasFile('image')){
	//     			$user->image = $image;
	//     		}

	//     		$user->phone = $phone;

	//     		$user->language = "en";

	//     		$user->user_fundraiser_id = $fundraisor_userId;

	//     		$user->save();

	// 	    	if($user){
	// 		        $user->assignRole('rider');
	// 		    	$token = $user->createToken('token')->plainTextToken;

	// 	    		$data = [
	// 		    		'status' => true,
	// 		    		'message' => "User registered successfully!",
	// 		    		'user' => $user,
	// 		    		'token' => $token
	// 		    	];

	// 		    	return response()->json($data, 201);
	// 	    	}else{
	// 	    		$data = [
	// 		    		'status' => false,
	// 		    		'message' => "Error accur on user registration"
	// 		    	];

	// 		    	return response()->json($data, 422);
	// 	    	}
	// 	    }else{
	// 	    	$data = [
	// 	    		'status' => false,
	// 	    		'message' => $jsonData['message']
	// 	    	];

	// 	    	return response()->json($data, 422);
	// 	    }
 //    	}else{
 //    		$data = [
	//     		'status' => false,
	//     		'message' => "Error accur on rider registration"
	//     	];

	//     	return response()->json($data, 422);
 //    	}
 //    }

    // public function verify(Request $request)
    // {
    // 	$input = $request->all();

    // 	$validator = Validator::make($input, [
    //         'user_id' => 'required'
    //     ]);
 
    //     if ($validator->fails()) {
    //     	$data = [
	   //  		'status' => false,
	   //  		'message' => $validator->errors()->first()
	   //  	];

    //     	return response()->json($data, 403);
    //     }

    //     $user_id = $request->get('user_id');

    //     $check_user = User::find($user_id);

    //     if($check_user){
    //     	if(!empty($check_user->user_fundraiser_id)){
		  //       $response = Http::get('https://investin-api.javatimescaffe.com/api/admin/verified/'.$check_user->user_fundraiser_id);

		  //       $jsonData = $response->json();

		  //       if(isset($jsonData['status'])){
		  //       	if($jsonData['status'] == true){
		  //       		$fundraisor_walletId = $jsonData['walletId'];

		  //       		$check_user->wallet_id = $fundraisor_walletId;
		  //       		if($check_user->save()){
		  //       			$user = User::find($check_user->id);
		  //       			$data = [
				// 	    		'status' => true,
				// 	    		'message' => "Rider verified successfully!",
				// 	    		'user' => $user
				// 	    	];

				// 	    	return response()->json($data, 201);
		  //       		}else{
		  //       			$data = [
				// 	    		'status' => false,
				// 	    		'message' => "Error accur on creating rider wallet"
				// 	    	];

				// 	    	return response()->json($data, 422);
		  //       		}
		  //       	}else{
		  //       		$data = [
				//     		'status' => false,
				//     		'message' => $jsonData['message']
				//     	];

				//     	return response()->json($data, 422);
		  //       	}
		  //       }else{
		  //       	$data = [
			 //    		'status' => false,
			 //    		'message' => "Error accur on rider verification"
			 //    	];

			 //    	return response()->json($data, 422);
		  //       }
		  //   }else{
		  //   	$data = [
		  //   		'status' => false,
		  //   		'message' => "User fundraiser id is empty"
		  //   	];

		  //   	return response()->json($data, 422);
		  //   }
	   //  }else{
	   //  	$data = [
	   //  		'status' => false,
	   //  		'message' => "User not found"
	   //  	];

	   //  	return response()->json($data, 404);
	   //  }
    // }

    // public function login(Request $request)
    // {
    // 	$input = $request->all();

    // 	$validator = Validator::make($input, [
    //         'email' => 'required|email',
    //         'password' => 'required|min:6'
    //     ]);
 
    //     if ($validator->fails()) {
    //     	$data = [
	   //  		'status' => false,
	   //  		'message' => $validator->errors()->first()
	   //  	];

    //     	return response()->json($data, 403);
    //     }

    //     $email = $request->get('email');
    //     $password = $request->get('password');

    // 	$response = Http::post('https://investin-api.javatimescaffe.com/api/users/login', [
    //         'email' => $email,
    //         'password' => $password
    //     ]);
  
    //     $jsonData = $response->json();

    //     if(isset($jsonData['status'])){
    //     	if($jsonData['status'] == true){
    //     		if($jsonData['user']['isVerified'] == true){
			 //    	if (empty($email) or empty($password)) {
			 //            $data = [
				//     		'status' => false,
				//     		'message' => "email and password required"
				//     	];

				//     	return response()->json($data, 422);
			 //        }
			 //        $user = User::whereRaw('lower(email) = ?', [$email])->first();

			 //        if (empty($user)) {
			 //            $data = [
				//     		'status' => false,
				//     		'message' => "Invalid email or password"
				//     	];

				//     	return response()->json($data, 422);
			 //        }

			 //        if (! Hash::check($password, $user->password)) {
			 //            $data = [
				//     		'status' => false,
				//     		'message' => "Invalid email or password"
				//     	];

				//     	return response()->json($data, 422);
			 //        }


			 //        $userPermissions = $user->getAllPermissions()->pluck('name')->toArray();
			 //        unset($user->roles);
			 //        unset($user->permissions);
			 //        $token = $user->createToken('token')->plainTextToken;
			 //        $user->last_name = $user->last_name ?? '';
		    
		  //   		$data = [
			 //    		'status' => true,
			 //    		'message' => "Logged in successfully!",
			 //    		'user' => $user,
			 //    		'token' => $token,
			 //    		'permissions' => $userPermissions
			 //    	];

			 //    	return response()->json($data, 201);

    //     		}else{
    //     			$data = [
			 //    		'status' => false,
			 //    		'message' => "Rider is not verified."
			 //    	];

			 //    	return response()->json($data, 422);
    //     		}
    //     	}else{
    //     		$data = [
		  //   		'status' => false,
		  //   		'message' => $jsonData['message']
		  //   	];

		  //   	return response()->json($data, 422);
    //     	}
    //     }else{
    //     	$data = [
	   //  		'status' => false,
	   //  		'message' => "Error accur on rider login"
	   //  	];

	   //  	return response()->json($data, 422);
    //     }
    // }

    public function updateLocation(Request $request)
    {
    	// $userId = 2;
        $latitude = $request->latitude;
        $longitude = $request->longitude;

        event(new LocationUpdated($latitude, $longitude));
        return response()->json("event success", 201);
        // event(new LocationUpdated($userId, $latitude, $longitude));
    }}
