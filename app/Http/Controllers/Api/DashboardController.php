<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\Investment;
use App\Models\InvestmentPlan;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Withdrawal;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function dashboard()
    {
        try {
            $userId = Auth::id();
            \Log::info('Dashboard request for user ID: ' . $userId);
            
            $balance = Wallet::where('user_id', $userId)->first();
            
            // Check if wallet exists, if not create one
            if (!$balance) {
                \Log::info('No wallet found for user ' . $userId . ', creating new wallet');
                $balance = Wallet::create([
                    'user_id' => $userId,
                    'withdrawal_amount' => 0,
                    'deposit_amount' => 0,
                    'profit_amount' => 0,
                    'bonus_amount' => 0,
                    'referral_amount' => 0,
                    'status' => 'active'
                ]);
            }
            
            // Debug: Log the wallet data
            \Log::info('Wallet data for user ' . Auth::id() . ':', [
                'deposit_amount' => $balance->deposit_amount ?? 0,
                'profit_amount' => $balance->profit_amount ?? 0,
                'bonus_amount' => $balance->bonus_amount ?? 0,
                'referral_amount' => $balance->referral_amount ?? 0,
                'withdrawal_amount' => $balance->withdrawal_amount ?? 0,
            ]);
            
            // Calculate total balance including deposit amount
            $total_balance = ($balance->deposit_amount ?? 0) + ($balance->profit_amount ?? 0) + ($balance->bonus_amount ?? 0) + ($balance->referral_amount ?? 0);
            
            \Log::info('Calculated total balance:', ['total_balance' => $total_balance]);
            
            $plan = Investment::where('user_id', Auth::id())
                ->where('status', 'active')
                ->count();
            $profit = Deposit::where('user_id', Auth::id())->get();
            $totalAmount = $profit->sum('amount');

            $daily_profit = round($totalAmount / 30, 2);

            // dd($daily_profit);
            $referral_bonus = $balance->bonus_amount + $balance->referral_amount;
            $withdrawal_amount = $balance->withdrawal_amount;
            return ResponseHelper::success([
                'total_balance' => $total_balance,
                'active_plans' => $plan,
                'daily_profit' => $daily_profit,
                'referral_bonus_earned' => $referral_bonus,
                'withdrawal_amount' => $withdrawal_amount,
            ], 'Dashboard data retrieved successfully');
        } catch (Exception $ex) {
            return ResponseHelper::error('User is not create' . $ex);
        }
    }

    public function about()
    {
        // Starting point
        $startDate = Carbon::parse('2025-08-11');

        // Current time
        $now = Carbon::now();

        // Get total days passed
        $daysPassed = $startDate->diffInDays($now);

        // Convert days to weeks (including fractions)
        $weeksPassed = $daysPassed / 7;

        // Base and increment values
        $baseAmount = 0;
        $incrementPerWeek = 500;

        $total = round($baseAmount + ($incrementPerWeek * $weeksPassed), 0);

        return response()->json([
            'Total Users' => $total,
            'Active Lans' => `$10,000`,

        ]);
    }

    public function index()
    {
        $total_users = User::count();
        $all_users = User::all();
        $active_users = User::where('status', 'active')->count();
        $total_withdrawal = Withdrawal::where('status', 'active')->count();
        $total_deposit = Deposit::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('amount');
        $active_withdrawal = Withdrawal::where('status', 'active')->count();
        $pending_withdrawal = Withdrawal::where('status', 'pending')->count();

        $approved_deposits = Deposit::where('status', 'active')->count();
        $pending_deposits = Deposit::where('status', 'pending')->count();
        return view('admin.index', compact('total_users', 'all_users', 'active_users', 'total_deposit', 'total_withdrawal', 'active_withdrawal', 'pending_withdrawal', 'approved_deposits', 'pending_deposits'));
    }
}
