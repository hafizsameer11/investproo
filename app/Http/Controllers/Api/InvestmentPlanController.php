<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\InvestmentPlan;
use Exception;
use Illuminate\Http\Request;

class InvestmentPlanController extends Controller
{
    /**
     * Get all active investment plans
     */
    public function index()
    {
        try {
            $plans = InvestmentPlan::where('status', 'active')
                ->orderBy('min_amount', 'asc')
                ->get();
                
            return ResponseHelper::success($plans, 'Investment plans retrieved successfully');
        } catch (Exception $ex) {
            return ResponseHelper::error('Failed to retrieve investment plans: ' . $ex->getMessage());
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
