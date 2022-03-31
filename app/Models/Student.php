<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $hidden = ['token'];
    protected $fillable = [
        'name', 'email', 'mobile_no', 'token', 'country_code' , 'date_of_birth', 'profile_image' 
    ];

    public function user(){
        return $this->morphOne(User::class, 'profile');
    }

}
