<?php

namespace App\Http\Controllers;

use App\Models\Services;
use App\Models\SheetBookingModel;
use App\Models\Shops;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SheetBookingModelController extends Controller
{
    public function create(Request $request)
    {

        $validator =   Validator::make($request->all(), [
            "date"   => "required|date|after_or_equal:today|date_format:Y-m-d",
            "time" => "required|date_format:H:i|time_after_now",
            "shop" => "required",
            "service" => "required",
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
                "message" => "Please fill all the required fields"

            ], 401);
        }
        try {
            $user = auth()->user();
            if (is_null($user)) {
                return response()->json([
                    'status' => false,
                    "message" => "user not found",
                ], 401);
            }

            $service = Services::where("shop_id", $request->shop)->where("id", $request->service)->first();
            if (is_null($service)) {
                return response()->json([
                    'status' => false,
                    "message" => "service not found",
                ], 401);
            }


            $booking =  SheetBookingModel::create([
                "user_id" => $user->id,
                "date" => $request->date,
                "time" => $request->time,
                "user_name" => $user->name,
                "shop_id" => $request->shop,
                "service_id" => $request->service,
            ]);
            return response()->json([
                'status' => true,
                'booking' => $booking,
                "message" => "Booking created successfully",
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                "message" => "Somthing went wrong",
                "error" => $e->getMessage(),
            ], 500);
        }
    }
}
