<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Investment;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Services\WalletOps;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvestmentController extends Controller
{
    public function investment()
    {
        try {
            $userId = Auth::id();
            Log::info('Fetching investments for user ID: ' . $userId);
            
            $investments = Investment::with(['investmentPlan', 'user'])
                ->where('user_id', $userId)
                ->where('status', 'active')
                ->orderBy('created_at', 'desc')
                ->get();

            Log::info('Found ' . $investments->count() . ' active investments for user ' . $userId);

            // Transform the data to include calculated fields
            $transformedInvestments = $investments->map(function ($investment) {
                return [
                    'id' => $investment->id,
                    'plan_name' => $investment->investmentPlan->plan_name ?? 'Unknown Plan',
                    'amount' => $investment->amount,
                    'start_date' => $investment->start_date,
                    'end_date' => $investment->end_date,
                    'status' => $investment->status,
                    'days_remaining' => $investment->days_remaining,
                    'progress_percentage' => $investment->progress_percentage,
                    'total_profit' => $investment->total_profit,
                    'daily_profit' => $investment->daily_profit,
                    'daily_profit_rate' => $investment->investmentPlan->profit_percentage ?? 0,
                    'duration_days' => $investment->investmentPlan->duration ?? 0,
                    'created_at' => $investment->created_at,
                ];
            });

            return ResponseHelper::success($transformedInvestments, 'Your active investments retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to retrieve investments: ' . $e->getMessage());
        }
    }
     public function createInvestment(Request $request)
    {
        try {
            $data = $request->validate([
                'investment_plan_id' => 'required|exists:investment_plans,id',
                'amount' => 'required|numeric|min:1',
            ]);

            $userId = Auth::id();
            
            // Check if user already has an active investment
            $activeInvestment = Investment::where('user_id', $userId)
                ->where('status', 'active')
                ->first();
                
            if ($activeInvestment) {
                return ResponseHelper::error('You can only have one active investment at a time.', 422);
            }

            $wallet = Wallet::where('user_id', $userId)->first();
            if (!$wallet) {
                return ResponseHelper::error('Wallet not found', 422);
            }

            // Check if user has sufficient balance
            $availableBalance = $wallet->total_balance - $wallet->locked_amount;
            if ($availableBalance < $data['amount']) {
                return ResponseHelper::error('Insufficient balance. Available: $' . number_format($availableBalance, 2), 422);
            }

            DB::beginTransaction();

            // Use WalletOps to debit amount
            $breakdown = WalletOps::debitByPriority($wallet, $data['amount']);
            
            // Lock the amount for investment
            WalletOps::lockAmount($wallet, $data['amount']);

            // Create investment
            $investment = Investment::create([
                'user_id' => $userId,
                'investment_plan_id' => $data['investment_plan_id'],
                'amount' => $data['amount'],
                'start_date' => now(),
                'end_date' => now()->addDays($request->investment_plan->duration ?? 30),
                'status' => 'active',
            ]);

            // Mark user as invested for referral purposes
            $wallet->is_invested = true;
            $wallet->save();

            // Process referral bonus if this is first investment
            if (!$wallet->is_invested) {
                $this->processReferralBonus($userId, $data['amount']);
            }

            DB::commit();

            return ResponseHelper::success($investment, 'Investment created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::error('Failed to create investment: ' . $e->getMessage());
        }
    }

    public function cancelInvestment($id)
    {
        DB::beginTransaction();
        try {
            $userId = Auth::id();

            $investment = Investment::where('id', $id)
                ->where('user_id', $userId)
                ->where('status', 'active')
                ->lockForUpdate()
                ->first();

            if (!$investment) {
                return ResponseHelper::error('Investment not found or already completed/canceled.', 404);
            }

            $wallet = Wallet::where('user_id', $userId)->lockForUpdate()->first();
            if (!$wallet) {
                return ResponseHelper::error('Wallet not found', 422);
            }
            //free the locked amount 
            // $wallet->locked_amount -= $investment->amount;

            // Use the cancel method from Investment model
            if ($investment->cancel()) {
                // Log transaction
                Transaction::create([
                    'user_id'     => $userId,
                    'type'        => 'canceled_investment',
                    'amount'      => $investment->amount,
                    'status'      => 'completed',
                    'description' => "Canceled investment: {$investment->investmentPlan->plan_name}",
                    'reference_id'=> $investment->id,
                ]);

                DB::commit();
                return ResponseHelper::success(null, 'Investment canceled successfully, funds returned to wallet.');
            } else {
                DB::rollBack();
                return ResponseHelper::error('Failed to cancel investment');
            }
        } catch (\Throwable $ex) {
            DB::rollBack();
            Log::error('Cancel investment failed: '.$ex->getMessage(), ['trace'=>$ex->getTraceAsString()]);
            return ResponseHelper::error('Failed to cancel investment: '.$ex->getMessage());
        }
    }

    private function processReferralBonus($userId, $amount)
    {
        $user = \App\Models\User::find($userId);
        if (!$user || !$user->referral_code) {
            return;
        }

        $sponsor = \App\Models\User::where('user_code', $user->referral_code)->first();
        if (!$sponsor) {
            return;
        }

        $sponsorWallet = $sponsor->wallet;
        if (!$sponsorWallet) {
            return;
        }

        // Calculate referral bonus (example: 5% of investment)
        $referralBonus = $amount * 0.05; // 5% referral bonus
        
        $sponsorWallet->referral_amount += $referralBonus;
        $sponsorWallet->save();

        // Log referral bonus transaction
        Transaction::create([
            'user_id' => $sponsor->id,
            'type' => 'referral_bonus',
            'amount' => $referralBonus,
            'status' => 'completed',
            'description' => "Referral bonus from {$user->name}",
        ]);
    }
}
