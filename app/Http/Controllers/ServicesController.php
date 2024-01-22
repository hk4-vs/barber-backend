<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Services;
use App\Models\ShopOwners;
use App\Models\Shops;
use Illuminate\Support\Facades\Validator;

class ServicesController extends Controller
{
    public function index()
    {
        try {
            $userauth = auth()->user();

            if (is_null($userauth)) {
                return response()->json(['message' => 'USER NOT FOUND.', 'status' => 'false'], 401);
            }

            $shop_owner = ShopOwners::where('user_id', $userauth->id)->first();
            $shop = Shops::where('shop_owner_id', $shop_owner->id)->first();
            $services = Services::where('shop_id', $shop->id)->get();

            return response()->json(['services' => $services, 'status' => 'true'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'SOMETHING WENT WRONG', 'status' => 'false'], 500);
        }
    }

    public function create(Request $request)
    {

        $validator =   Validator::make($request->all(), [
            'name' => 'required|string|unique:services',
            "price" => 'required|numeric',
            'description' => 'string',

        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
                "message" => "Please fill all the required fields"
            ]);
        }
        try {
            $userauth = auth()->user();

            if (is_null($userauth)) {
                return response()->json(['message' => 'USER NOT FOUND.', 'status' => 'false'], 401);
            }

            $shop_owner = ShopOwners::where('user_id', $userauth->id)->first();
            $shop = Shops::where('shop_owner_id', $shop_owner->id)->first();


            $service = Services::create([
                'name' => $request->name,
                'price' => $request->price,
                'description' => $request->description,
                'shop_id' => $shop->id,

            ]);
            return response()->json([
                'status' => true,
                'services' => $service,
                "message" => "Service created successfully"
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                "error" => $e->getMessage(),
                "message" => "Something went wrong",
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validator =   Validator::make($request->all(), [
                'name' => 'required|string|unique:services',
                'description' => 'string',
                "price" => 'required|numeric',

            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'errors' => $validator->errors(),
                    "message" => "Please fill all the required fields"
                ]);
            }
            $user = auth()->user();
            if (is_null($user)) {
                return response()->json(['message' => 'USER NOT FOUND.', 'status' => 'false'], 401);
            }

            $shop_owner = ShopOwners::where('user_id', $user->id)->first();
            $shop = Shops::where('shop_owner_id', $shop_owner->id)->first();

            $services = Services::where('shop_id', $shop->id)->get()->find($id);
            if (is_null($services)) {
                return response()->json(['message' => 'SERVICE NOT FOUND.', 'status' => 'false'], 401);
            }

            $services->update([
                'name' => $request->name,
                'price' => $request->price,
                'description' => $request->description,

            ]);

            return response()->json([
                'status' => true,
                "message" => "Service updated successfully",
                'service' => $services,
            ], 200);
        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                "error" => $e->getMessage(),
                "message" => "Something went wrong",
            ], 500);
        }
    }

    public function delete($id)
    {
        try {
            $user = auth()->user();
            if (is_null($user)) {
                return response()->json(['message' => 'USER NOT FOUND.', 'status' => 'false'], 401);
            }
            $shop_owner = ShopOwners::where('user_id', $user->id)->first();
            $shop = Shops::where('shop_owner_id', $shop_owner->id)->first();

            $services = Services::where('shop_id', $shop->id)->get()->find($id);
            if (is_null($services)) {
                return response()->json(['message' => 'SERVICE NOT FOUND.', 'status' => 'false'], 401);
            }
            $services->delete();
            return response()->json([
                'status' => true,
                'services' => $services,
                "message" => "Service delete successfully"
            ], 200);
        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                "error" => $e->getMessage(),
                "message" => "Something went wrong",
            ], 500);
        }
    }
}
