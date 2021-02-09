<?php

namespace App\Http\Controllers\Therapist\Auth;

use App\Http\Controllers\Controller as BaseController;
use App\Traits\ResetsPasswords;
use Illuminate\Auth\Passwords\PasswordBrokerManager;
<<<<<<< HEAD
use Illuminate\Http\Request;
use APp\Therapist;
=======
use Illuminate\Support\Facades\Password;
use Illuminate\Http\Request;
use App\Therapist;
use Illuminate\Support\Facades\Validator;
>>>>>>> a1af10094a4c25489d0fb294eb5811e66c43dd85

class ForgotPasswordController extends BaseController
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use ResetsPasswords;

<<<<<<< HEAD
=======
    public $errorMsg = [
        'swr' => "Something went wrong! Please try again after an hour."
    ];

    public $successMsg = [
        'password.reset' => "Password reset successfully! Check your mailbox."
    ];

>>>>>>> a1af10094a4c25489d0fb294eb5811e66c43dd85
    // 1. Send reset password email
    public function generateResetToken(Request $request)
    {
        $this->validate($request, ['email' => 'required|email']);

        // Send password reset to the user with this email address
        $response = $this->broker()->sendResetLink(
            $request->only('email')
        );

        return $response == Password::RESET_LINK_SENT
            ? response()->json(true)
            : response()->json(false);
    }

    // 2. Reset Password
    public function resetPassword(Request $request)
    {
        // Check input is valid
        $rules = [
            'token'    => 'required',
            'name'     => 'required|string',
            'password' => 'required|confirmed|min:6',
        ];
        $this->validate($request, $rules);

        // Reset the password
        $response = $this->broker()->reset(
        $this->credentials($request),
            function ($user, $password) {
                $user->password = app('hash')->make($password);
                $user->save();
            }
        );

        return $response == Password::PASSWORD_RESET
            ? response()->json(true)
            : response()->json(false);
    }

    /**
     * Get the password reset credentials from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        return $request->only('username', 'password', 'password_confirmation', 'token');
    }

    /**
     * Get the broker to be used during password reset.
     *
     * @return \Illuminate\Contracts\Auth\PasswordBroker
     */
    public function broker()
    {
        $passwordBrokerManager = new PasswordBrokerManager(app());

        return $passwordBrokerManager->broker();
    }

<<<<<<< HEAD
    protected function sendResetLinkResponse(Request $request, $response)
    {
        $user  = [];
        $model = new Therapist();
        $email = $request->get('email', false);

        return $this->returnSuccess(trans($response));
    }

    protected function sendResetLinkFailedResponse(Request $request, $response)
    {
        return $this->returnError(trans($response));
=======
    protected function validateSendResetLinkEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email', 'exists:' . Therapist::getTableName() . ',email']
        ]);

        if ($validator->fails()) {
            return $this->returnError($validator->errors()->first());
        }

        return $this->returnSuccess(__("Success"));
    }

    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postReset(Request $request)
    {
        return $this->getReset($request);
>>>>>>> a1af10094a4c25489d0fb294eb5811e66c43dd85
    }
}
