<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Loyalty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LoyaltyController extends Controller
{
    /**
     * Display a listing of loyalty tiers
     */
    public function index()
    {
        $loyalties = Loyalty::ordered()->get();
        return view('admin.pages.loyalty.index', compact('loyalties'));
    }

    /**
     * Show the form for creating a new loyalty tier
     */
    public function create()
    {
        return view('admin.pages.loyalty.create');
    }

    /**
     * Store a newly created loyalty tier
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'days_required' => 'required|integer|min:1|unique:loyalties,days_required',
            'bonus_percentage' => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        Loyalty::create($request->all());

        return redirect()->route('admin.loyalty.index')
            ->with('success', 'Loyalty tier created successfully');
    }

    /**
     * Show the form for editing the specified loyalty tier
     */
    public function edit($id)
    {
        $loyalty = Loyalty::findOrFail($id);
        return view('admin.pages.loyalty.edit', compact('loyalty'));
    }

    /**
     * Update the specified loyalty tier
     */
    public function update(Request $request, $id)
    {
        $loyalty = Loyalty::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'days_required' => 'required|integer|min:1|unique:loyalties,days_required,' . $id,
            'bonus_percentage' => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $loyalty->update($request->all());

        return redirect()->route('admin.loyalty.index')
            ->with('success', 'Loyalty tier updated successfully');
    }

    /**
     * Remove the specified loyalty tier
     */
    public function destroy($id)
    {
        $loyalty = Loyalty::findOrFail($id);
        $loyalty->delete();

        return redirect()->route('admin.loyalty.index')
            ->with('success', 'Loyalty tier deleted successfully');
    }

    /**
     * Toggle loyalty tier status
     */
    public function toggleStatus($id)
    {
        $loyalty = Loyalty::findOrFail($id);
        $loyalty->update(['is_active' => !$loyalty->is_active]);

        $status = $loyalty->is_active ? 'activated' : 'deactivated';
        return redirect()->route('admin.loyalty.index')
            ->with('success', "Loyalty tier {$status} successfully");
    }
}
