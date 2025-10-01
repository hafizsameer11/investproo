<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\ResponseHelper;
use App\Models\Investment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LoyaltyBoostController extends Controller
{
    public function getLoyaltyBoost(Request $request)
    {
        try {
            $userId = Auth::id();
            
            // Get user's latest active investment
            $latestInvestment = Investment::where('user_id', $userId)
                ->where('status', 'active')
                ->orderBy('created_at', 'desc')
                ->first();
            
            if (!$latestInvestment) {
                return ResponseHelper::success([
                    'has_active_investment' => false,
                    'message' => 'No active investment found',
                    'loyalty_boost' => 0,
                    'penalty_amount' => 0,
                    'days_invested' => 0,
                    'days_remaining' => 0,
                    'can_withdraw_without_penalty' => false,
                    'loyalty_bonus_available' => false,
                ], 'Loyalty boost data retrieved');
            }
            
            $investmentStartDate = Carbon::parse($latestInvestment->start_date);
            $investmentEndDate = Carbon::parse($latestInvestment->end_date);
            $currentDate = Carbon::now();
            
            // Calculate days invested (ensure positive value)
            $daysInvested = max(0, $investmentStartDate->diffInDays($currentDate));
            $daysRemaining = max(0, $currentDate->diffInDays($investmentEndDate, false));
            
            // Check if user has been invested for at least 30 days
            $has30DaysInvestment = $daysInvested >= 30;
            
            // Calculate loyalty boost (15% if invested for 30+ days)
            $loyaltyBoostPercentage = $has30DaysInvestment ? 15 : 0;
            
            // Calculate penalty (50% of profit if withdrawing before completion)
            $penaltyAmount = 0;
            $canWithdrawWithoutPenalty = $daysRemaining <= 0; // Can withdraw without penalty only after completion
            
            if (!$canWithdrawWithoutPenalty) {
                // Calculate 50% penalty of current profit amount from wallet
                $userWallet = \App\Models\Wallet::where('user_id', $userId)->first();
                $currentProfit = $userWallet ? $userWallet->profit_amount : 0;
                $penaltyAmount = $currentProfit * 0.5;
            }
            
            // Check if loyalty bonus is available (30+ days invested and not withdrawn recently)
            $lastWithdrawal = \App\Models\Withdrawal::where('user_id', $userId)
                ->whereIn('status', ['active', 'pending'])
                ->where('created_at', '>=', $currentDate->subDays(30))
                ->first();
            
            $loyaltyBonusAvailable = $has30DaysInvestment && !$lastWithdrawal;
            
            return ResponseHelper::success([
                'has_active_investment' => true,
                'investment_id' => $latestInvestment->id,
                'investment_amount' => $latestInvestment->amount,
                'investment_start_date' => $latestInvestment->start_date,
                'investment_end_date' => $latestInvestment->end_date,
                'days_invested' => $daysInvested,
                'days_remaining' => max(0, $daysRemaining),
                'loyalty_boost' => $loyaltyBoostPercentage,
                'loyalty_boost_percentage' => $loyaltyBoostPercentage,
                'penalty_amount' => $penaltyAmount,
                'penalty_percentage' => 50,
                'can_withdraw_without_penalty' => $canWithdrawWithoutPenalty,
                'loyalty_bonus_available' => $loyaltyBonusAvailable,
                'investment_duration_days' => $latestInvestment->investmentPlan->duration ?? 0,
                'expected_return' => $latestInvestment->expected_return ?? 0,
            ], 'Loyalty boost data retrieved successfully');
            
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to get loyalty boost data: ' . $e->getMessage());
        }
    }
}
