<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function store(Request $request)
    {
        $fields = $request->validate([
            'title' => 'required|string|max:255',
            'featured_image' => 'nullable|string',
            'description' => 'nullable|string',
            'unit_price' => 'nullable|numeric|min:0',
            'currency' => 'nullable',
            'stock_quantity' => 'nullable',
            'is_available' => 'nullable'
        ]);


        $user = Auth::user();


        if (!$user->farmerProfile) {
            return response()->json([
                'message' => 'User does not have a farmer profile. Please complete registration.'
            ], 403);
        }

        $imagePath = null;

        if (!empty($fields['featured_image'])) {
            $base64Image = $fields['featured_image'];

            if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
                $imageType = strtolower($type[1]);

                if (!in_array($imageType, ['jpg', 'jpeg', 'png'])) {
                    return response()->json([
                        'message' => 'Invalid image type'
                    ], 422);
                }

                $base64Image = substr($base64Image, strpos($base64Image, ',') + 1);
                $base64Image = base64_decode($base64Image);

                if ($base64Image === false) {
                    return response()->json([
                        'message' => 'Base64 decode failed'
                    ], 422);
                }

                $destinationPath = public_path('assets/images/product');

                if (!File::exists($destinationPath)) {
                    File::makeDirectory($destinationPath, 0755, true);
                }

                $fileName = time() . '_' . Str::random(10) . '.' . $imageType;

                file_put_contents($destinationPath . '/' . $fileName, $base64Image);

                $imagePath = 'assets/images/product/' . $fileName;
            } else {
                return response()->json([
                    'message' => 'Invalid base64 image format'
                ], 422);
            }
        }


        $product = Product::create([
            'user_id' => $user->id,
            'farm_id' => $user->farmerProfile->id,
            'title' => $fields['title'],
            'featured_image' => $imagePath,
            'description' => $fields['description'] ?? null,
            'unit_price' => $fields['unit_price'],
            'currency' => $fields['currency'],
            'stock_quantity' => $fields['stock_quantity'],
            'is_available' => $fields['is_available'] ?? true,
        ]);


        return response()->json([
            'message' => 'Product created successfully',
            'product' => $product
        ], 201);
    }

    public function index()
    {
        $user = Auth::user();

        if (!$user->farmerProfile) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $products = Product::where('farm_id', $user->farmerProfile->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($products);
    }
}
