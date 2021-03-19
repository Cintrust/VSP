<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterUserController extends Controller
{
    public function create(Request $request)
    {
        $data =  $request->validate([
//            'first_name' => ['bail', 'required', 'string', 'min:3', 'max:40'],
//            'last_name' => ['bail', 'required', 'string', 'min:3', 'max:40'],
            'name' => ['bail', 'required', 'string', 'min:3', 'max:60'],
            'email' => ['bail', 'required', 'string', 'email', 'max:200', 'unique:users'],
            'password' => ['bail', 'required', 'string', 'min:8',],

        ]);

        $user = User::create([
            "name"=>$data['name'],
            "email"=>$data['email'],
            "password"=>Hash::make($data['password']),
        ]);


        return response(null, 201);

    }
}
