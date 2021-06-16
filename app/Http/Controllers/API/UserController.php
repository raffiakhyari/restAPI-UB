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

use Illuminate\Support\Facades\Mail;
use App\Mail\verifyMail;

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

            //send verification code to email
            $code = $this->sendCode($request->email);
            //storing code to session
            $request->session()->put('code', $code);


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

    public function sendCode($to_email="axelchristiant33@gmail.com"){
        $code = $this->random_str();
        Mail::to($to_email)->send(new verifyMail($code));
        return $code;
    }

    public function verifyCode(Request $request){
        if($request->code == $request->session()->get('code')){
            return ResponseFormatter::success(null, "Successful Verification");
         }
        return ResponseFormatter::error(null,"Wrong Code!",400);
    }

    function random_str(int $length = 5): string {
        $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        if ($length < 1) {
            throw new \RangeException("Length must be a positive integer");
        }
        $pieces = [];
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $pieces []= $keyspace[random_int(0, $max)];
        }
        return implode('', $pieces);
    }




}
