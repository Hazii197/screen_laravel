<?php

namespace App\Http\Controllers;

use App\Profile;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Support\Facades\Password;

class APILoginController extends Controller
{
    use SendsPasswordResetEmails;
    use ResetsPasswords;
    public function login() {
        // get email and password from request
        $credentials = request(['email', 'password']);

        // try to auth and get the token using api authentication
        if (!$token = auth('api')->attempt($credentials)) {
            // if the credentials are wrong we send an unauthorized error in json format
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return response()->json([
            'token' => $token,
            'type' => 'bearer', // you can ommit this
            'expires' => auth('api')->factory()->getTTL() * 600, // time to expiration
        ]);
    }
    public function register(Request $request){
        $request->validate([
            'username'=>'required||string',
            'email'=>'required||email',
            'password'=>'required',
        ]);
        $user = User::where('email','=',$request->email)->first();
        if(!$user) {
            $user = new User();
            $user->name = $request->username;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->company=$request->company;
            if ($user->save()) {
                return response()->json(['status' => 200, 'message' => 'Success! User registered Successfully'], 200);
            } else {
                return response()->json(['status' => 400, 'message' => 'Failed! User not Saved Successfully'], 200);
            }
        }else{
            return response()->json(['status'=>402,'message'=>'Failed! This email is already in use.Please use different one']);
        }
    }
    public function logout(Request $request){
        $token = $request->token;
        if(empty($token)){
            return response()->json(['status'=>'401','message'=>'Failed! Please Provide token to logged out']);
        }else{
            auth()->logout();
            auth()->logout(true);
            return response()->json(['status'=>'200','message'=>'Success! User logged out successfully']);
        }
    }
    public function profile(Request $request){
        $id = auth()->user()->id;
        $profile = DB::table('profiles')->where('user_id',$id)->first();
        if($profile){
            $profile = Profile::find($profile->id);
            $profile->address1 = $request->address1;
            $profile->address2 = $request->address2;
            $profile->city = $request->city;
            $profile->country = $request->country;
            $profile->StateProvince = $request->StateProvince;
            $profile->ZipPostalCode = $request->ZipPostalCode;
            $profile->user_id = $id;

            if($profile->update()){
                return response()->json(["status"=>'200','response'=>"Success! Profile updated successfully"]);
            }else{
                return response()->json(["status"=>'200','response'=>"Failed! An error occurred in updating profile"]);
            }
        }else{
            $profile = new Profile();
            $profile->address1 = $request->address1;
            $profile->address2 = $request->address2;
            $profile->city = $request->city;
            $profile->country = $request->country;
            $profile->StateProvince = $request->StateProvince;
            $profile->ZipPostalCode = $request->ZipPostalCode;
            $profile->user_id = $id;

            if($profile->save()){
                return response()->json(["status"=>'200','response'=>"Success! Profile saved successfully"]);
            }else{
                return response()->json(["status"=>'200','response'=>"Failed! An error occurred in saving profile"]);
            }

        }
    }
    public function getProfile(Request $request){
        $id = auth()->user()->id;
        $profile = DB::table('profiles')->where('user_id',$id)->first();
        if($profile) {
            $profile = Profile::find($profile->id);
            return response()->json(["data"=>$profile]);
        }else{
            return response()->json(['data'=>'Please update profile first']);
        }
    }
    public function getResetToken(Request $request)
    {
        $this->validate($request, ['email' => 'required|email']);
        $user = User::where('email', $request->input('email'))->first();
        if (!$user) {
            return response()->json(['status'=>'400','message'=>'Failed! This email is not in our database  '], 400);
        }
        $token = $this->broker()->createToken($user);
        return response()->json(['token' => $token]);
    }
    public function reset(Request $request)
    {
        $this->validate($request, $this->rules(), $this->validationErrorMessages());

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $response = $this->broker()->reset(
            $this->credentials($request), function ($user, $password) {
            $this->resetPassword($user, $password);
        }
        );
        if ($response == Password::PASSWORD_RESET) {
            return response()->json(['status'=>'200','message'=>'Success! Password reset successfully'],200);
        } else {
            return response()->json(['status'=>'202','message'=>'Failed! Password is not reset']);
        }

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        /* return $response == Password::PASSWORD_RESET
             ? $this->sendResetResponse($response)
             : $this->sendResetFailedResponse($request, $response);*/
    }
}
