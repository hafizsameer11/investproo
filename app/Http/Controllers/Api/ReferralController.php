<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Referrals;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReferralController extends Controller
{
    /**
     * Get users who joined with the current user's referral code
     */
    public function getMyReferrals()
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return ResponseHelper::error('User not found');
            }

            // Get users who used this user's referral code
            $referrals = User::where('referral_code', $user->user_code)
                ->select('id', 'name', 'email', 'created_at', 'status')
                ->orderBy('created_at', 'desc')
                ->get();

            // Get referral statistics
            $referralStats = Referrals::where('user_id', $user->id)->first();

            $data = [
                'referrals' => $referrals,
                'stats' => [
                    'total_referrals' => $referralStats ? $referralStats->total_referrals : 0,
                    'total_earnings' => $referralStats ? $referralStats->referral_bonus_amount : 0,
                    'referral_code' => $user->user_code,
                ]
            ];

            return ResponseHelper::success($data, 'Referral data retrieved successfully');
        } catch (\Exception $e) {
            \Log::error('Referral error: ' . $e->getMessage());
            return ResponseHelper::error('Failed to retrieve referral data: ' . $e->getMessage());
        }
    }

    /**
     * Get multi-level referral network
     */
    public function getReferralNetwork()
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return ResponseHelper::error('User not found');
            }

            $network = $this->buildReferralNetwork($user->user_code, 1, 5);
            
            return ResponseHelper::success($network, 'Referral network retrieved successfully');
        } catch (\Exception $e) {
            \Log::error('Referral network error: ' . $e->getMessage());
            return ResponseHelper::error('Failed to retrieve referral network: ' . $e->getMessage());
        }
    }

    /**
     * Build multi-level referral network
     */
    private function buildReferralNetwork($referralCode, $level, $maxLevel)
    {
        if ($level > $maxLevel || !$referralCode) {
            return [];
        }

        $referrals = User::where('referral_code', $referralCode)
            ->select('id', 'name', 'email', 'user_code', 'created_at', 'status')
            ->orderBy('created_at', 'desc')
            ->get();

        $network = [];
        foreach ($referrals as $referral) {
            $network[] = [
                'id' => $referral->id,
                'name' => $referral->name,
                'email' => $referral->email,
                'user_code' => $referral->user_code,
                'level' => $level,
                'joined_at' => $referral->created_at,
                'status' => $referral->status,
                'sub_referrals' => $this->buildReferralNetwork($referral->user_code, $level + 1, $maxLevel)
            ];
        }

        return $network;
    }

    /**
     * Get referral statistics
     */
    public function getReferralStats()
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return ResponseHelper::error('User not found');
            }

            $referralRecord = Referrals::where('user_id', $user->id)->first();

            $stats = [
                'referral_code' => $user->user_code,
                'total_referrals' => $referralRecord ? $referralRecord->total_referrals : 0,
                'total_earnings' => $referralRecord ? $referralRecord->referral_bonus_amount : 0,
                'per_user_bonus' => $referralRecord ? $referralRecord->per_user_referral : 0,
                'level_1_referrals' => User::where('referral_code', $user->user_code)->count(),
                'level_2_referrals' => $this->countLevelReferrals($user->user_code, 2),
                'level_3_referrals' => $this->countLevelReferrals($user->user_code, 3),
                'level_4_referrals' => $this->countLevelReferrals($user->user_code, 4),
                'level_5_referrals' => $this->countLevelReferrals($user->user_code, 5),
            ];

            return ResponseHelper::success($stats, 'Referral statistics retrieved successfully');
        } catch (\Exception $e) {
            \Log::error('Referral stats error: ' . $e->getMessage());
            return ResponseHelper::error('Failed to retrieve referral statistics: ' . $e->getMessage());
        }
    }

    /**
     * Count referrals at a specific level
     */
    private function countLevelReferrals($referralCode, $level)
    {
        if ($level <= 1) {
            return User::where('referral_code', $referralCode)->count();
        }

        $count = 0;
        $level1Referrals = User::where('referral_code', $referralCode)->pluck('user_code');
        
        foreach ($level1Referrals as $code) {
            $count += $this->countLevelReferrals($code, $level - 1);
        }

        return $count;
    }
}
