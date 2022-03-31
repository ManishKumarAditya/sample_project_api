<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SessionControlller extends Controller
{
    // create login for user
    public function login(Request $request) {
        // validate incoming request
        $validate_data = Validator::make($request->all(), [
            'email'     => ['required', 'string', 'email', 'max:100'],
            'password'  => ['required', 'string', 'min:8', 'max:100']
        ]);

        if($validate_data->fails()) {
            $response_data['errors'] = $validate_data->errors()->all();
            return response()->json(['data' => $response_data], 422);
        }

        $login_credentials = [
            'email'     => $request->email,
            'password'  => $request->password
        ];
        
        if(!$user = User::select('email', 'id', 'profile_id', 'profile_type')->with('profile:id,name')->where('email', $login_credentials['email'])->first()) {
            $response_data['errors'] = 'User does not exist';
            return response()->json(['data' => $response_data], 404);
        }

        if (auth()->attempt($login_credentials)) {
            /** @var User $user */
            $token = $user->createToken('AccessToken')->accessToken;
            //fetching login user data
            $response_data['user_details'] = $user;
            $response_data['token'] = $token;
            $response_data['message'] = 'login successful';

            // respose 
            return response()->json(['data' => $response_data], 200);
        } 
        $response_data['errors'] = 'Password mismatch';
        return response()->json(['data' => $response_data], 401);   
    }

    public function profile() {
        return response()->json(['data' => Auth::user()], 200);
    }

    // public function logout() {
    //     $token = Auth::user()->token();
    //     $token->revoke();
        
    //     $response_data['message'] = 'Logout successful';
    //     return response()->json(['data' => $response_data], 200);
    // }
}
