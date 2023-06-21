<?php

namespace App\Http\Controllers\API\Rider;

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


class FundraiseController extends Controller
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

        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);

    	if($user){
	        $user->assignRole('rider');
	    	$token = $user->createToken('token')->plainTextToken;

    		$data = [
	    		'status' => true,
	    		'message' => "User registered successfully",
	    		'user' => $user,
	    		'token' => $token
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


        $userPermissions = $user->getAllPermissions()->pluck('name')->toArray();
        unset($user->roles);
        unset($user->permissions);
        $token = $user->createToken('token')->plainTextToken;
        $user->last_name = $user->last_name ?? '';


        if($user->status == 1){
        	$ldate = date('Y-m-d H:i:s');

        	$add_log = new UserLog();
        	$add_log->user_id = $user->id;
        	$add_log->last_login_time = $ldate;
        	$add_log->save();

        	if($add_log){
	        	$data = [
		    		'status' => true,
		    		'message' => "Logged in successfully.",
		    		'user' => $user,
		    		'token' => $token,
		    		'permissions' => $userPermissions
		    	];

		    	return response()->json($data, 201);
	    	}else{
	    		$data = [
		    		'status' => false,
		    		'message' => "User log not mentain error"
		    	];

		    	return response()->json($data, 422);
	    	}
        }else{
			try {
				$otp = rand(100000, 999999);

		        $update_otp = User::find($user->id);
		        $update_otp->otp = $otp;
				$update_otp->save();

				$mailBody = 'your verification otp code is: '.$otp;
		        
		        $array = array('subject'=>'Verification OTP Code','view'=>'emails.mail-otp','body' => $mailBody);

		        Mail::to($user->email)->send(new OtpMail($array));

		        $data = [
		    		'status' => true,
		    		'message' => "Please verify your account",
		    		'user' => $user,
		    		'token' => $token,
		    		'permissions' => $userPermissions,
		    		'otp' => $otp
		    	];

		    	return response()->json($data, 201);
	    	} catch (\Exception $e) {
	            $data = [
		    		'status' => false,
		    		'message' => "Mail not send due to something went wrong.",
		    		'error' => $e->getMessage()
		    	];

		    	return response()->json($data, 500);
	        }
    	}
    }

    // public function changePassword(Request $request)
    // {
    // 	$validator = \Validator::make($request->all(), [
    //         'current_password' => 'required|min:6',
    //         'new_password' => 'required|min:6',
    //         'confirm_new_password' => 'required|min:6|same:new_password'
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status' => 422,
    //             'message' => $validator->errors()->first()
    //         ], 422);
    //     }else{
    //         $user = User::where('id', $request->user_id)->first();

    //         if($user){
    //             if (!Hash::check($request->current_password, $user->password)) {
    //                 return response()->json([
    //                     'status' => 422,
    //                     'message' => 'Invalid Credentials!'
    //                 ], 422);
    //             }else{
    //                 $user->password = Hash::make($request->new_password);
    //                 $user->save();
    
    //                 return response()->json([
    //                     'status' => 200,
    //                     'message' => 'Password changed successfully.'
    //                 ], 200);
    //             }
    //         }else{
    //             return response()->json([
    //                 'status' => 404,
    //                 'message' => 'User Does Not Exists!'
    //             ], 404);
    //         }
    //     }
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

    // 	if($response->successful()){
    // 		return $jsonData;
    // 		if (empty($email) or empty($password)) {
	   //          $data = [
		  //   		'status' => false,
		  //   		'message' => "email and password required"
		  //   	];

		  //   	return response()->json($data, 422);
	   //      }
	   //      $user = User::whereRaw('lower(email) = ?', [$email])->first();

	   //      if (empty($user)) {
	   //          $data = [
		  //   		'status' => false,
		  //   		'message' => "Invalid email or password"
		  //   	];

		  //   	return response()->json($data, 422);
	   //      }

	   //      if (! Hash::check($password, $user->password)) {
	   //          $data = [
		  //   		'status' => false,
		  //   		'message' => "Invalid email or password"
		  //   	];

		  //   	return response()->json($data, 422);
	   //      }


	   //      $userPermissions = $user->getAllPermissions()->pluck('name')->toArray();
	   //      unset($user->roles);
	   //      unset($user->permissions);
	   //      $token = $user->createToken('token')->plainTextToken;
	   //      $user->last_name = $user->last_name ?? '';
    
    // 		$data = [
	   //  		'status' => true,
	   //  		'message' => "Logged in successfully.",
	   //  		'user' => $user,
	   //  		'token' => $token,
	   //  		'permissions' => $userPermissions
	   //  	];

	   //  	return response()->json($data, 201);
    // 	}else{
    // 		$data = [
	   //  		'status' => false,
	   //  		'message' => "Error accur on user login by fundraise"
	   //  	];

	   //  	return response()->json($data, 422);
    // 	}
    // }
}
