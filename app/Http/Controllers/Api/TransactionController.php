<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
     public function userTransactions()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            // Fetch all transactions related to the authenticated user
            $transactions = Transaction::where('user_id', $user->id)
                                ->orderBy('created_at', 'desc')
                                ->get()
                                ->map(function ($transaction) {
                                    return [
                                        'id' => $transaction->id,
                                        'type' => $transaction->type,
                                        'amount' => (float) $transaction->amount,
                                        'status' => $transaction->status,
                                        'description' => $transaction->description,
                                        'created_at' => $transaction->created_at,
                                        'updated_at' => $transaction->updated_at,
                                    ];
                                });

            return response()->json([
                'status' => 'success',
                'message' => 'User transactions retrieved successfully.',
                'data' => $transactions
            ]);
        } catch (\Exception $e) {
            \Log::error('Transaction error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve transactions: ' . $e->getMessage()
            ], 500);
        }
    }


public function index()
{
    $transactions = Transaction::with(['user', 'deposit', 'withdrawal'])
                        ->orderBy('created_at', 'desc')
                        ->get();

    return view('admin.pages.transaction', compact('transactions'));
}
}