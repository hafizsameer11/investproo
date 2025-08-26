<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Referrals;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReferralController extends Controller
{
  /**
     * GET /api/referrals/my
     * Returns direct referrals (level 1) from users table.
     * Shape matches your RN service: { status, message, data: { referrals: [], stats: {...} } }
     */
    public function getMyReferrals(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated'], 401);
        }

        $code = (string) ($user->user_code ?? '');
        $wallet=Wallet::where('user_id',$user->id)->first();
        if ($code === '') {
            // No code on this user -> nothing to return
            return response()->json([
                'status'  => 'success',
                'message' => 'My referrals fetched',
                'data'    => [
                    'referrals' => [],
                    'stats'     => [
                        'total_referrals' => 0,
                        'total_earnings'  => $wallet ? $wallet->referral_amount : 0,
                        'referral_code'   => '',
                    ],
                ],
            ], 200);
        }

        // Level-1 referrals = users whose referral_code == my user_code
        $rows = User::query()
            ->select(['id', 'name', 'email', 'status', 'user_code', 'referral_code', 'created_at'])
            ->where('referral_code', $code)
            ->latest('id')
            ->get();

        // Map to your RN ReferralUser shape
        $referrals = $rows->map(fn ($u) => [
            'id'         => (int) $u->id,
            'name'       => (string) ($u->name ?? ''),
            'email'      => (string) ($u->email ?? ''),
            'created_at' => optional($u->created_at)?->toISOString(),
            'status'     => (string) ($u->status ?? 'pending'),
        ])->values();

        return response()->json([
            'status'  => 'success',
            'message' => 'My referrals fetched',
            'data'    => [
                'referrals' => $referrals,
                'stats'     => [
                    'total_referrals' => $rows->count(),
                    'total_earnings'  => $wallet ? $wallet->referral_amount : 0, // keep 0 unless you have a payout table to sum from
                    'referral_code'   => $code,
                ],
            ],
        ], 200);
    }

    /**
     * GET /api/referrals/stats
     * Counts levels 1–5 via BFS over users.user_code/referral_code.
     * Returns the exact snake_case structure your app expects.
     */
    public function getReferralStats(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated'], 401);
        }

        $rootCode = (string) ($user->user_code ?? '');
        if ($rootCode === '') {
            return response()->json([
                'status'  => 'success',
                'message' => 'Referral stats',
                'data'    => [
                    'level_1_referrals' => 0,
                    'level_2_referrals' => 0,
                    'level_3_referrals' => 0,
                    'level_4_referrals' => 0,
                    'level_5_referrals' => 0,
                    'total_referrals'   => 0,
                    'total_earnings'    => 0,
                    'referral_code'     => '',
                    'per_user_bonus'    => 0,
                ],
            ], 200);
        }

        // ---------- BFS for up to 5 levels ----------
        $levelCounts = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];

        // Level 1 seed: all users who used my code
        $currentCodes = [$rootCode];
        $visitedUserIds = []; // just a safety net to avoid cycles (shouldn't happen with codes)

        for ($level = 1; $level <= 5; $level++) {
            // Find users whose referral_code is in $currentCodes
            $batch = User::query()
                ->select(['id', 'user_code', 'referral_code'])
                ->whereIn('referral_code', $currentCodes)
                ->get();

            // Count unique new users
            $newUsers = $batch->filter(function ($u) use (&$visitedUserIds) {
                if (isset($visitedUserIds[$u->id])) return false;
                $visitedUserIds[$u->id] = true;
                return true;
            });

            $levelCounts[$level] = $newUsers->count();

            // Prepare next wave: the codes of these users (their user_code)
            $currentCodes = $newUsers
                ->pluck('user_code')
                ->filter(fn ($c) => !empty($c))
                ->values()
                ->all();

            if (empty($currentCodes)) break; // no deeper levels
        }
        // -------------------------------------------
        $wallet=Wallet::where('user_id',$user->id)->first();
        $totalReferrals = array_sum($levelCounts);

        return response()->json([
            'status'  => 'success',
            'message' => 'Referral stats',
            'data'    => [
                'level_1_referrals' => $levelCounts[1],
                'level_2_referrals' => $levelCounts[2],
                'level_3_referrals' => $levelCounts[3],
                'level_4_referrals' => $levelCounts[4],
                'level_5_referrals' => $levelCounts[5],
                'total_referrals'   => $totalReferrals,
                'total_earnings'    => $wallet ? $wallet->referral_amount : 0,      // set from your payouts if/when available
                'referral_code'     => $rootCode,
                'per_user_bonus'    => 0,      // keep 0 unless you have a rule/table
            ],
        ], 200);
    }

    /**
     * GET /api/referrals/network
     * Returns Level-1 nodes (you can extend to include children later).
     */
    public function getReferralNetwork(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated'], 401);
        }

        $code = (string) ($user->user_code ?? '');
        if ($code === '') {
            return response()->json([
                'status'  => 'success',
                'message' => 'Referral network',
                'data'    => [
                    'levels' => 0,
                    'level1' => [],
                ],
            ], 200);
        }

        $level1 = User::query()
            ->select(['id', 'name', 'email', 'status', 'user_code', 'referral_code', 'created_at'])
            ->where('referral_code', $code)
            ->latest('id')
            ->get();

        return response()->json([
            'status'  => 'success',
            'message' => 'Referral network',
            'data'    => [
                'levels' => 1,
                'level1' => $level1,
            ],
        ], 200);
    }

}
