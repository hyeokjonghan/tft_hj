<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class ApiAuthController extends Controller
{

    public function generateAccessToken($authData) {
            $request = Request::create('/oauth/token', 'POST', $authData);
            try {
                $response = app()->handle($request);
                $authData = json_decode($response->content(), true);
                if($response->getStatusCode() === 200) {
                    return response($authData);
                } else {
                    return response($authData, Response::HTTP_UNAUTHORIZED);
                }
            } catch (Exception $e) {
                return response();
            }
    }

    public function createToken(Request $request) {
        $user = User::where('email', $request->email)->first();
        if($user) {
            $data = [
                'grant_type'=>'password',
                'client_id' => env("APP_CLIENT_ID"),
                'client_secret'=> env("APP_CLIENT_SECRET"), 
                'username'=>$user->email,
                'password'=>$request->password,
                'scope'=>'*'
            ];

            return $this->generateAccessToken($data);
        } else {
            return response()->caps('not found user', Response::HTTP_NOT_FOUND);
        }
    }

    public function tokenRefresh(Request $request) {
        $data = [
            'grant_type' => "refresh_token",
            'refresh_token' => $request->headers->get('refresh_token'),
            'client_id' => env("APP_CLIENT_ID"),
            'client_secret'=> env("APP_CLIENT_SECRET"), // oauth_clients id = 2의 secret 값
            'scope' => '',
        ];
    
        $request = Request::create('/oauth/token', 'POST', $data);
        $response = app()->handle($request);
        $response = json_decode($response->content(),true);
        try {
            if(isset($response['error'])) {
                return response($response, Response::HTTP_UNAUTHORIZED);
            } else {
                return response($response);
            }
        } catch (Exception $e) {
            return response($response, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function createUser(Request $request) {
        $validator = [
            'name'=>'required|string|max:50',
            'email'=>'required', // 내일 정규식 추가
            'password'=>'required' // 내일 정규식 추가
        ];

        $validatorCheck = Validator::make($request->all(), $validator);
        if($validatorCheck->fails()) {
            return response($validatorCheck->errors()->all(), Response::HTTP_METHOD_NOT_ALLOWED);
        }

        // check User
        $checkUser = User::where('email', $request->email)->first();
        if($checkUser) {
            return response()->caps('user email is already.', Response::HTTP_CONFLICT);
        }

        $user = new User([
            'name'=>$request->name,
            'email'=>$request->email,
            'password'=>bcrypt($request->password)
        ]);
        $user->save();

        return $user;
    }

}
