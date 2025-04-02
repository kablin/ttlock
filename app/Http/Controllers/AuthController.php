<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;



class AuthController extends Controller
{
    public function CreateUser(Request $request)
    {
        try {

            $validated = $request->validate([

                'email' => 'required|string|email|max:255',
                'password' => 'required|string|min:8',
            ]);

            $user = User::firstOrCreate([
                'email' => $validated['email']
            ], [
                'name' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);


            $credentials['email'] = $validated['email'];
            $credentials['password'] = $validated['password'];

            if (!Auth::attempt($credentials))
            {
                return response()->json(['status' => false,'message' => 'Incorrect email or password',],200);

            }

            return response()->json([
                'status' => true,
                'message' => 'User created successfully',
            ], 200); 

 
        } catch (\Exception $e) {
            return  response()->json(['status' => false],200);
        }
    }
}
