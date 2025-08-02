<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request){
    	try {
    		$request->validate([
    			'email' => 'email|required',
    			'password' => 'required'
    		]);

    		$credentials = request(['email', 'password']);

    		if (!Auth::attempt($credentials)) {
    			return response()->json([
    				'message' => 'Unauthorized'
    			], 401);
    		}

    		$user = User::where('email', $request->email)->first();

    		if ( ! Hash::check($request->password, $user->password, [])) {
    			throw new \Exception('Error in Login');
    		}

    		$tokenResult = $user->createToken('authToken')->plainTextToken;
    		return response()->json([
    			'access_token' => $tokenResult,
				'user' => $user,
    			'token_type' => 'Bearer',
    		], 200);
    	} catch (Exception $error) {
    		return response()->json([
    			'message' => 'Error in Login',
    			'error' => $error,
    		], 500);
    	}
    }
}
