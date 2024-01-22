<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ShopOwners;
use App\Models\ShopPhotos;
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

class ShopOwnerController extends Controller
{
    public function createShopOwner(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',

            // shop owner details
            "phone" => "required|string|max:11",
            "gender" => "required|in:male,female,other",
            "address" => "required|string",
            "owner_photo" => "required|image|mimes:jpeg,png,jpg|max:2048",

            // shop details
            "shop_name" => "required|string",
            "shop_state" => "required|string",
            "shop_city" => "required|string",
            "pincode" => "required|string",
            "shop_address" => "required|string",
            "shop_photo" => "required|image|mimes:jpeg,png,jpg|max:2048",
        ]);

        if ($validator->fails()) {
            // Handle validation errors
            return response()->json([
                'errors' => $validator->errors(),
            ], 400);
        }
        $ownerImage = $request->file('owner_photo')->store('public/owner_photos');
        $ownerImagePath = Storage::url($ownerImage);
        $ownerImageUrl = asset($ownerImagePath);
        $shopImage = $request->file('shop_photo')->store('public/shop_photos');
        $shopImagePath = Storage::url($shopImage);
        $shopImageUrl = asset($shopImagePath);

        try {
            // Generate OTP
            $otp = rand(100000, 999999);

            // Store the OTP and registration data in the database
            $verificationData = [
                'otp' => $otp,
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'phone' => $request->phone,
                'gender' => $request->gender,
                'address' => $request->address,
                'owner_photo' => $ownerImageUrl,
                'shop_name' => $request->shop_name,
                'shop_state' => $request->shop_state,
                'shop_city' => $request->shop_city,
                'pincode' => $request->pincode,
                'shop_address' => $request->shop_address,
                'shop_photo' => $shopImageUrl,
            ];

            DB::beginTransaction();

            // Insert the verification data into the database
            $verificationId = DB::table('un_auth_owners')->insertGetId($verificationData);
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
                'message' => 'Error creating data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function verifyOTP(Request $request)
    {
        // Retrieve the verification ID from the request
        $verificationId = $request->input('verification_id');

        // Retrieve the verification data from the database based on the verification ID
        $verificationData = DB::table('un_auth_owners')->where('id', $verificationId)->first();

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
            DB::table('un_auth_owners')->where('id', $verificationId)->update(['verified' => true]);

            // Create the user using the registration data
            $user = User::create([
                'name' => $verificationData->name,
                'email' => $verificationData->email,
                'password' => $verificationData->password,
                'type' => 'shopOwner',
            ]);

            $shopOwner = ShopOwners::create([
                'user_id' => $user->id,
                'phone' => $verificationData->phone,
                'gender' => $verificationData->gender,
                'address' => $verificationData->address,
                'owner_photo' => $verificationData->owner_photo,
            ]);

            $shop = Shops::create([
                'shop_owner_id' => $shopOwner->id,
                'shop_name' => $verificationData->shop_name,
                'shop_state' => $verificationData->shop_state,
                'shop_city' => $verificationData->shop_city,
                'pincode' => $verificationData->pincode,
                'shop_address' => $verificationData->shop_address,
                'shop_photo' => $verificationData->shop_photo,
            ]);

            // Remove the verification data from the database
            DB::table('un_auth_owners')->where('id', $verificationId)->delete();

            DB::commit();
            return response()->json([
                'message' => 'User registered successfully.',
                'user' => $user,
                "shop" => $shop,
                "shopOwner" => $shopOwner,
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

    public function updateShopOwner(Request $request)
    {
        $user =  auth()->user();

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            "phone" => "string|max:11",
            "address" => "string",
            "owner_photo" => "image|mimes:jpeg,png,jpg|max:2048",
            "shop_name" => "string",
            "shop_address" => "string",
            "shop_photo" => "image|mimes:jpeg,png,jpg|max:2048",
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
                "status" => false,
            ], 422);
        }

        try {
            $shopOwner = ShopOwners::where('user_id', $user->id)->first();
            return response()->json($shopOwner, 200);

            $ownerImage = $request->file('owner_photo')->store('public/owner_photos');
            $ownerImagePath = Storage::url($ownerImage);
            $ownerImageUrl = asset($ownerImagePath);
            $shopImage = $request->file('shop_photo')->store('public/shop_photos');
            $shopImagePath = Storage::url($shopImage);
            $shopImageUrl = asset($shopImagePath);
            // DB::beginTransaction();
        } catch (\Exception $e) {

            // Handle error
            return response()->json([
                'message' => 'Error updating data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getShopOwnerProfile(Request $request)
    {

        $user =  Auth::user();
        if (!$user) {
            return response()->json([
                'message' => 'User not found',
                'status' => false,
            ], 404);
        }

        $shopOwner = ShopOwners::where('user_id', $user->id)->first();
        $shopDetails = Shops::where('shop_owner_id', $shopOwner->id)->first();
        $shopImages =  ShopPhotos::where('shop_id', $shopDetails->id)->get();
        $response = ["shopOwner" => $shopOwner, "shopDetails" => $shopDetails, "shopImages" => $shopImages, "user" => $user];
        return response()->json($response, 200);
    }
}
