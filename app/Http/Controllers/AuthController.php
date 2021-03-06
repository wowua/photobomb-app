<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        Validator::make($request->all(), [
            'name' => 'required|unique:users',
            'password' => 'required',
            'email' => 'required|email|unique:users'
        ], [
            'required' => "Поле обязательно для заполнения",
            'name.unique' => "Пользователь с таким имененм уже существует",
            'email.unique' => "Пользователь с таким email уже существует"
        ])->validate();
        
        $user = new User([
            'name' => $request->name,
            'password' => bcrypt($request->password),
            'email' => $request->email
        ]);
        
        $user->remember_token = bcrypt($request->email.time());
        $user->save();

        return response()->json([
            // 'user' => $user,
            'message' => 'Регистрация прошла успешно'
        ]);
    }  

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'status' => 0,
                    'error' => 'Не верное имя пользователя или пароль'
                ], 401);
            }
        } catch (JWTException $e) {
            return response()->json([
                'status' => 0,
                'error' => 'Не получилось создать токен!'
            ], 500);
        }

        return response()->json([
            'status' => 1,
            'message' => 'Успешный вход',
            'token' => $token,
        ]);
    }

    public function refreshToken(Request $request)
    {
        $token = JWTAuth::getToken();
        $newToken = JWTAuth::refresh($token);

        return response()->json([
            'token' => $newToken,
        ]);
    }

    public function getUser() {
        return response()->json([
            'user' => Auth::user()
        ]);
    }
}
