<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\WelcomeRegistrationMail;
use App\Models\Admin;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function store(Request $request)
    {
        // validate incoming request
        $validator = Validator::make($request->all(), [
            'name'         => ['required', 'string', 'max:255'],
            'email'        => ['required', 'string', 'email', 'max:100', 'unique:students'],
            'phone'        => ['required', 'digits:10', 'regex:/^[0-9]{10}$/'],
            'state'        => ['required', 'string', 'max:255'],
            'country_code' => ['required', 'string', 'regex:/^\+\d{1,5}$/'],
        ]);

        if ($validator->fails()) {
            $response_data['errors'] = $validator->errors()->all();
            return response()->json(['data' => $response_data], 422);
        }

        $token = Str::random(10);
        $admin = Admin::create([
            'name'          => $request['name'],
            'email'         => $request['email'],
            'phone'         => $request['phone'],
            'token'         => $token,
            'address'       => $request['address'],
            'country_code'  => $request['country_code'],
        ]);

        // send welcome mail to the distributor
        $title = '[Self_register] Welcome Admin mail';
        $sendmail = Mail::to($admin['email'])->send(new WelcomeRegistrationMail($title, $admin['name'], $token));

        // response
        $response_data['message'] = 'Welcome mail sent on your email id.';
        return response()->json(['data' => $response_data], 201);
    }

    // method is used to get all user's
    public function index() {
        $students = Student::orderBy('name')->get(); 
        $response_data['message'] = 'success';
        $response_data['students'] = $students;
        return response()->json(['data' => $response_data], 200);       
    }

    public function delete_user($id) {
        // find students
        if(!$delete_user = Student::find($id)) {
            $response_data['errors'] = 'student not found';
            return response()->json(['data' => $response_data], 404);
        }

        // delete review from database
        $delete_user->delete($id);

        $user = User::where(['profile_id' => $id, 'profile_type' => 'App\Models\Student'])->delete($id);

        // response
        $response_data['message'] = 'User deleted';
        return response()->json(['data' => $response_data], 200);
    }
}
