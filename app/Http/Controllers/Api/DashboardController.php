<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\ClaimedAmount;
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
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
public function dashboard()
{
    try {
        $userId = Auth::id();
        if (!$userId) {
            return ResponseHelper::error('Unauthorized', 401);
        }

        Log::info('Dashboard request for user ID: ' . $userId);

        // Ensure wallet exists
        $wallet = Wallet::firstOrCreate(
            ['user_id' => $userId],
            [
                'withdrawal_amount' => 0,
                'deposit_amount'    => 0,
                'profit_amount'     => 0,
                'bonus_amount'      => 0,
                'referral_amount'   => 0,
                'status'            => 'active',
            ]
        );

        // Normalize numeric fields
        $deposit   = (float) ($wallet->deposit_amount   ?? 0);
        $profitAmt = (float) ($wallet->profit_amount    ?? 0);
        $bonus     = (float) ($wallet->bonus_amount     ?? 0);
        $referral  = (float) ($wallet->referral_amount  ?? 0);
        $withdrawn = (float) ($wallet->withdrawal_amount?? 0); // informational

        Log::info('Wallet data for user ' . $userId . ':', [
            'deposit_amount'    => $deposit,
            'profit_amount'     => $profitAmt,
            'bonus_amount'      => $bonus,
            'referral_amount'   => $referral,
            'withdrawal_amount' => $withdrawn,
        ]);

        // Pending withdrawals (to reserve funds)
        $pendingWithdrawals = (float) Withdrawal::where('user_id', $userId)
            ->where('status', 'pending')
            ->sum('amount');

        // Base total = sum of credited buckets (DO NOT add estimates)
        $total_balance_raw = $deposit + $profitAmt + $bonus + $referral;

        // Available = total - pending
        $available_balance = max(0, $total_balance_raw - $pendingWithdrawals);

        // Active plans count
        $activePlansCount = Investment::where('user_id', $userId)
            ->where('status', 'active')
            ->count();

        // ---- PROFIT METRICS FROM CLAIMED AMOUNTS ----
        // Today’s profit (claimed today only)
        $todaysProfit = (float) ClaimedAmount::where('user_id', $userId)
            ->whereDate('created_at', Carbon::today())
            ->sum('amount');

        // Lifetime claimed profit (all-time)
        $totalProfitEarned = (float) ClaimedAmount::where('user_id', $userId)
            ->sum('amount');

        // Round for presentation (keep raw if you prefer)
        $available_balance = round($available_balance, 2);
        $total_balance_raw = round($total_balance_raw, 2);
        $todaysProfit      = round($todaysProfit, 2);
        $totalProfitEarned = round($totalProfitEarned, 2);
        $profitAmt         = round($profitAmt, 2);

        Log::info('Dashboard computed:', [
            'available_balance'   => $available_balance,
            'total_balance_raw'   => $total_balance_raw,
            'pending_withdrawals' => $pendingWithdrawals,
            'active_plans'        => $activePlansCount,
            'todays_profit'       => $todaysProfit,
            'profit_amount'       => $profitAmt,
            'total_profit_earned' => $totalProfitEarned,
        ]);

        return ResponseHelper::success([
            // balances
            'total_balance'       => $available_balance,      // spendable now (after holding pending withdrawals)
            'total_balance_raw'   => $total_balance_raw,      // before pending reservations
            'pending_withdrawals' => round($pendingWithdrawals, 2),

            // plans
            'active_plans'        => $activePlansCount,

            // profit KPIs
            'profit_amount'       => $profitAmt,              // current profit bucket in wallet
            'todays_profit'       => $todaysProfit,           // claimed today via claimed_amounts
            'total_profit_earned' => $totalProfitEarned,      // lifetime claimed via claimed_amounts

            // bonuses
            'referral_bonus_earned' => round($bonus + $referral, 2),

            // historical total withdrawn (info only)
            'withdrawal_amount'   => round($withdrawn, 2),
        ], 'Dashboard data retrieved successfully');

    } catch (Exception $ex) {
        Log::error('Dashboard error', ['e' => $ex]);
        return ResponseHelper::error('Failed to load dashboard: ' . $ex->getMessage(), 500);
    }
}


    public function about()
    {
        try {
            // Starting point
            $startDate = Carbon::parse('2025-08-11');

            // Current time
            $now = Carbon::now();

            // Get total days passed
            $daysPassed = $startDate->diffInDays($now);

            // Convert days to weeks (including fractions)
            $weeksPassed = $daysPassed / 7;

            // Base and increment values
            $baseAmount = 500; // Start from 500 users
            $incrementPerWeek = 50; // Add 50 users per week

            $total_users = round($baseAmount + ($incrementPerWeek * $weeksPassed), 0);

            // Get real active investments count
            $active_investments = Investment::where('status', 'active')->count();

            return ResponseHelper::success([
                'total_users' => $total_users,
                'active_plans' => (string)$active_investments,
            ], 'About data retrieved successfully');
        } catch (Exception $ex) {
            Log::error('About data error: ' . $ex->getMessage());
            return ResponseHelper::error('Failed to retrieve about data: ' . $ex->getMessage());
        }
    }

    public function index()
    {
        $total_users = User::count();
        $all_users = User::all();
        $active_users = User::where('status', 'active')->count();
        $total_withdrawal = Withdrawal::where('status', 'active')->count();
        $total_deposit = Deposit::sum('amount');
        $active_withdrawal = Withdrawal::where('status', 'active')->count();
        $pending_withdrawal = Withdrawal::where('status', 'pending')->count();

        $approved_deposits = Deposit::where('status', 'active')->count();
        $pending_deposits = Deposit::where('status', 'pending')->count();

        return view('admin.index', compact('total_users', 'all_users', 'active_users', 'total_deposit', 'total_withdrawal', 'active_withdrawal', 'pending_withdrawal', 'approved_deposits', 'pending_deposits'));
    }
}
