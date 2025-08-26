<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\InvestmentPlan;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InvestmentPlanController extends Controller
{
    /**
     * Get investment plans with optional status filter
     */
    public function index(Request $request)
    {
        try {
            $plans = InvestmentPlan::query()
                ->when($request->filled('status'), function($query) use ($request) {
                    $status = $request->string('status');
                    if (in_array($status, ['active', 'inactive'])) {
                        $query->where('status', $status);
                    }
                })
                ->orderBy('min_amount', 'asc')
                ->get();
                
            return ResponseHelper::success($plans, 'Investment plans retrieved successfully');
        } catch (Exception $ex) {
            Log::error('InvestmentPlanController@index failed', ['error' => $ex->getMessage()]);
            return ResponseHelper::error('Failed to fetch investment plans');
        }
    }

    /**
     * Get a specific investment plan by ID
     */
    public function show($id)
    {
        try {
            $plan = InvestmentPlan::findOrFail($id);
            return ResponseHelper::success($plan, 'Investment plan retrieved successfully');
        } catch (Exception $ex) {
            return ResponseHelper::error('Investment plan not found: ' . $ex->getMessage());
        }
    }
}
