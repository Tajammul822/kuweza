<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\FarmProfile;
use App\Models\Product;

class TransactionController extends Controller
{
    public function initiateTransaction(Request $request)
    {
        
        $request->validate([
            'qr_code_string' => 'required|string',
            'total_amount'   => 'required|numeric|min:1',
            'currency'       => 'required|in:CDF,USD',
            'items'          => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1',
        ]);

        $user = Auth::user();

        if ($user->role_id != 3 || !$user->vendorProfile) {
            return response()->json(['message' => 'Unauthorized. Only vendors can perform this action.'], 403);
        }

        
        $farmerProfile = FarmProfile::where('qr_code_string', $request->qr_code_string)->first();

        if (!$farmerProfile) {
            return response()->json(['message' => 'Invalid QR Code. Farmer not found.'], 404);
        }

        
        $transaction = Transaction::create([
            'transaction_code' => 'TRX-' . strtoupper(Str::random(8)),
            'vendor_id'  => $user->vendorProfile->id,
            'farmer_id'  => $farmerProfile->id,
            'total_amount' => $request->total_amount,
            'currency'   => $request->currency,
            'status'     => 'PENDING',
        ]);

        
        foreach ($request->items as $item) {
           
            $product = Product::find($item['product_id']);

            TransactionItem::create([
                'transaction_id' => $transaction->id,
                'product_id'     => $product->id,
                'quantity'       => $item['quantity'],
            ]);
        }

        return response()->json([
            'message' => 'Transaction request submitted successfully. Waiting for admin approval.',
            'transaction_code' => $transaction->transaction_code,
            'amount_to_pay' => $transaction->total_amount
        ], 201);
    }
}
