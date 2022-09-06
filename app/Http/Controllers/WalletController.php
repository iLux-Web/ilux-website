<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Wallet;
use App\Models\User;


class WalletController extends Controller
{
    public static function showBalance()
    {
        $bal = Wallet::where('user_id', Auth::user()->id)->value('balance');
        return number_format($bal, 2, '.', ',');
    }

    public function add(Request $request)
    {
        // $this->authorize('update', Wallet::where('user_id', $user_id));

        try {
            if ($request->adminKey != env('ADMIN_KEY')) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid key'
                ]);
            }

            if (!User::where('id', $request->user_id)->exists()) {
                return response()->json([
                    'status' => false,
                    'message' => 'User does not exist'
                ]);
            }

            $wallet = Wallet::where('user_id', $request->user_id);

            if (!$wallet && $request->adminKey != env('ADMIN_KEY')) {
                return response()->json([
                    'status' => false,
                    'message' => 'Wallet does not exist'
                ]);
            }

            $wallet->increment('balance', $request->amount);

            return response()->json([
                'status' => true,
                'message' => "Successfully added P" . $request->amount . " to " . User::where('id', $request->user_id)->value('username'),
                'balance' => $wallet->value('balance')
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function deduct(Request $request)
    {
        try {
            if (!User::where('id', $request->user_id)->exists()) {
                return response()->json([
                    'status' => false,
                    'message' => 'User does not exist'
                ]);
            }

            $wallet = Wallet::where('user_id', $request->user_id);

            if (!$wallet) {
                return response()->json([
                    'status' => false,
                    'message' => 'Wallet does not exist'
                ]);
            }

            $wallet->decrement('balance', $request->amount);

            return response()->json([
                'status' => true,
                'message' => "Successfully deducted P" . $request->amount . " to " . User::where('id', $request->user_id)->value('username'),
                'balance' => $wallet->value('balance')
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }
}
