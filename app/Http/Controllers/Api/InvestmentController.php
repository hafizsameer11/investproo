<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Investment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvestmentController extends Controller
{
    public function investment()
    {
        try {
            $userId = Auth::id();
            \Log::info('Fetching investments for user ID: ' . $userId);
            
            $investments = Investment::with(['investmentPlan', 'user'])
                ->where('user_id', $userId)
                ->where('status', 'active')
                ->orderBy('created_at', 'desc')
                ->get();

            \Log::info('Found ' . $investments->count() . ' active investments for user ' . $userId);

            // Transform the data to include calculated fields
            $transformedInvestments = $investments->map(function ($investment) {
                return [
                    'id' => $investment->id,
                    'plan_name' => $investment->investmentPlan->plan_name ?? 'Unknown Plan',
                    'amount' => $investment->amount,
                    'start_date' => $investment->start_date,
                    'end_date' => $investment->end_date,
                    'status' => $investment->status,
                    'days_remaining' => $investment->days_remaining,
                    'progress_percentage' => $investment->progress_percentage,
                    'total_profit' => $investment->total_profit,
                    'daily_profit_rate' => $investment->investmentPlan->profit_percentage ?? 0,
                    'duration_days' => $investment->investmentPlan->duration ?? 0,
                    'created_at' => $investment->created_at,
                ];
            });

            return ResponseHelper::success($transformedInvestments, 'Your active investments retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to retrieve investments: ' . $e->getMessage());
        }
    }
}
