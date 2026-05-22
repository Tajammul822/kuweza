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
use App\Models\VendorProfile;

class AuthController extends Controller
{


    public function register(Request $request)
    {
  
        $fields = $request->validate([
            'name' => 'nullable',
            'email' => 'nullable|email',
            'role_id' => 'required|in:1,2,3',
            'password' => 'required|confirmed',
            'phone' => 'required|unique:users,phone' 
        ], [
            'phone.unique' => 'This phone number is already registered with another account.'
        ]);


        if (in_array($request->role_id, [2, 3])) {
            $metaValidation = [
                'street' => 'nullable',
                'village' => 'nullable',
                'region' => 'nullable',
                'bank_name' => 'nullable',
                'account_number' => 'nullable',
                'id_image' => 'required',
                'id_number' => 'required',
            ];


            if ($request->role_id == 2) {
                $metaValidation['farm_name'] = 'required';
            } elseif ($request->role_id == 3) {
                $metaValidation['shop_name'] = 'nullable';
                $metaValidation['market_location'] = 'nullable';
                $metaValidation['payment_provider'] = 'required';
            }

            $request->validate($metaValidation);
        }


        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'password' => Hash::make($fields['password']),
            'role_id' => $fields['role_id'],
            'phone' => $fields['phone']
        ]);


        if (in_array($user->role_id, [2, 3])) {

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

            if ($user->role_id == 2) {

                $qrCodeString = (string) Str::uuid();

                FarmProfile::create([
                    'user_id' => $user->id,
                    'farm_name' => $request->farm_name,
                    'qr_code_string' => $qrCodeString
                ]);
            } elseif ($user->role_id == 3) {

                VendorProfile::create([
                    'user_id' => $user->id,
                    'shop_name' => $request->shop_name,
                    'market_location' => $request->market_location,
                    'payment_provider' => $request->payment_provider
                ]);
            }
        }

        return response()->json([
            'user' => $user,
            'message' => 'User registered successfully'
        ], 201);
    }

    public function getProfile(Request $request)
    {
        $user = $request->user();
        $user->load('vendorProfile', 'farmerProfile');
        $meta = UserMeta::where('user_id', $user->id)->first();

        return response()->json([
            'name'       => $user->name,
            'phone'      => $user->phone,
            'farm_name'  => $user->farmerProfile?->farm_name,
            'shop_name'  => $user->vendorProfile?->shop_name,
            'street'     => $meta?->street,
            'village'    => $meta?->village,
            'region'     => $meta?->region,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $rules = [
            'name'    => 'required|string|max:255',
            'street'  => 'nullable|string|max:255',
            'village' => 'nullable|string|max:255',
            'region'  => 'nullable|string|max:255',
        ];

        if ($user->role_id == 2) {
            $rules['farm_name'] = 'required|string|max:255';
        }

        if ($user->role_id == 3) {
            $rules['shop_name'] = 'nullable|string|max:255';
        }

        $data = $request->validate($rules);

        // Update user name
        $user->update(['name' => $data['name']]);

        // Upsert address meta (street, village, region)
        UserMeta::updateOrCreate(
            ['user_id' => $user->id],
            [
                'street'  => $data['street']  ?? null,
                'village' => $data['village'] ?? null,
                'region'  => $data['region']  ?? null,
            ]
        );

        // Role-specific profile update
        if ($user->role_id == 2) {
            FarmProfile::where('user_id', $user->id)
                ->update(['farm_name' => $data['farm_name']]);
        }

        if ($user->role_id == 3 && isset($data['shop_name'])) {
            VendorProfile::where('user_id', $user->id)
                ->update(['shop_name' => $data['shop_name']]);
        }

        $user->load('vendorProfile', 'farmerProfile');
        $meta = UserMeta::where('user_id', $user->id)->first();

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user'    => [
                'name'       => $user->name,
                'phone'      => $user->phone,
                'farm_name'  => $user->farmerProfile?->farm_name,
                'shop_name'  => $user->vendorProfile?->shop_name,
                'street'     => $meta?->street,
                'village'    => $meta?->village,
                'region'     => $meta?->region,
            ],
        ]);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password'     => 'required|min:6|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect.',
            ], 422);
        }

        $user->update(['password' => Hash::make($request->new_password)]);

        return response()->json(['message' => 'Password changed successfully.']);
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

        $farmProfile = null;

        if ($user->role_id == 2) {
            $farmProfile = FarmProfile::where('user_id', $user->id)->first();
        }

        $token = $user->createToken($user->phone);

        return response()->json([
            'user' => $user,
            'farm_profile' => $farmProfile,
            'token' => $token->plainTextToken,
        ]);
    }
}
