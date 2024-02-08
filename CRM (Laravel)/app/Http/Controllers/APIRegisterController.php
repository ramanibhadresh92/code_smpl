<?php

namespace App\Http\Controllers;
use App\Http\Controllers\LRBaseController;
use Illuminate\Http\Request;
use App\Models\AdminUser;
use JWTFactory;
use JWTAuth;
use Validator;
use Response;


class APIRegisterController extends LRBaseController
{
    public function passencrypt(Request $request)
    {
        return Response::json(passencrypt($request->password));
    }

    public function passdecrypt(Request $request)
    {
        return Response::json(passdecrypt($request->password));
    }

   	public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255|unique:adminuser',
            'username' => 'required|unique:adminuser',
            'password'=> 'required'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        AdminUser::create([
            'username' => $request->get('username'),
            'email' => $request->get('email'),
            'password' => passencrypt($request->get('password')),
        ]);
        $user = AdminUser::first();
        $token = JWTAuth::fromUser($user);
        
        return Response::json(compact('token'));
    }
}
