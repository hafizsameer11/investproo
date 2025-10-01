<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MiningSession;
use App\Models\User;
use App\Models\AdminEdit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MiningSessionsController extends Controller
{
    public function index()
    {
        $miningSessions = MiningSession::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $totalSessions = MiningSession::count();
        $activeSessions = MiningSession::where('status', 'active')->count();
        $totalRewards = MiningSession::sum('reward_amount');

        return view('admin.pages.mining-sessions', compact(
            'miningSessions', 
            'totalSessions', 
            'activeSessions', 
            'totalRewards'
        ));
    }

    public function getUserSessions($userId)
    {
        $user = User::findOrFail($userId);
        $sessions = MiningSession::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'user' => $user,
            'sessions' => $sessions
        ]);
    }

    public function updateReward(Request $request, $sessionId)
    {
        $request->validate([
            'reward_amount' => 'required|numeric|min:0',
            'reason' => 'nullable|string|max:255'
        ]);

        $session = MiningSession::findOrFail($sessionId);
        $oldValue = $session->reward_amount;
        $newValue = $request->reward_amount;

        DB::beginTransaction();
        try {
            // Update session reward
            $session->reward_amount = $newValue;
            $session->save();

            // Log admin edit
            AdminEdit::create([
                'admin_id' => Auth::id(),
                'user_id' => $session->user_id,
                'field_name' => 'reward_amount',
                'old_value' => $oldValue,
                'new_value' => $newValue,
                'edit_type' => 'mining_reward',
                'reason' => $request->reason
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Mining reward updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to update mining reward: ' . $e->getMessage());
        }
    }

    public function activateSession($sessionId)
    {
        $session = MiningSession::findOrFail($sessionId);

        if ($session->status === 'active') {
            return redirect()->back()->with('error', 'Session is already active');
        }

        $session->status = 'active';
        $session->save();

        return redirect()->back()->with('success', 'Mining session activated');
    }

    public function deactivateSession($sessionId)
    {
        $session = MiningSession::findOrFail($sessionId);

        if ($session->status === 'inactive') {
            return redirect()->back()->with('error', 'Session is already inactive');
        }

        $session->status = 'inactive';
        $session->save();

        return redirect()->back()->with('success', 'Mining session deactivated');
    }

    public function getSessionStats()
    {
        $stats = [
            'total_sessions' => MiningSession::count(),
            'active_sessions' => MiningSession::where('status', 'active')->count(),
            'inactive_sessions' => MiningSession::where('status', 'inactive')->count(),
            'total_rewards' => MiningSession::sum('reward_amount'),
            'average_reward' => MiningSession::avg('reward_amount'),
            'top_users' => MiningSession::with('user')
                ->selectRaw('user_id, SUM(reward_amount) as total_rewards')
                ->groupBy('user_id')
                ->orderBy('total_rewards', 'desc')
                ->limit(10)
                ->get()
        ];

        return response()->json($stats);
    }
}
