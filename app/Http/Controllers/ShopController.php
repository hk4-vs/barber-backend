<?php

namespace App\Http\Controllers;

use App\Models\ShopOwners;
use App\Models\ShopPhotos;
use App\Models\Shops;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\Services;

class ShopController extends Controller
{
    public function index()
    {
        $shops = Shops::all();
        foreach ($shops as $shop) {
            $shopImages =   ShopPhotos::where("shop_id", $shop->id)->get();
            $services = Services::where('shop_id', $shop->id)->get();
            $shop->shop_images = $shopImages;
            $shop->services = $services;
        }

        return response()->json($shops, 200);
    }

    public function show($id)
    {
        try {
            $shop = Shops::where('id', $id)->get()->first();
            $shopImages =   ShopPhotos::where("shop_id", $shop->id)->get();
            $shop->shop_images = $shopImages;
            return response()->json($shop, 200);
        } catch (\Exception $th) {
            return response()->json(['message' => 'SOMETHING WENT WRONG', 'status' => 'false', "error" => $th->getMessage()], 500);
        }
    }

    public function uploadShopImages(Request $request)
    {
        try {

            $user =  Auth::user();
            if (!$user) {
                return response()->json([
                    'message' => 'Unauthorized',
                    'status' => false,
                ], 401);
            }
            $validator = Validator::make($request->all(), [
                'images' => 'required|array',
                'images.*' => 'image|mimes:jpeg,png,jpg|max:2048',

            ]);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors(),
                    'status' => false,
                ], 400);
            }

            $shopOwner = ShopOwners::where('user_id', $user->id)->get()->first();
            $shop = Shops::where('shop_owner_id', $shopOwner->id)->get()->first();

            if ($request->hasFile('images')) {
                $images = $request->file('images');
                foreach ($images as $image) {
                    $shopPhotos = new ShopPhotos();
                    $shopImage = $image->store('public/shop_images');
                    $shopImagePath = Storage::url($shopImage);
                    $shopImageUrl = asset($shopImagePath);
                    $shopPhotos->image = $shopImageUrl;
                    $shopPhotos->shop_id = $shop->id;
                    $shopPhotos->save();
                }
                $imageUrls = ShopPhotos::where('shop_id', $shop->id)->get();
                return response()->json(['message' => 'Images uploaded successfully', "images" => $imageUrls, "status" => true], 200);
            }

            return response()->json(['error' => 'No images found'], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage(), "status" => false, "message" => 'An error occurred while uploading images'], 400);
        }
    }

    public function deleteImage($id)
    {
        try {
            $user =  Auth::user();
            if (!$user) {
                return response()->json([
                    'message' => 'Unauthorized',
                    'status' => false,
                ], 401);
            }
            $shopOwner = ShopOwners::where('user_id', $user->id)->get()->first();
            $shop = Shops::where('shop_owner_id', $shopOwner->id)->get()->first();
            $shopPhoto = ShopPhotos::where('shop_id', $shop->id)->get()->find($id);
            if ($shopPhoto == null || $shopPhoto == "" || $shopPhoto == [] || $shopPhoto == false) {
                return response()->json(['message' => 'Image not found', "status" => false], 404);
            }
            $isDelete = $shopPhoto->delete();
            // $shopPhoto->delete();
            return response()->json(['message' => 'Image deleted successfully', "status" => true, "delete" => $isDelete], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage(), "status" => false, "message" => 'An error occurred while deleting image'], 400);
        }
    }
}
