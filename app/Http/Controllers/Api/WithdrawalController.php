<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\Withdrawal;
use App\Services\OtpService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class WithdrawalController extends Controller
{
    protected $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }
    public function store(Request $request)
    {
        try { 
            $data = $request->validate([
                'amount' => 'required|numeric|min:50',
                'wallet_address' => 'required|string|min:10',
                'crypto_type' => 'required|string',
                'password' => 'required|string',
                'otp' => 'required|string|size:6',
                'notes' => 'nullable|string'
            ]);

            $user = Auth::user();
            if (!$user) {
                return ResponseHelper::error('User not authenticated', 401);
            }

            // Verify OTP
            $otpResult = $this->otpService->verifyOtp($user->email, $data['otp'], 'withdrawal');
            if (!$otpResult['success']) {
                return ResponseHelper::error($otpResult['message'], 422);
            }

            // Check if user has sufficient balance
            $wallet = Wallet::where('user_id', $user->id)->first();
            if (!$wallet) {
                return ResponseHelper::error('Wallet not found', 422);
            }

            // Calculate total available balance
            $totalBalance = ($wallet->deposit_amount ?? 0) + ($wallet->profit_amount ?? 0) + ($wallet->bonus_amount ?? 0) + ($wallet->referral_amount ?? 0);
            
            if ($totalBalance < $data['amount']) {
                return ResponseHelper::error('Insufficient balance for withdrawal. Available: $' . number_format($totalBalance, 2), 422);
            }

            // Create withdrawal record
            $withdrawalData = [
                'user_id' => $user->id,
                'amount' => $data['amount'],
                'wallet_address' => $data['wallet_address'],
                'crypto_type' => $data['crypto_type'],
                'notes' => $data['notes'] ?? '',
                'status' => 'pending',
            ];

            $withdrawal = Withdrawal::create($withdrawalData);
            $wallet=Wallet::where('user_id', $user->id)->decrement('deposit_amount', $data['amount']); 
            // --- Track loyalty: Update last withdrawal date and calculate loyalty bonus ---
            $user->update(['last_withdrawal_date' => Carbon::now()]);
            
            // Calculate loyalty bonus if user qualifies
            $loyaltyProgress = $user->getLoyaltyProgress();
            $currentTier = $user->getCurrentLoyaltyTier();
            
            if ($currentTier && $loyaltyProgress['current_days'] >= $currentTier->days_required) {
                $loyaltyBonus = ($data['amount'] * $currentTier->bonus_percentage) / 100;
                
                if ($loyaltyBonus > 0) {
                    // Add loyalty bonus to wallet
                    $wallet->bonus_amount = ($wallet->bonus_amount ?? 0) + $loyaltyBonus;
                    $wallet->save();
                    
                    // Update user's loyalty bonus earned
                    $user->loyalty_bonus_earned = ($user->loyalty_bonus_earned ?? 0) + $loyaltyBonus;
                    $user->save();
                    
                    // Create loyalty bonus transaction
                    Transaction::create([
                        'user_id' => $user->id,
                        'type' => 'loyalty_bonus',
                        'amount' => $loyaltyBonus,
                        'status' => 'completed',
                        'description' => "Loyalty bonus ({$currentTier->bonus_percentage}%) for {$currentTier->days_required} days",
                        'withdrawal_id' => $withdrawal->id,
                    ]);
                }
            }

            // Create transaction record
            Transaction::create([
                'user_id' => $user->id,
                'type' => 'withdrawal',
                'amount' => $data['amount'],
                'status' => 'pending',
                'description' => "Withdrawal request - {$data['crypto_type']}",
                'withdrawal_id' => $withdrawal->id,
            ]);
            
            return ResponseHelper::success($withdrawal, 'Withdrawal request submitted successfully');
        } catch (Exception $ex) {
            \Log::error('Withdrawal error: ' . $ex->getMessage());
            return ResponseHelper::error('Please try again for withdrawal: ' . $ex->getMessage());
        }
    }

    public function update(Request $request, $withdrawalId)
    {
        try
       { 
         Withdrawal::where('id', $withdrawalId)->update([
        'status' => 'active',
        'withdrawal_date' => now(),
    ]);
        $data['status'] = 'active';
        $data['withdrawal_date'] = Carbon::now(); 
        $detail = Withdrawal::find($withdrawalId);
        $wallet = Wallet::where('user_id', $detail['user_id'])->update([
            'withdrawal_amount' => $detail['amount'],
        ]);
      $transactions=Transaction::where('withdrawal_id', $withdrawalId)->update([
        'status' => 'completed',
    ]);
         return redirect()->back()->with('success', 'Withdrawal approved successfully.');
    } catch (Exception $ex) {
            return ResponseHelper::error('Not approved the  withdrawal' . $ex);
        }
    }

    public function index()
    {
        $all_withdrawals = Withdrawal::with('user')->latest()->get();
    $total_withdrawals = Withdrawal::count();
    $pending_withdrawals = Withdrawal::where('status', 'pending')->count();
    $approved_withdrawals = Withdrawal::where('status', 'active')->count();
    return view('admin.pages.withdrawal', compact('all_withdrawals', 'total_withdrawals', 'pending_withdrawals', 'approved_withdrawals'));
    }


public function destroy($id)
{
    Withdrawal::where('id', $id)->delete();
    return redirect()->back()->with('success', 'Withdrawal deleted.');
}

    // Get user withdrawals
    public function userWithdrawals()
    {
        try {
            $user = Auth::user();
            $withdrawals = Withdrawal::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();
                
            return ResponseHelper::success($withdrawals, 'User withdrawals retrieved successfully');
        } catch (Exception $ex) {
            return ResponseHelper::error('Failed to retrieve withdrawals: ' . $ex->getMessage());
        }
    }

}
