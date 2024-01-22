<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\ResetPasswordEmail;
use Illuminate\Support\Facades\Password;

use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class ForgotPasswordController extends Controller
{
    public function sendResetLink(Request $request)
    {
        // try {
        //     $email = $request->input('email');

        //     // Check if the email exists in the users table
        //     $user = DB::table('users')->where('email', $email)->first();
        //     if (!$user) {
        //         return response()->json(['message' => 'Email not found.', "status" => false], 404);
        //     }

        //     // Generate a unique token for password reset
        //     $token = Str::random(60);

        //     DB::beginTransaction();

        //     // Save the token and email in the password_resets table
        //     // DB::table('password_resets')->updateOrInsert([
        //     //     'email' => $email,
        //     //     'token' => $token,
        //     //     'created_at' => Carbon::now()
        //     // ]);
        //     if (DB::table('password_resets')->where('email', $email)->exists()) {
        //         // Update the record
        //         DB::table('password_resets')
        //             ->where('email', $email)
        //             ->update([
        //                 'token' => $token,
        //                 'created_at' => Carbon::now()
        //             ]);
        //     } else {
        //         // Insert a new record
        //         DB::table('password_resets')->insert([
        //             'email' => $email,
        //             'token' => $token,
        //             'created_at' => Carbon::now()
        //         ]);
        //     }
        //     DB::commit();

        //     // Send the reset password link to the user's email
        //     Mail::to($email)->send(new ResetPasswordEmail($token));

        //     return response()->json(['message' => 'Reset password link sent to your email.', "status" => true], 200);
        // } catch (\Exception $e) {
        //     DB::rollBack();
        //     return response()->json(['message' => 'Failed to send reset password link.', "status" => false, "error" => $e->getMessage()], 500);
        // }

        {

            $validator =  Validator::make($request->all(), [
                'email' => 'required|email|exists:users',
            ]);
            if ($validator->fails()) {
                return response()->json(['message' => "Email is Invalid or Doesn't Exist", "status" => false, "errors" => $validator->errors()], 422);
            }

            $response = $this->broker()->sendResetLink(
                $request->only('email')
            );

            return response()->json(['message' => trans($response)]);
        }
    }
    protected function broker()
    {
        return Password::broker();
    }
}
