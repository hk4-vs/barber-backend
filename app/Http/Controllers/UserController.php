<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ShopOwners;
use App\Models\Shops;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpEmail;

class UserController extends Controller
{




    public function createUser(Request $request)
    {


        // Validate the request data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        // Return validation error response if validation fails
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
                "status" => false,
            ], 422);
        }

        try {
            // Generate OTP
            $otp = rand(100000, 999999);

            // Store the OTP and registration data in the database
            $verificationData = [
                'otp' => $otp,
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'verified' => false,
            ];

            DB::beginTransaction();

            // Insert the verification data into the database
            $verificationId = DB::table('otp_verifications')->insertGetId($verificationData);
            DB::commit();

            // Send OTP to user's email
            Mail::to($request->email)->send(new OtpEmail($otp));

            return response()->json([
                'message' => 'OTP sent to email',
                'verification_id' => $verificationId,
                'status' => true,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            // Handle error
            return response()->json([
                'message' => 'Error sending OTP',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateUserData(Request $request)
    {

        $user =  auth()->user();
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
                "status" => false,
            ], 422);
        }
        try {
            $isUpdate =   User::where('id', $user->id)->update([
                'name' => $request->name,
            ]);

            return response()->json(["user" => $user, "isUpdate" => $isUpdate, "status" => true], 200);
        } catch (\Exception  $e) {
            return response()->json([
                'message' => 'Server Error',
                'error' => $e->getMessage(),
                "status" => false,
            ]);
        }
    }





    public function verifyOTP(Request $request)
    {
        // Retrieve the verification ID from the request
        $verificationId = $request->input('verification_id');

        // Retrieve the verification data from the database based on the verification ID
        $verificationData = DB::table('otp_verifications')->where('id', $verificationId)->first();

        // Check if the verification data exists
        if (!$verificationData) {
            return response()->json([
                'message' => 'Invalid verification ID',
                'status' => false,
            ], 422);
        }

        // Verify the OTP provided by the user
        if ($request->otp != $verificationData->otp) {
            return response()->json([
                'message' => 'Invalid OTP',
                'status' => false,
            ], 422);
        }

        try {
            DB::beginTransaction();
            // Update the verification status to mark it as verified
            DB::table('otp_verifications')->where('id', $verificationId)->update(['verified' => true]);

            // Create the user using the registration data
            $user = User::create([
                'name' => $verificationData->name,
                'email' => $verificationData->email,
                'password' => $verificationData->password,
            ]);

            // Remove the verification data from the database
            DB::table('otp_verifications')->where('id', $verificationId)->delete();

            DB::commit();
            return response()->json([
                'message' => 'User registered successfully.',
                'user' => $user,
                "status" => true,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            // Handle error
            return response()->json([
                'message' => 'Error creating user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            $token = $request->user()->createToken($request->email)->plainTextToken;

            return response()->json([
                'message' => 'Login successful',
                'user' => $user,
                'user_type' => $user->type,
                'access_token' => $token,
                "status" => true,
            ], 200);
        }

        return response()->json([
            'message' => 'Invalid credentials',
            "status" => false,
        ], 401);
    }

    public function logout(Request $request)
    {
        try {
            if ($request->user()) {
                $request->user()->tokens()->delete();
            }

            return response()->json([
                'message' => 'Logged out successfully.',
                "status" => true,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Logged out faild',
                "error" => $e->getMessage(),
                "status" => false,
            ], 500);
        }
    }
}
