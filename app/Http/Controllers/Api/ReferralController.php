<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Referrals;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReferralController extends Controller
{
    public function getMyReferrals(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['status'=>'error','message'=>'Unauthenticated'], 401);
        }

        $rows = Referrals::with('user')
            ->where('referral_code', $user->user_code)
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'message' => 'My referrals fetched',
            'data' => $rows,
        ], 200);
    }

    public function getReferralStats(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['status'=>'error','message'=>'Unauthenticated'], 401);
        }

        $total = Referrals::where('referral_code', $user->user_code)->count();

        // "active" definition: referred user has status = 'active' (adjust if different field)
        $active = Referrals::where('referral_code', $user->user_code)
            ->whereHas('user', fn($q) => $q->where('status', 'active'))
            ->count();

        return response()->json([
            'status' => 'success',
            'message' => 'Referral stats',
            'data' => [
                'totalReferrals' => $total,
                'activeReferrals' => $active,
                'totalNetwork' => $total, // adjust if multi-level implemented
            ],
        ], 200);
    }

    public function getReferralNetwork(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['status'=>'error','message'=>'Unauthenticated'], 401);
        }

        // Single-level for now. If you implement tree later, extend this.
        $level1 = Referrals::with('user')
            ->where('referral_code', $user->user_code)
            ->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Referral network',
            'data' => [
                'levels' => 1,
                'level1' => $level1,
            ],
        ], 200);
    }
}
