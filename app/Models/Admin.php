<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    use HasFactory;

    protected $hidden = ['token'];
    
    protected $fillable = [
        'name', 'email', 'phone', 'token', 'state', 'country_code', 'address'
    ];

    public function user(){
        return $this->morphOne(User::class, 'profile');
    }
}
