<?php

namespace App\Modules\Authentication\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Authentication\Http\Requests\ResetPasswordRequest;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class NewPasswordController extends Controller
{
    use ResetsPasswords;

    public function reset(ResetPasswordRequest $request)
    {
        $request->validate($request->rules());

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $status = $this->resetPassword($request, function ($user, $password) {
            $user->forceFill([
                'password' => bcrypt($password),
            ])->setRememberToken(\Illuminate\Support\Str::random(60));

            $user->save();
        });

        // If the password was successfully reset, we will clear the reset tokens and
        // return the proper response.
        return $status == \Illuminate\Support\Facades\Password::PASSWORD_RESET
                    ? response()->json(['message' => 'Password reset successfully'])
                    : response()->json(['message' => 'Invalid password reset token.'], 400);
    }
}
