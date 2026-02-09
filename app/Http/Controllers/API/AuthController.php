<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\UserMeta;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use App\Models\FarmProfile;

class AuthController extends Controller
{
    public function register(Request $request)
    {

        $fields = $request->validate([
            'name' => 'nullable',
            'email' => 'required|email|unique:users',
            'role_id' => 'required',
            'password' => 'required|confirmed',
            'phone' => 'required'
        ]);


        if ($request->role_id == 2) {
            $request->validate([
                'street' => 'nullable',
                'village' => 'nullable',
                'region' => 'nullable',
                'bank_name' => 'nullable',
                'account_number' => 'nullable',
                'id_image' => 'required',
                'id_number' => 'required',
                'farm_name' => 'required',
            ]);
        }


        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'password' => Hash::make($fields['password']),
            'role_id' => $fields['role_id'],
            'phone' => $fields['phone']
        ]);

        // If farmer, save meta + base64 image
        if ($user->role_id == 2) {
            $imagePath = null;

            if ($request->id_image) {

                $base64Image = $request->id_image;

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

                    $destinationPath = public_path('assets/images/id');

                    if (!File::exists($destinationPath)) {
                        File::makeDirectory($destinationPath, 0755, true);
                    }

                    $fileName = time() . '_' . Str::random(10) . '.' . $imageType;

                    file_put_contents($destinationPath . '/' . $fileName, $base64Image);

                    $imagePath = 'assets/images/id/' . $fileName;
                }
            }

            UserMeta::create([
                'user_id' => $user->id,
                'street' => $request->street,
                'village' => $request->village,
                'region' => $request->region,
                'bank_name' => $request->bank_name,
                'account_number' => $request->account_number,
                'id_image' => $imagePath,
                'id_number' => $request->id_number,
            ]);

            $qrCodeString = (string) Str::uuid();

            FarmProfile::create([
                'user_id' => $user->id,
                'farm_name' => $request->farm_name,
                'qr_code_string' => $qrCodeString
            ]);
        }

        $farmerMeta = null;
        $farmProfile = null;

        if ($user->role_id == 2) {
            $farmerMeta = UserMeta::where('user_id', $user->id)->first();
            $farmProfile = FarmProfile::where('user_id', $user->id)->first();
        }

        return response()->json([
            'user' => $user,
            'farmer_meta' => $farmerMeta,
            'farm_profile' => $farmProfile,
            'message' => 'User registered successfully'
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'phone' => 'required',
            'password' => 'required',
        ]);

        $user = User::where('phone', $request->phone)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'The provided credentials are incorrect.',
            ], 401);
        }


        $token = $user->createToken($user->phone);

        return response()->json([
            'user' => $user,
            'token' => $token->plainTextToken,
        ]);
    }
}
