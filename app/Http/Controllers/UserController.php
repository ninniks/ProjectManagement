<?php

namespace App\Http\Controllers;

use App\Http\Rules\UserLoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Guards\TokenGuard;

class UserController extends Controller
{
    public function login(UserLoginRequest $request): JsonResponse
    {

            $email = $request->get('email');
            $pass = $request->get('password');
            $token = Auth::attempt(['email' => $email, 'password' => $pass]);

            if(!$token){
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            return response()->json(['user' => Auth::user(), 'token' => $token]);
    }
}
