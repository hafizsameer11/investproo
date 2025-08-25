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
                'amount' => 'required|numeric|min:1',
                'otp' => 'required|string|size:6'
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
            if (!$wallet || $wallet->deposit_amount < $data['amount']) {
                return ResponseHelper::error('Insufficient balance for withdrawal', 422);
            }

            $data['user_id'] = $user->id;
            $data['status'] = 'pending';
            $withdrawal = Withdrawal::create($data);
            
            return ResponseHelper::success($withdrawal, 'Withdrawal request submitted successfully');
        } catch (Exception $ex) {
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
        Transaction::create([
            'user_id'=> $detail['user_id'],
            'withdrawal_id'=> $withdrawalId
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
