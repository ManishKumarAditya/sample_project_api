<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\CreateUserMail;
use App\Models\Admin;
use App\Models\Student;
use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function create_user(Request $request) {
        // validate incoming request
        $validate_request = Validator::make($request->all(), [
            'token'     => ['required', 'string', 'max:30',
            function ($attribute, $value, $fail) {
                if (!DB::table('admins')->where($attribute, $value)->exists()&&
                    !DB::table('students')->where($attribute, $value)->exists()) 
                {
                    return $fail("The provided $attribute does not exists.");
                }
            }
        ],
            'password'  => ['required', 'string', 'min:8', 'max:100', 'confirmed'],
        ]);

        if($validate_request->fails()) {
            $response_data['errors'] = $validate_request->errors()->all();
            return response()->json(['data' => $response_data], 422);
        }

        if($admin = Admin::where('token', $request['token'])->first()) {
            $admin->user()->create([
                'name'      => $admin->name,
                'email'     => $admin->email,
                'password'  => Hash::make($request['password']),
            ]);

            $admin->token = NULL;
            $admin->save();
            // send login credentials in mail to admin 
            $create_user_mail = Mail::to($admin['email'])->send(new CreateUserMail($admin->name, $admin->email, $request['password'])); 
           
            // response
            $response_data['message'] = 'login credentials is sent on your email id.';
            return response()->json(['data' => $response_data], 201);
        }
        elseif($customer = Student::where('token', $request['token'])->first()) {
            // if the user is a customer
            $customer->user()->create([
                'name'      => $customer->name,
                'email'     => $customer->email,
                'password'  => Hash::make($request['password']),
            ]);
            $customer->token = NULL;
            $customer->save();

            $create_user_mail = Mail::to($customer['email'])->send(new CreateUserMail($customer->name, $customer->email, $request['password'])); 
            
            // response
            $response_data['message'] = 'login credentials is sent on your email id.';
            return response()->json(['data' => $response_data], 201); 
        }         
    }

    public function update(Request $request) {
        /** @var User $user */
        $user = Auth::user();
        if($user->profile_type == 'App\Models\Student'){
            // validate incoming request
            $validator = Validator::make($request->all(), [
                'name'          => ['required', 'string', 'max:255'],
                'mobile_no'     => ['required', 'digits:10', 'regex:/^[0-9]{10}$/'],
                'profile_image' => ['nullable', 'mimes:jpg', 'max:10240'],
                'country_code'  => ['required', 'string', 'regex:/^\+\d{1,5}$/'],
                'date_of_birth' => ['required', 'date_format:Y-m-d']
            ]);

            if($validator->fails()) {
                $response_data['errors'] = $validator->errors()->all();
                return response()->json(['data' => $response_data], 422);    
            }

            $data = [
                'name'          => $request['name'],
                'mobile_no'     => $request['mobile_no'],
                'country_code'  => $request['country_code'],
                'date_of_birth' => $request['date_of_birth']
            ];
    
            // save supporting document in assets/uploads/student_profile folder
            if ($request->hasFile('profile_image')) {
                $directory_assets_appointment = 'assets/uploads/student_profile';
                File::isDirectory($directory_assets_appointment) or File::makeDirectory($directory_assets_appointment, 0777, true, true);
    
                $supporting_document = $request['profile_image'];
                $supporting_document_with_extension = changeFileName($supporting_document, "student_profile");
                $supporting_document->move($directory_assets_appointment, $supporting_document_with_extension);
                $data['profile_image'] = $supporting_document_with_extension;
            }

            //update student profile
            $user->profile()->update($data);

            $user->update([
                'name' => $request['name'],
            ]);
        }else{
            return response()->json(['data' => 'uunauthorized'], 401);
        }
       
        // response
        $response_data['message'] = 'Profile Updated!';
        $response_data['profile'] = $user->profile;
        return response()->json(['data' => $response_data], 200);
    }
}
