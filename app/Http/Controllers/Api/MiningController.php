<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\ClaimedAmount;
use App\Models\Investment;
use App\Models\MiningSession;
use App\Models\User;
use App\Models\Wallet;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MiningController extends Controller
{
        public function start()
    {
        try {
            $user = Auth::user();
            if (!$user) return ResponseHelper::error('Unauthorized', 401);

            // One active session at a time
            $activeSession = MiningSession::where('user_id', $user->id)
                ->where('status', 'active')
                ->first();

            if ($activeSession) {
                return ResponseHelper::error('You already have an active mining session', 400);
            }

            // Require an active investment with a plan
            $investment = Investment::with('investmentPlan')
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->first();

            if (!$investment || !$investment->investmentPlan) {
                return ResponseHelper::error('No active investment/plan found. Please invest to start mining.', 400);
            }

            $session = MiningSession::create([
                'user_id'       => $user->id,
                'investment_id' => $investment->id,
                'started_at'    => now(),
                'status'        => 'active',
                'progress'      => 0,
                'rewards_claimed' => false,
            ]);

            return ResponseHelper::success($session, 'Mining session started successfully');
        } catch (Exception $ex) {
            return ResponseHelper::error('Failed to start mining session: ' . $ex->getMessage());
        }
    }

    /**
     * Report mining status; mark completed when 24h elapse.
     * Does NOT credit wallet here.
     */
public function status()
{
    try {
        $user = Auth::user();
        if (!$user) return ResponseHelper::error('Unauthorized', 401);

        $session = MiningSession::where('user_id', $user->id)
            ->whereIn('status', ['active', 'completed'])
            ->orderByDesc('id')
            ->first();

        if (!$session) {
            return ResponseHelper::success([
                'status'         => 'idle',
                'progress'       => 0.00,
                'time_remaining' => 0,
                'session'        => null,
            ], 'No active mining session');
        }

        // Always compute from timestamps in UTC
        $duration  = 24 * 60 * 60; // 24h seconds
        $startedAt = \Carbon\Carbon::parse($session->started_at)->utc();
        $nowUtc    = now('UTC');

        // signed diff to handle future/past; clamp to [0..duration]
        $elapsed   = $nowUtc->diffInSeconds($startedAt, false);
        if ($elapsed < 0) $elapsed = 0;
        if ($elapsed > $duration) $elapsed = $duration;

        // compute progress 0..100 with 2 decimals
        $progress  = round(($elapsed / $duration) * 100, 2);
        $timeRemaining = $duration - $elapsed;

        // Transition + persist
        if ($progress >= 100 && $session->status !== 'completed') {
            $session->update([
                'status'     => 'completed',
                'progress'   => 100.00,
                'stopped_at' => $session->stopped_at ?: $nowUtc,
            ]);
        } elseif ($session->status === 'active' && (float)$session->progress !== (float)$progress) {
            $session->update(['progress' => $progress]);
        }

        $session->refresh(); // ensure we return fresh values

        // Return consistent payload
        return ResponseHelper::success([
            'status'         => $session->status,            // 'active' or 'completed'
            'progress'       => (float)$session->progress,   // 0..100 (never negative)
            'time_remaining' => (int)$timeRemaining,         // 0..86400
            'started_at'     => $session->started_at,
            'session'        => $session,
        ], $session->status === 'completed'
            ? 'Mining session completed. Please claim your rewards.'
            : 'Mining session in progress');

    } catch (\Throwable $ex) {
        return ResponseHelper::error('Failed to get mining status: ' . $ex->getMessage());
    }
}




    /**
     * Stop an active session (no rewards credited).
     */
    public function stop()
    {
        try {
            $user = Auth::user();
            if (!$user) return ResponseHelper::error('Unauthorized', 401);

            $session = MiningSession::where('user_id', $user->id)
                ->where('status', 'active')
                ->first();

            if (!$session) {
                return ResponseHelper::error('No active mining session found', 400);
            }

            $session->update([
                'status'     => 'stopped',
                'stopped_at' => now(),
            ]);

            return ResponseHelper::success($session, 'Mining session stopped successfully');
        } catch (Exception $ex) {
            return ResponseHelper::error('Failed to stop mining session: ' . $ex->getMessage());
        }
    }

    /**
     * Claim rewards for a completed (unclaimed) session:
     * - Compute amount = investment.amount * (profit_percentage / 100)
     * - Create claimed_amounts record
     * - Increment wallet.profit_amount
     * - Mark session rewards_claimed = true
     */
    public function claimRewards()
    {
        try {
            $user = Auth::user();
            if (!$user) return ResponseHelper::error('Unauthorized', 401);

            $session = MiningSession::where('user_id', $user->id)
                ->where('status', 'active')
                ->where('rewards_claimed', false)
                ->orderBy('started_at') // oldest first, if multiple completed
                ->first();

            if (!$session) {
                
                return ResponseHelper::error('No completed mining session with unclaimed rewards', 400);
            }

            // Get the investment tied to the session (ensures exact plan used when started)
            $investment = Investment::with('investmentPlan')->find($session->investment_id);

            if (!$investment || !$investment->investmentPlan) {
                return ResponseHelper::error('Linked investment/plan not found for this session', 422);
            }

            $percentage = (float) $investment->investmentPlan->profit_percentage; 
            $baseAmount = (float) $investment->amount;                             
            $amount = round($baseAmount * ($percentage / 100), 2);             

            if ($amount <= 0) {
                return ResponseHelper::error('Calculated reward amount is zero. Check plan percentage and investment amount.', 422);
            }

            DB::transaction(function () use ($user, $session, $investment, $amount) {
                // 1) Create claimed_amounts record
                ClaimedAmount::create([
                    'user_id'       => $user->id,
                    'investment_id' => $investment->id,
                    'amount'        => $amount,
                    'reason'        => 'mining_daily_profit',
                ]);

                // 2) Update wallet.profit_amount
                $wallet = Wallet::firstOrCreate(
                    ['user_id' => $user->id],
                    ['balance' => 0, 'profit_amount' => 0, 'bonus_amount' => 0]
                );

                $wallet->increment('profit_amount', $amount);

                // 3) Mark session claimed
                $session->update(['rewards_claimed' => true,'status'=>'completed']);
                // MiningSession::where('id', $session->id)->update(['rewards_claimed' => true]);

            });

            return ResponseHelper::success([
                'session_id'  => $session->id,
                'claimed'     => true,
                'amount'      => $amount,
                'currency'    => 'USD', // adjust if you track currencies
                'message'     => 'Rewards claimed and added to wallet profit_amount',
            ], 'Mining rewards claimed successfully');
        } catch (Exception $ex) {
            return ResponseHelper::error('Failed to claim mining rewards: ' . $ex->getMessage());
        }
    }

}
