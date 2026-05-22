<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\FarmProfile;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use App\Notifications\AdminNewTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TransactionController extends Controller
{
    /**
     * Vendor's purchase history — every transaction they have submitted.
     */
    public function purchases()
    {
        $user = Auth::user();

        if ($user->role_id != 3 || !$user->vendorProfile) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $transactions = Transaction::where('vendor_id', $user->vendorProfile->id)
            ->with([
                'farmerProfile.user:id,name,phone,address',
                'items.product:id,title,unit_price,currency',
            ])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($tx) => [
                'transaction_code' => $tx->transaction_code,
                'status'           => $tx->status,
                'farm_name'        => $tx->farmerProfile->farm_name,
                'farm_owner_name'  => $tx->farmerProfile->user->name,
                'farm_contact'     => $tx->farmerProfile->user->phone,
                'farm_address'     => $tx->farmerProfile->user->address,
                'products'         => $tx->items->map(fn ($item) => [
                    'name'       => $item->product->title,
                    'quantity'   => $item->quantity,
                    'unit_price' => $item->product->unit_price,
                    'currency'   => $item->product->currency,
                ]),
                'total_amount'  => $tx->total_amount,
                'currency'      => $tx->currency,
                'purchase_date' => $tx->created_at->toDateTimeString(),
            ]);

        return response()->json(['purchases' => $transactions]);
    }

    public function initiateTransaction(Request $request)
    {
        $request->validate([
            'qr_code_string'     => 'required|string',
            'total_amount'       => 'required|numeric|min:1',
            'currency'           => 'required|in:CDF,USD',
            'items'              => 'required|array',
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
            'vendor_id'        => $user->vendorProfile->id,
            'farmer_id'        => $farmerProfile->id,
            'total_amount'     => $request->total_amount,
            'currency'         => $request->currency,
            'status'           => 'PENDING',
        ]);

        foreach ($request->items as $item) {
            TransactionItem::create([
                'transaction_id' => $transaction->id,
                'product_id'     => Product::find($item['product_id'])->id,
                'quantity'       => $item['quantity'],
            ]);
        }

        // Notify all admins of the new transaction
        $transaction->load('vendorProfile.user');
        User::where('role_id', 1)->each(function ($admin) use ($transaction) {
            $admin->notify(new AdminNewTransaction($transaction));
        });

        return response()->json([
            'message'          => 'Transaction submitted successfully. Waiting for admin approval.',
            'transaction_code' => $transaction->transaction_code,
            'amount_to_pay'    => $transaction->total_amount,
        ], 201);
    }
}
