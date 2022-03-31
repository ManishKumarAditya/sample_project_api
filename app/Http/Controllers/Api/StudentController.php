<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\WelcomeRegistrationMail;
use App\Models\Student;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class StudentController extends Controller
{
    // register details in students table
    public function store(Request $request) {
        //incoming validate incoming reqest
        $validator = Validator::make($request->all(), [
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['required', 'string', 'email', 'max:100', 'unique:users'],
            'mobile_no'     => ['required', 'digits:10', 'regex:/^[0-9]{10}$/'],
            'profile_image' => ['nullable', 'mimes:jpg', 'max:10240'],
            'country_code'  => ['required', 'string', 'regex:/^\+\d{1,5}$/'],
            'date_of_birth' => ['required', 'date_format:Y-m-d']
        ]);

        // validation error
        if($validator->fails()) {
            $response_data['errors'] = $validator->errors()->all();
            return response()->json(['data' => $response_data], 422);    
        }

        $token = Str::random(10);

        $user = [
            'name'          => $request['name'],
            'email'         => $request['email'],
            'mobile_no'     => $request['mobile_no'],
            'token'         => $token,
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
            $user['profile_image'] = $supporting_document_with_extension;
        }

        $students = Student::create($user);

        // send mail to register user
        $title = 'Welcome Registration Mail';
        $sendmail = Mail::to($user['email'])->send(new WelcomeRegistrationMail($title, $user['name'], $token));

        // send response
        $response_data['message'] = 'User registration successfully & Welcome mail sent on your email id';
        return response()->json(['data' => $response_data], 201);
    }



}
