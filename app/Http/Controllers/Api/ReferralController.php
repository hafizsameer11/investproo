<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Referrals;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReferralController extends Controller
{
    public function getMyReferrals(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated'], 401);
        }

        $rows = Referrals::with('user:id,name,email,status,user_code')
            ->where('referral_code', $user->user_code)
            ->latest()
            ->get();

        // Map to the frontend's expected ReferralUser structure
        $referrals = $rows->map(function ($r) {
            return [
                'id'         => (int) $r->id,
                'name'       => optional($r->user)->name ?? '',
                'email'      => optional($r->user)->email ?? '',
                'created_at' => $r->created_at ? $r->created_at->toISOString() : null,
                'status'     => optional($r->user)->status ?? 'pending',
            ];
        })->values();

        $totalEarnings = (float) $rows->sum(function ($r) {
            // referral_bonus_amount may be string, cast safely
            return (float) ($r->referral_bonus_amount ?? 0);
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'My referrals fetched',
            'data'    => [
                'referrals' => $referrals,
                'stats'     => [
                    'total_referrals' => $rows->count(),
                    'total_earnings'  => $totalEarnings,
                    'referral_code'   => $user->user_code,
                ],
            ],
        ], 200);
    }

    public function getReferralStats(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated'], 401);
        }

        // Direct referrals (Level 1)
        $level1 = Referrals::where('referral_code', $user->user_code)->count();

        // If you haven’t implemented deeper levels yet, keep them as 0.
        $level2 = 0;
        $level3 = 0;
        $level4 = 0;
        $level5 = 0;

        $totalEarnings = (float) Referrals::where('referral_code', $user->user_code)
            ->sum(DB::raw('COALESCE(referral_bonus_amount,0)'));

        // Optional per-user bonus (fallback to the latest row’s per_user_referral if present)
        $perUserBonus = (float) (Referrals::where('referral_code', $user->user_code)
            ->latest()
            ->value('per_user_referral') ?? 0);

        return response()->json([
            'status'  => 'success',
            'message' => 'Referral stats',
            'data'    => [
                'level_1_referrals' => $level1,
                'level_2_referrals' => $level2,
                'level_3_referrals' => $level3,
                'level_4_referrals' => $level4,
                'level_5_referrals' => $level5,
                'total_referrals'   => $level1 + $level2 + $level3 + $level4 + $level5,
                'total_earnings'    => $totalEarnings,
                'referral_code'     => $user->user_code,
                'per_user_bonus'    => $perUserBonus,
            ],
        ], 200);
    }

    public function getReferralNetwork(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated'], 401);
        }

        // Single-level for now. Extend later for deeper levels.
        $level1 = Referrals::with('user:id,name,email,status,user_code')
            ->where('referral_code', $user->user_code)
            ->latest()
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
