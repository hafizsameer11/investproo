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
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Fetch all transactions related to the authenticated user
        $transactions = Transaction::with(['deposit', 'withdrawal']) // Optional: eager load related models
                            ->where('user_id', $user->id)
                            ->get();
        // dd($transactions);  
        return response()->json([
            'status' => true,
            'message' => 'User transactions retrieved successfully.',
            'data' => $transactions
        ]);
    }


public function index()
{
    $transactions = Transaction::with(['user', 'deposit', 'withdrawal'])
                        ->orderBy('created_at', 'desc')
                        ->get();

    return view('admin.pages.transaction', compact('transactions'));
}
}