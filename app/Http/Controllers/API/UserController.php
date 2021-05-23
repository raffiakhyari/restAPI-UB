<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use App\Http\Requests\UserPostRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function login(Request $request)
    {
        try {

            //validasi input login
            $request->validate([
                'email'=> 'email|required',
                'password'=> 'required',
            ]);

            //mengecek credentials login
            $credentials = request(['email', 'password']);
            if(!Auth::attempt($credentials)){
                return ResponseFormatter::error([
                    'message' => 'Unauthorized'
                ], 'Authentication Failed', 500);
            }

            //jika Hash tidak sesuai maka beri error
            $user = User::where('email', $request->email)->first();
            if(!Hash::check($request->password, $user->password, [])){
                throw new \Exception('Invalid Credentials');
            }

            //jika berhasil maka login
            $tokenResult = $user->createToken('authToken')->plaintTextToken;
            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user'=> $user
            ], 'Authenticated');
        }

        catch(Exception $error){
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $error
            ], 'Authentication Failed');
        }
    }

    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken()->delete();

        return ResponseFormatter::success($token, 'Token Revoked');
    }

    public function fetch(Request $request)
    {
        return ResponseFormatter::success(
            $request->user(),'Data user berhasil diambil');
    }

    public function register(Request $request){


            $validator = Validator::make($request->all(),[
                'name'=>'required|max:255',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:8',
                'NoTelpon' => 'required|regex:/(08)[0-9]{9}/',

            ]);

            //kalau validator nya fail
            if($validator->fails()){
                return ResponseFormatter::error([
                    'message' => 'Something went wrong',
                    'error' => $validator->messages(),
                ],'Authentication Failed', 500);

            }

            $user = User::create($request->all());

            return ResponseFormatter::success([
                'user'=> $user
                ], 'Daftar berhasil');

    }

    public function updateProfile(Request $request)
    {
        $data = $request->all();

        $user = Auth::user();
        $user -> update($data);

        return ResponseFormatter::success($user, 'Profile Updated');
    }




}
