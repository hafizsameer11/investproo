<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\MiningSession;
use App\Models\User;
use App\Models\Wallet;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MiningController extends Controller
{
    public function start()
    {
        try {
            $user = Auth::user();
            
            // Check if user already has an active mining session
            $activeSession = MiningSession::where('user_id', $user->id)
                ->where('status', 'active')
                ->first();
                
            if ($activeSession) {
                return ResponseHelper::error('You already have an active mining session', 400);
            }
            
            // Create new mining session
            $session = MiningSession::create([
                'user_id' => $user->id,
                'started_at' => now(),
                'status' => 'active',
                'progress' => 0,
            ]);
            
            return ResponseHelper::success($session, 'Mining session started successfully');
        } catch (Exception $ex) {
            return ResponseHelper::error('Failed to start mining session: ' . $ex->getMessage());
        }
    }
    
    public function status()
    {
        try {
            $user = Auth::user();
            
            $session = MiningSession::where('user_id', $user->id)
                ->where('status', 'active')
                ->first();
                
            if (!$session) {
                return ResponseHelper::success([
                    'status' => 'idle',
                    'progress' => 0,
                    'time_remaining' => 0,
                ], 'No active mining session');
            }
            
            // Calculate progress and time remaining
            $startedAt = Carbon::parse($session->started_at);
            $now = Carbon::now();
            $duration = 24 * 60 * 60; // 24 hours in seconds
            $elapsed = $now->diffInSeconds($startedAt);
            $progress = min(($elapsed / $duration) * 100, 100);
            $timeRemaining = max($duration - $elapsed, 0);
            
            // Check if session is completed
            if ($progress >= 100) {
                $session->update([
                    'status' => 'completed',
                    'progress' => 100,
                ]);
                
                // Add mining rewards to wallet
                $wallet = Wallet::where('user_id', $user->id)->first();
                if ($wallet) {
                    $reward = 50; // $50 mining reward
                    $wallet->increment('bonus_amount', $reward);
                }
                
                return ResponseHelper::success([
                    'status' => 'completed',
                    'progress' => 100,
                    'time_remaining' => 0,
                    'reward' => $reward,
                ], 'Mining session completed! You earned $' . $reward);
            }
            
            // Update progress
            $session->update(['progress' => $progress]);
            
            return ResponseHelper::success([
                'status' => 'active',
                'progress' => $progress,
                'time_remaining' => $timeRemaining,
                'started_at' => $session->started_at,
            ], 'Mining session in progress');
        } catch (Exception $ex) {
            return ResponseHelper::error('Failed to get mining status: ' . $ex->getMessage());
        }
    }
    
    public function stop()
    {
        try {
            $user = Auth::user();
            
            $session = MiningSession::where('user_id', $user->id)
                ->where('status', 'active')
                ->first();
                
            if (!$session) {
                return ResponseHelper::error('No active mining session found', 400);
            }
            
            $session->update([
                'status' => 'stopped',
                'stopped_at' => now(),
            ]);
            
            return ResponseHelper::success($session, 'Mining session stopped successfully');
        } catch (Exception $ex) {
            return ResponseHelper::error('Failed to stop mining session: ' . $ex->getMessage());
        }
    }
    
    public function claimRewards()
    {
        try {
            $user = Auth::user();
            
            $session = MiningSession::where('user_id', $user->id)
                ->where('status', 'completed')
                ->where('rewards_claimed', false)
                ->first();
                
            if (!$session) {
                return ResponseHelper::error('No completed mining session with unclaimed rewards', 400);
            }
            
            // Mark rewards as claimed
            $session->update(['rewards_claimed' => true]);
            
            return ResponseHelper::success($session, 'Mining rewards claimed successfully');
        } catch (Exception $ex) {
            return ResponseHelper::error('Failed to claim mining rewards: ' . $ex->getMessage());
        }
    }
}
