<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\InvestmentPlanRequest;
use App\Models\InvestmentPlan;
use Exception;
use Illuminate\Http\Request;

class InvestmentPlanController extends Controller
{
   public function index()
{
    $plans = InvestmentPlan::orderBy('id', 'desc')->get();
    return view('admin.pages.plans.investment-plan', compact('plans'));
}   

    public function store(InvestmentPlanRequest $request)
    {
        try
        {
            $data = $request->validated();
            $data['status'] = 'active';
            $investment_plans = InvestmentPlan::create($data);
            return ResponseHelper::success($investment_plans, 'Investment Plan created successfully');
            
        }catch (Exception $ex) {
            return ResponseHelper::error('Investment Plan is not created ' . $ex);
        }
    }
    public function update(InvestmentPlanRequest $request, string $id)
    {
        try
        {
            $data = $request->validated();
        $plans = InvestmentPlan::where('id', $id)->update($data);
        return ResponseHelper::success($plans, 'Investment Plan updated successfully');
        }catch (Exception $ex) {
            return ResponseHelper::error('Investment Plan is not updated ' . $ex);
        }

    }
    public function destory(string $id)
    {
        try
        {
            if(!$id)
            {
                return ResponseHelper::error('This plan is not exist');
            }
            $delete_plan = InvestmentPlan::delete($id);
            return ResponseHelper::success($delete_plan, 'This is delete successfully');
        }catch (Exception $ex) {
            return ResponseHelper::error('Investment Plan is not deleted ' . $ex);
        }
    }
}
