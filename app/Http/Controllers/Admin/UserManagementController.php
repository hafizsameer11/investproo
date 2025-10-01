<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminEdit;
use App\Models\ClaimedAmount;
use App\Models\Investment;
use App\Models\MiningSession;
use App\Models\User;
use App\Models\Wallet;
use App\Services\WalletOps;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UserManagementController extends Controller
{
    /**
     * Display user management page with claimable amounts
     */
    public function index()
    {
        // Get users with their wallets and investments
        $users = User::with(['wallet', 'investments' => function($query) {
            $query->where('status', 'active')->orWhere('status', 'completed');
        }])->paginate(20);

        // Get users with missed claims (completed investments but not claimed)
        $usersWithMissedClaims = $this->getUsersWithMissedClaims();

        return view('admin.pages.user-management', compact('users', 'usersWithMissedClaims'));
    }

    /**
     * Get users who have completed investments but haven't claimed their amounts
     */
    private function getUsersWithMissedClaims()
    {
        return User::whereHas('investments', function($query) {
            $query->where('status', 'completed');
        })
        ->whereDoesntHave('claimedAmounts', function($query) {
            $query->whereHas('investment', function($subQuery) {
                $subQuery->where('status', 'completed');
            });
        })
        ->with(['wallet', 'investments' => function($query) {
            $query->where('status', 'completed');
        }])
        ->get();
    }

    /**
     * Help user claim missed amount
     */
    public function helpClaimAmount(Request $request, $userId)
    {
        $request->validate([
            'investment_id' => 'required|exists:investments,id',
            'amount' => 'required|numeric|min:0',
            'reason' => 'nullable|string|max:255'
        ]);

        $user = User::findOrFail($userId);
        $investment = Investment::findOrFail($request->investment_id);

        // Verify the investment belongs to the user
        if ($investment->user_id !== $user->id) {
            return redirect()->back()->with('error', 'Investment does not belong to this user');
        }

        // Check if investment is completed
        if ($investment->status !== 'completed') {
            return redirect()->back()->with('error', 'Investment must be completed to claim amount');
        }

        DB::beginTransaction();
        try {
            // Create claimed amount record
            $claimedAmount = ClaimedAmount::create([
                'user_id' => $user->id,
                'investment_id' => $investment->id,
                'amount' => $request->amount,
                'reason' => 'admin_help_claim_' . ($request->reason ?? 'missed_claim')
            ]);

            // Update wallet profit amount
            $wallet = $user->wallet;
            if (!$wallet) {
                $wallet = Wallet::create([
                    'user_id' => $user->id,
                    'status' => 'active'
                ]);
            }

            $wallet->increment('profit_amount', $request->amount);

            // Log admin action
            AdminEdit::create([
                'admin_id' => Auth::id(),
                'user_id' => $userId,
                'field_name' => 'claimed_amount',
                'old_value' => 0,
                'new_value' => $request->amount,
                'edit_type' => 'admin_help_claim',
                'reason' => $request->reason ?? 'Admin helped user claim missed amount'
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Successfully helped user claim amount of $' . number_format($request->amount, 2));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to help claim amount: ' . $e->getMessage());
        }
    }

    /**
     * Update user wallet balances
     */
    public function updateWallet(Request $request, $userId)
    {
        $request->validate([
            'deposit_amount' => 'nullable|numeric|min:0',
            'profit_amount' => 'nullable|numeric|min:0',
            'referral_amount' => 'nullable|numeric|min:0',
            'bonus_amount' => 'nullable|numeric|min:0',
            'withdrawal_amount' => 'nullable|numeric|min:0',
            'locked_amount' => 'nullable|numeric|min:0',
            'reason' => 'nullable|string|max:255'
        ]);

        $user = User::findOrFail($userId);
        $wallet = $user->wallet;

        if (!$wallet) {
            $wallet = Wallet::create([
                'user_id' => $user->id,
                'status' => 'active'
            ]);
        }

        DB::beginTransaction();
        try {
            $oldValues = $wallet->toArray();
            $changes = [];

            // Update each field if provided
            $fields = ['deposit_amount', 'profit_amount', 'referral_amount', 'bonus_amount', 'withdrawal_amount', 'locked_amount'];
            
            foreach ($fields as $field) {
                if ($request->has($field) && $request->$field !== null) {
                    $oldValue = $wallet->$field ?? 0;
                    $newValue = $request->$field;
                    
                    if ($oldValue != $newValue) {
                        $wallet->$field = $newValue;
                        $changes[$field] = [
                            'old' => $oldValue,
                            'new' => $newValue
                        ];
                    }
                }
            }

            $wallet->save();

            // Log each change
            foreach ($changes as $field => $change) {
                AdminEdit::create([
                    'admin_id' => Auth::id(),
                    'user_id' => $userId,
                    'field_name' => $field,
                    'old_value' => $change['old'],
                    'new_value' => $change['new'],
                    'edit_type' => 'wallet_update',
                    'reason' => $request->reason ?? 'Admin updated wallet balance'
                ]);
            }

            DB::commit();
            return redirect()->back()->with('success', 'Wallet balances updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to update wallet: ' . $e->getMessage());
        }
    }

    /**
     * Get user's claimable amounts
     */
    public function getUserClaimableAmounts($userId)
    {
        $user = User::with(['wallet', 'investments' => function($query) {
            $query->where('status', 'completed');
        }])->findOrFail($userId);

        $completedInvestments = $user->investments;
        $claimableAmounts = [];

        foreach ($completedInvestments as $investment) {
            $totalClaimed = ClaimedAmount::where('user_id', $userId)
                ->where('investment_id', $investment->id)
                ->sum('amount');

            $claimableAmount = $investment->expected_return - $totalClaimed;
            
            if ($claimableAmount > 0) {
                $claimableAmounts[] = [
                    'investment' => $investment,
                    'total_expected' => $investment->expected_return,
                    'total_claimed' => $totalClaimed,
                    'claimable_amount' => $claimableAmount
                ];
            }
        }

        return response()->json([
            'user' => $user,
            'claimable_amounts' => $claimableAmounts
        ]);
    }

    /**
     * Force claim mining session rewards
     */
    public function forceClaimMiningRewards(Request $request, $userId)
    {
        $request->validate([
            'session_id' => 'required|exists:mining_sessions,id',
            'amount' => 'required|numeric|min:0',
            'reason' => 'nullable|string|max:255'
        ]);

        $user = User::findOrFail($userId);
        $session = MiningSession::findOrFail($request->session_id);

        if ($session->user_id !== $user->id) {
            return redirect()->back()->with('error', 'Mining session does not belong to this user');
        }

        DB::beginTransaction();
        try {
            // Create claimed amount record
            ClaimedAmount::create([
                'user_id' => $user->id,
                'investment_id' => $session->investment_id,
                'amount' => $request->amount,
                'reason' => 'admin_force_claim_' . ($request->reason ?? 'mining_rewards')
            ]);

            // Update wallet
            $wallet = $user->wallet;
            if (!$wallet) {
                $wallet = Wallet::create([
                    'user_id' => $user->id,
                    'status' => 'active'
                ]);
            }

            $wallet->increment('profit_amount', $request->amount);

            // Mark session as claimed
            $session->update([
                'rewards_claimed' => true,
                'status' => 'completed'
            ]);

            // Log admin action
            AdminEdit::create([
                'admin_id' => Auth::id(),
                'user_id' => $userId,
                'field_name' => 'mining_rewards_claimed',
                'old_value' => 0,
                'new_value' => $request->amount,
                'edit_type' => 'admin_force_claim_mining',
                'reason' => $request->reason ?? 'Admin force claimed mining rewards'
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Successfully force claimed mining rewards of $' . number_format($request->amount, 2));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to force claim mining rewards: ' . $e->getMessage());
        }
    }

    /**
     * Get user's mining sessions
     */
    public function getUserMiningSessions($userId)
    {
        $user = User::findOrFail($userId);
        $sessions = MiningSession::where('user_id', $userId)
            ->with('investment')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'user' => $user,
            'sessions' => $sessions
        ]);
    }
}
