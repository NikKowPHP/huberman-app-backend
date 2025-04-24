<?php

namespace App\Modules\Authentication\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;

class ForgotPasswordController extends Controller
{
    use SendsPasswordResetEmails;

    /**
     * Get the response for a successful password reset link request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     * @return \Illuminate\Http\JsonResponse
     */
    protected function sendResetLinkResponse(\Illuminate\Http\Request $request, $response)
    {
        return response()->json(['message' => $response], 200);
    }

    /**
     * Get the response for a failed password reset link request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     * @return \Illuminate\Http\JsonResponse
     */
    protected function sendResetLinkFailedResponse(\Illuminate\Http\Request $request, $response)
    {
        return response()->json(['email' => [$response]], 422);
    }
}
