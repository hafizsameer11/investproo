<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\Withdrawal;
use App\Services\OtpService;
use App\Services\WalletOps;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

            // Verify password
            if (!Hash::check($data['password'], $user->password)) {
                return ResponseHelper::error('Invalid password', 422);
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

            // Calculate available balance (excluding locked amounts)
            $availableBalance = $wallet->total_balance - $wallet->locked_amount;
            
            if ($availableBalance < $data['amount']) {
                return ResponseHelper::error('Insufficient balance for withdrawal. Available: $' . number_format($availableBalance, 2), 422);
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
            
            // Use WalletOps to debit amount with priority
            $breakdown = WalletOps::debitByPriority($wallet, $data['amount']); 

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
            Log::error('Withdrawal error: ' . $ex->getMessage());
            return ResponseHelper::error('Please try again for withdrawal: ' . $ex->getMessage());
        }
    }

    public function approve(Request $request, $withdrawalId)
    {
        try {
            $withdrawal = Withdrawal::find($withdrawalId);
            if (!$withdrawal) {
                return ResponseHelper::error('Withdrawal not found', 404);
            }

            if ($withdrawal->status !== 'pending') {
                return ResponseHelper::error('Withdrawal is not pending', 422);
            }

            $withdrawal->update([
                'status' => 'active',
                'withdrawal_date' => now(),
            ]);

            $wallet = Wallet::where('user_id', $withdrawal->user_id)->first();
            if ($wallet) {
                $wallet->withdrawal_amount += $withdrawal->amount;
                $wallet->save();
            }

            Transaction::where('withdrawal_id', $withdrawalId)->update([
                'status' => 'completed',
            ]);

            return ResponseHelper::success($withdrawal, 'Withdrawal approved successfully');
        } catch (Exception $ex) {
            return ResponseHelper::error('Failed to approve withdrawal: ' . $ex->getMessage());
        }
    }

    public function reject(Request $request, $withdrawalId)
    {
        try {
            $withdrawal = Withdrawal::find($withdrawalId);
            if (!$withdrawal) {
                return ResponseHelper::error('Withdrawal not found', 404);
            }

            if ($withdrawal->status !== 'pending') {
                return ResponseHelper::error('Withdrawal is not pending', 422);
            }

            $withdrawal->update([
                'status' => 'rejected',
            ]);

            // Refund the deducted amount back to wallet
            $wallet = Wallet::where('user_id', $withdrawal->user_id)->first();
            if ($wallet) {
                WalletOps::refundToBucket($wallet, $withdrawal->amount, 'deposit_amount');
            }

            Transaction::where('withdrawal_id', $withdrawalId)->update([
                'status' => 'failed',
            ]);

            return ResponseHelper::success($withdrawal, 'Withdrawal rejected and amount refunded');
        } catch (Exception $ex) {
            return ResponseHelper::error('Failed to reject withdrawal: ' . $ex->getMessage());
        }
    }

    public function index()
    {
        $all_withdrawals = Withdrawal::with('user')->latest()->get();
        $total_withdrawals = Withdrawal::count();
        $pending_withdrawals = Withdrawal::where('status', 'pending')->count();
        $approved_withdrawals = Withdrawal::where('status', 'active')->count();
        $rejected_withdrawals = Withdrawal::where('status', 'rejected')->count();
        return view('admin.pages.withdrawal', compact('all_withdrawals', 'total_withdrawals', 'pending_withdrawals', 'approved_withdrawals', 'rejected_withdrawals'));
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
