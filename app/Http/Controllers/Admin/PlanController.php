<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InvestmentPlan;

class PlanController extends Controller
{
    public function index()
    {
        $plans = InvestmentPlan::orderByDesc('id')->get();
        return view('admin.pages.plans.investment-plan', compact('plans'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'plan_name' => 'nullable|string|max:255',
            'min_amount' => 'nullable|numeric|min:0',
            'max_amount' => 'nullable|numeric|min:0|gte:min_amount',
            'profit_percentage' => 'nullable|numeric|min:0',
            'duration' => 'nullable|integer|min:1',
            'status' => 'nullable|in:active,inactive',
        ]);

        InvestmentPlan::create($data);

        return redirect()->route('plans')->with('success', 'Plan created successfully.');
    }

    public function update(Request $request, $id)
    {
        // dd($request->all());
        $plan = InvestmentPlan::findOrFail($id);
        $data = $request->validate([
            'plan_name' => 'nullable|string|max:255',
            'min_amount' => 'nullable|numeric|min:0',
            'max_amount' => 'nullable|numeric|min:0|gte:min_amount',
            'profit_percentage' => 'nullable|numeric|min:0',
            'duration' => 'nullable|integer|min:1',
        ]);

        $plan->update($data);

        return redirect()->route('plans')->with('success', 'Plan updated successfully.');
    }

    public function destroy($id)
    {
        $plan = InvestmentPlan::findOrFail($id);
        $plan->delete();

        return redirect()->route('plans')->with('success', 'Plan deleted successfully.');
    }
}
