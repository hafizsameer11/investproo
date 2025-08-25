<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\DepositRequest;
use App\Models\Chain;
use App\Models\Deposit;
use App\Models\Investment;
use App\Models\Transaction;
use App\Models\Wallet;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DepositeController extends Controller
{
    public function store(DepositRequest $request)
    {
        try
        {
            $data = $request->validated();
            
            $user = Auth::user();
            // $wallet = Wallet::where('user_id', $user->id)->first();
            
            // Handle image upload
            if (isset($data['deposit_picture']) && $data['deposit_picture'] && $data['deposit_picture'] instanceof \Illuminate\Http\UploadedFile) {
                $img = $data['deposit_picture'];
                $ext = $img->getClientOriginalExtension();
                $imageName = time() . '.' . $ext;
                $img->move(public_path('/deposits'), $imageName);
                $data['deposit_picture'] = 'deposits/' . $imageName;
            } else {
                // If no image uploaded, set to null
                $data['deposit_picture'] = null;
            }
            
            $data['user_id'] = Auth::id();
            $data['deposit_date'] = Carbon::now();
            $data['status'] = 'pending'; // Deposit needs approval
            $data['investment_plan_id'] = null; // No plan for deposit-only flow
            $chain_id = $data['chain_id'] ?? null;
            $chain_details = $chain_id ? Chain::where('id', $chain_id)->get() : null;
            
            $deposit = Deposit::create($data);
            $response = [
                'deposit_detail' => $deposit,
                'chain_detail' => $chain_details,
                'amount' => $data['amount']
            ];
            return ResponseHelper::success($response, 'Deposit request submitted successfully. It will be reviewed and added to your wallet.');
        } catch (Exception $ex) {
            return ResponseHelper::error('Deposit failed: ' . $ex->getMessage());
        }
    }

    public function activatePlan(Request $request, string $planId)
    {
        try
        {
            $data = $request->validate([
                'amount' => 'required|numeric|min:1',
            ]);
            
            $user = Auth::user();
            $wallet = Wallet::where('user_id', $user->id)->first();
            $plan = \App\Models\InvestmentPlan::find($planId);
            
            Log::info('Plan activation request:', [
                'user_id' => $user->id,
                'plan_id' => $planId,
                'amount' => $data['amount'],
                'wallet_data' => [
                    'deposit_amount' => $wallet->deposit_amount ?? 0,
                    'withdrawal_amount' => $wallet->withdrawal_amount ?? 0,
                    'profit_amount' => $wallet->profit_amount ?? 0,
                    'bonus_amount' => $wallet->bonus_amount ?? 0,
                    'referral_amount' => $wallet->referral_amount ?? 0,
                ]
            ]);
            
            if (!$plan) {
                return ResponseHelper::error('Investment plan not found', 404);
            }
            
            // Calculate total available balance (including deposit amount)
            $totalBalance = ($wallet->deposit_amount ?? 0) + 
                           ($wallet->withdrawal_amount ?? 0) + 
                           ($wallet->profit_amount ?? 0) + 
                           ($wallet->bonus_amount ?? 0) + 
                           ($wallet->referral_amount ?? 0);
            
            if ($totalBalance < $data['amount']) {
                return ResponseHelper::error('Insufficient balance. Please deposit first.', 400);
            }
            
            // Check if plan minimum amount is met
            if ($data['amount'] < $plan->min_amount) {
                return ResponseHelper::error("Minimum amount for this plan is $" . $plan->min_amount, 400);
            }
            
            // Check if plan maximum amount is not exceeded
            if ($data['amount'] > $plan->max_amount) {
                return ResponseHelper::error("Maximum amount for this plan is $" . $plan->max_amount, 400);
            }
            
            // Deduct amount from wallet (prioritize deposit_amount first)
            $deductedAmount = min($data['amount'], $totalBalance);
            $remainingAmount = $deductedAmount;
            
            // Calculate new wallet values
            $newDepositAmount = max(0, ($wallet->deposit_amount ?? 0) - $remainingAmount);
            $remainingAmount = max(0, $remainingAmount - ($wallet->deposit_amount ?? 0));
            
            $newWithdrawalAmount = max(0, ($wallet->withdrawal_amount ?? 0) - $remainingAmount);
            $remainingAmount = max(0, $remainingAmount - ($wallet->withdrawal_amount ?? 0));
            
            $newProfitAmount = max(0, ($wallet->profit_amount ?? 0) - $remainingAmount);
            $remainingAmount = max(0, $remainingAmount - ($wallet->profit_amount ?? 0));
            
            $newBonusAmount = max(0, ($wallet->bonus_amount ?? 0) - $remainingAmount);
            $remainingAmount = max(0, $remainingAmount - ($wallet->bonus_amount ?? 0));
            
            $newReferralAmount = max(0, ($wallet->referral_amount ?? 0) - $remainingAmount);
            
            Wallet::where('user_id', $user->id)->update([
                'deposit_amount' => $newDepositAmount,
                'withdrawal_amount' => $newWithdrawalAmount,
                'profit_amount' => $newProfitAmount,
                'bonus_amount' => $newBonusAmount,
                'referral_amount' => $newReferralAmount,
            ]);
            
            // Create investment
            $investment = Investment::create([
                'user_id' => $user->id,
                'investment_plan_id' => $planId,
                'amount' => $deductedAmount,
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addDays($plan->duration_days),
                'status' => 'active'
            ]);
            
            // Create transaction record
            Transaction::create([
                'user_id' => $user->id,
                'investment_id' => $investment->id,
                'type' => 'investment',
                'amount' => $deductedAmount,
                'status' => 'completed'
            ]);
            
            // Process referral earnings for this investment
            Log::info('Starting referral earnings processing for investment:', [
                'user_id' => $user->id,
                'user_referral_code' => $user->referral_code,
                'investment_amount' => $deductedAmount,
                'plan_id' => $plan->id
            ]);
            $this->processInvestmentReferralEarnings($user, $deductedAmount, $plan);
            
            $response = [
                'investment' => $investment,
                'plan' => $plan,
                'amount_invested' => $deductedAmount,
                'remaining_balance' => $totalBalance - $deductedAmount
            ];
            
            return ResponseHelper::success($response, 'Plan activated successfully!');
        } catch (Exception $ex) {
            return ResponseHelper::error('Plan activation failed: ' . $ex->getMessage());
        }
    }

    /**
     * Process referral earnings when a user invests
     */
    protected function processInvestmentReferralEarnings($investingUser, $investmentAmount, $plan)
    {
        try {
            // Check if user has a referral code
            if (!$investingUser->referral_code) {
                Log::info('No referral code found for user, skipping referral earnings');
                return;
            }

            // Define referral percentages per level
            $referralPercentages = [
                1 => 0.10,  // 10% for level 1
                2 => 0.07,  // 7% for level 2
                3 => 0.05,  // 5% for level 3
                4 => 0.03,  // 3% for level 4
                5 => 0.02,  // 2% for level 5
            ];

            $currentReferralCode = $investingUser->referral_code;
            $level = 1;

            while ($level <= 5 && $currentReferralCode) {
                // Find the referrer user by their user_code
                $referrerUser = \App\Models\User::where('user_code', $currentReferralCode)->first();

                if (!$referrerUser) {
                    Log::info('Referrer not found for code: ' . $currentReferralCode . ' at level: ' . $level);
                    break; // Stop if no referrer found
                }

                Log::info('Processing referral for level ' . $level . ':', [
                    'referrer_user_id' => $referrerUser->id,
                    'referrer_user_code' => $referrerUser->user_code,
                    'investment_amount' => $investmentAmount,
                    'percentage' => $referralPercentages[$level] * 100 . '%'
                ]);

                // Calculate referral bonus for this level
                $referralBonus = $investmentAmount * $referralPercentages[$level];

                // Find or create referral record for this referrer
                $referralRecord = \App\Models\Referrals::firstOrCreate(
                    ['user_id' => $referrerUser->id],
                    [
                        'referral_code' => $referrerUser->user_code,
                        'referral_bonus_amount' => 0,
                        'total_referrals' => 0
                    ]
                );

                // Update referral bonus amount
                $referralRecord->referral_bonus_amount += $referralBonus;
                $referralRecord->save();

                // Update referrer's wallet
                $referrerWallet = \App\Models\Wallet::where('user_id', $referrerUser->id)->first();
                if ($referrerWallet) {
                    $referrerWallet->referral_amount += $referralBonus;
                    $referrerWallet->save();
                } else {
                    // Create wallet if it doesn't exist
                    \App\Models\Wallet::create([
                        'user_id' => $referrerUser->id,
                        'referral_amount' => $referralBonus,
                        'deposit_amount' => 0,
                        'withdrawal_amount' => 0,
                        'profit_amount' => 0,
                        'bonus_amount' => 0,
                        'status' => 'active'
                    ]);
                }

                // Log the referral earning
                Log::info('Referral earning processed:', [
                    'investing_user_id' => $investingUser->id,
                    'referrer_user_id' => $referrerUser->id,
                    'level' => $level,
                    'investment_amount' => $investmentAmount,
                    'referral_bonus' => $referralBonus,
                    'percentage' => $referralPercentages[$level] * 100 . '%'
                ]);

                // Go up the chain to next level
                $currentReferralCode = $referrerUser->referral_code;
                $level++;
            }
        } catch (Exception $ex) {
            Log::error('Error processing referral earnings: ' . $ex->getMessage());
        }
    }

   public function update(Request $request, $depositId)
{
    

    $deposit = Deposit::findOrFail($depositId);

    // Update deposit status
    $deposit->update([
        'status'=>'active'
    ]);

    // Create investment for the user
    // Investment::create([
    //     'user_id' => $deposit->user_id,
    //     'investment_plan_id' => $deposit->investment_plan_id,
    //     'start_date' => Carbon::now(),
    //     'end_date' => Carbon::now()->addMonth(),
    //     'status' => 'active'
    // ]);
    Transaction::create([
        'user_id'=> $deposit->user_id,
        'deposit_id'=> $deposit->id
    ]);
    Log::info('Deposit status updated for user', ['user_id' => $deposit->user_id, 'deposit_id' => $deposit->id]);
  $wallet = Wallet::where('user_id', $deposit->user_id)->first();

if ($wallet) {
    $wallet->deposit_amount += $deposit->amount;
    $wallet->save();
}

    return redirect()->route('deposits');
}


    public function index()
    {
        $all_deposits = Deposit::with(['user', 'investmentPlan', 'chain'])->latest()->get();
        $total_deposits = Deposit::count();
        $pending_deposits = Deposit::where('status', 'pending')->count();
        $active_deposits = Deposit::where('status', 'active')->count();
        $chains = Chain::all();
        return view('admin.pages.deposit', compact('all_deposits','total_deposits', 'active_deposits','pending_deposits', 'chains'));
}
// update chain address
public function updateChain(Request $request, $id)
    {
         $request->validate([
        'chain_id' => 'nullable',
    ]);
    $deposit = Deposit::findOrFail($id);
    $deposit->chain_id = $request->chain_id;
    $deposit->save();

        return redirect()->route('deposits')->with('success', 'Deposit chain updated successfully.');
    }
    public function destroy($id)
{
    // dd($id);
    $deposit = Deposit::findOrFail($id);

    // Optional: Delete deposit image from storage if exists
    if ($deposit->deposit_picture && Storage::exists($deposit->deposit_picture)) {
        Storage::delete($deposit->deposit_picture);
    }

    // Delete the deposit record
    $deposit->delete();

    return redirect()->back()->with('success', 'Deposit deleted successfully.');
}

    // Get user deposits
    public function userDeposits()
    {
        try {
            $user = Auth::user();
            $deposits = Deposit::where('user_id', $user->id)
                ->with(['investmentPlan', 'chain'])
                ->orderBy('created_at', 'desc')
                ->get();
                
            return ResponseHelper::success($deposits, 'User deposits retrieved successfully');
        } catch (Exception $ex) {
            return ResponseHelper::error('Failed to retrieve deposits: ' . $ex->getMessage());
        }
    }
}
