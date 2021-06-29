<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Code;
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
            //$tokenResult = $user->createToken('authToken')->plaintTextToken;
            return ResponseFormatter::success([
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

    public function register( UserPostRequest $request){


            $validated = $request->validated();

            //send verification code to email
            $this->sendCode($request->email);

            $user = User::create([
                'name' => $request->name,
                'email'=> $request->email,
                'NoTelpon' => $request->NoTelpon,
                'password' => Hash::make($request->password)
            ]);

            $code = $this->sendCode($user->id, $user->email);


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

    public function resendCode(){
        $id = Auth::id();
        $to_email = Auth::user()->email;
        $this->sendCode($id,$to_email);
        return ResponseFormatter::success(null, 'Resend Successful!');
    }

    function sendCode($id, $to_email){
        $code = $this->random_str();
        Mail::to($to_email)->send(new verifyMail($code));
        $updated_code =Code::upsert([['user_id' => $id, 'code' => $code,]], ['user_id'],['code']);
        return $code;
    }

    function verifyCode(Request $request){
        $user_id = Auth::id();
        $actual_code = Code::findOrFail($user_id);

        if($request->code == $actual_code){
            verifyUser($user_id);
            return ResponseFormatter::success(null, "Successful Verification!");
         }
        return ResponseFormatter::error(null,"Wrong Code!",400);
    }

    function verifyUser($user_id){
        Code::where('user_id', $user_id)->delete();
        $user =  User::find($user_id);
        $user->verified = 1;

    }

    function random_str(int $length = 6): string {
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
