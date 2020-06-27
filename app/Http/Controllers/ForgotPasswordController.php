<?php
//namespace App\Http\Controllers\Auth;
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
class ForgotPasswordController extends Controller
{
    use SendsPasswordResetEmails;
    public function __construct()
    {
        $this->middleware('guest');
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

        if ($request->wantsJson()) {
            if ($response == Password::PASSWORD_RESET) {
                return response()->json(['status'=>'200','message'=>'Success! Password reset successfully'],200);
            } else {
                return response()->json(['status'=>'202','message'=>'Failed! Password is not reset']);
            }
        }

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        return $response == Password::PASSWORD_RESET
            ? $this->sendResetResponse($response)
            : $this->sendResetFailedResponse($request, $response);
    }
}
