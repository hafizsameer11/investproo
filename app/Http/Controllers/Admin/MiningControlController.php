<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MiningSession;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MiningControlController extends Controller
{
    /**
     * Display all mining sessions with management options
     */
    public function index()
    {
        $miningSessions = MiningSession::with(['user', 'investment'])
            ->latest()
            ->paginate(20);

        $stats = [
            'total_sessions' => MiningSession::count(),
            'active_sessions' => MiningSession::where('status', 'active')->count(),
            'completed_sessions' => MiningSession::where('status', 'completed')->count(),
            'total_rewards' => MiningSession::sum('rewards_earned'),
            'claimed_rewards' => MiningSession::where('rewards_claimed', true)->sum('rewards_earned'),
            'unclaimed_rewards' => MiningSession::where('rewards_claimed', false)->sum('rewards_earned'),
        ];

        return view('admin.pages.mining-control', compact('miningSessions', 'stats'));
    }

    /**
     * Delete a mining session
     */
    public function deleteSession(Request $request, $sessionId)
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $session = MiningSession::findOrFail($sessionId);

        DB::beginTransaction();
        try {
            // Log the admin action before deletion
            \App\Models\AdminEdit::create([
                'admin_id' => Auth::id(),
                'user_id' => $session->user_id,
                'field_name' => 'mining_session_deleted',
                'old_value' => $session->id,
                'new_value' => 'deleted',
                'edit_type' => 'delete_mining_session',
                'reason' => $request->reason
            ]);

            $session->delete();

            DB::commit();
            return redirect()->back()->with('success', "Mining session #{$sessionId} deleted successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to delete mining session: ' . $e->getMessage());
        }
    }

    /**
     * Update mining session rewards
     */
    public function updateRewards(Request $request, $sessionId)
    {
        $request->validate([
            'rewards_earned' => 'required|numeric|min:0',
            'reason' => 'required|string|max:500'
        ]);

        $session = MiningSession::findOrFail($sessionId);
        $oldRewards = $session->rewards_earned;

        DB::beginTransaction();
        try {
            $session->update([
                'rewards_earned' => $request->rewards_earned,
                'updated_at' => Carbon::now()
            ]);

            // Log the admin action
            \App\Models\AdminEdit::create([
                'admin_id' => Auth::id(),
                'user_id' => $session->user_id,
                'field_name' => 'mining_rewards_updated',
                'old_value' => $oldRewards,
                'new_value' => $request->rewards_earned,
                'edit_type' => 'update_mining_rewards',
                'reason' => $request->reason
            ]);

            DB::commit();
            return redirect()->back()->with('success', "Mining session #{$sessionId} rewards updated successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to update mining session: ' . $e->getMessage());
        }
    }

    /**
     * Force claim mining rewards
     */
    public function forceClaimRewards(Request $request, $sessionId)
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $session = MiningSession::with('user.wallet')->findOrFail($sessionId);

        if ($session->rewards_claimed) {
            return redirect()->back()->with('error', 'Rewards for this session have already been claimed.');
        }

        DB::beginTransaction();
        try {
            // Add rewards to user's profit amount
            $wallet = $session->user->wallet;
            if ($wallet) {
                $wallet->increment('profit_amount', $session->rewards_earned);
                $wallet->save();
            }

            // Mark rewards as claimed
            $session->update([
                'rewards_claimed' => true,
                'status' => 'completed',
                'stopped_at' => Carbon::now()
            ]);

            // Log the admin action
            \App\Models\AdminEdit::create([
                'admin_id' => Auth::id(),
                'user_id' => $session->user_id,
                'field_name' => 'mining_rewards_claimed',
                'old_value' => 'unclaimed',
                'new_value' => 'claimed',
                'edit_type' => 'force_claim_mining',
                'reason' => $request->reason
            ]);

            DB::commit();
            return redirect()->back()->with('success', "Mining session #{$sessionId} rewards claimed successfully. Added \${$session->rewards_earned} to user's profit balance.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to claim mining rewards: ' . $e->getMessage());
        }
    }

    /**
     * Activate a mining session
     */
    public function activateSession(Request $request, $sessionId)
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $session = MiningSession::findOrFail($sessionId);

        DB::beginTransaction();
        try {
            $session->update([
                'status' => 'active',
                'started_at' => Carbon::now()
            ]);

            // Log the admin action
            \App\Models\AdminEdit::create([
                'admin_id' => Auth::id(),
                'user_id' => $session->user_id,
                'field_name' => 'mining_session_activated',
                'old_value' => 'inactive',
                'new_value' => 'active',
                'edit_type' => 'activate_mining',
                'reason' => $request->reason
            ]);

            DB::commit();
            return redirect()->back()->with('success', "Mining session #{$sessionId} activated successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to activate mining session: ' . $e->getMessage());
        }
    }

    /**
     * Deactivate a mining session
     */
    public function deactivateSession(Request $request, $sessionId)
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $session = MiningSession::findOrFail($sessionId);

        DB::beginTransaction();
        try {
            $session->update([
                'status' => 'completed',
                'stopped_at' => Carbon::now()
            ]);

            // Log the admin action
            \App\Models\AdminEdit::create([
                'admin_id' => Auth::id(),
                'user_id' => $session->user_id,
                'field_name' => 'mining_session_deactivated',
                'old_value' => 'active',
                'new_value' => 'completed',
                'edit_type' => 'deactivate_mining',
                'reason' => $request->reason
            ]);

            DB::commit();
            return redirect()->back()->with('success', "Mining session #{$sessionId} deactivated successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to deactivate mining session: ' . $e->getMessage());
        }
    }

    /**
     * Get mining session details for modal
     */
    public function getSessionDetails($sessionId)
    {
        $session = MiningSession::with(['user', 'investment'])
            ->findOrFail($sessionId);

        return response()->json([
            'success' => true,
            'data' => $session
        ]);
    }
}
