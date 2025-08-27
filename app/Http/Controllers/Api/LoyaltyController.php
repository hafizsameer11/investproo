<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Loyalty;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LoyaltyController extends Controller
{
    /**
     * Get user's loyalty status and progress
     */
    public function getUserLoyalty()
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not authenticated'
                ], 401);
            }

            $loyaltyProgress = $user->getLoyaltyProgress();
            $currentTier = $user->getCurrentLoyaltyTier();
            $nextTier = $user->getNextLoyaltyTier();
            
            // Get all active loyalty tiers for display
            $allTiers = Loyalty::active()->ordered()->get();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'current_days' => $loyaltyProgress['current_days'],
                    'current_tier' => $currentTier,
                    'next_tier' => $nextTier,
                    'days_remaining' => $loyaltyProgress['days_remaining'],
                    'progress_percentage' => $loyaltyProgress['progress_percentage'],
                    'loyalty_bonus_earned' => $user->loyalty_bonus_earned,
                    'all_tiers' => $allTiers,
                    'first_investment_date' => $user->first_investment_date,
                    'last_withdrawal_date' => $user->last_withdrawal_date
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get loyalty status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all loyalty tiers (admin)
     */
    public function getAllLoyalties()
    {
        try {
            $loyalties = Loyalty::ordered()->get();

            return response()->json([
                'status' => 'success',
                'data' => $loyalties
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get loyalty tiers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create new loyalty tier (admin)
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'days_required' => 'required|integer|min:1|unique:loyalties,days_required',
                'bonus_percentage' => 'required|numeric|min:0|max:100',
                'description' => 'nullable|string',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $loyalty = Loyalty::create($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Loyalty tier created successfully',
                'data' => $loyalty
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create loyalty tier: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update loyalty tier (admin)
     */
    public function update(Request $request, $id)
    {
        try {
            $loyalty = Loyalty::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'days_required' => 'required|integer|min:1|unique:loyalties,days_required,' . $id,
                'bonus_percentage' => 'required|numeric|min:0|max:100',
                'description' => 'nullable|string',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $loyalty->update($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Loyalty tier updated successfully',
                'data' => $loyalty
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update loyalty tier: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete loyalty tier (admin)
     */
    public function destroy($id)
    {
        try {
            $loyalty = Loyalty::findOrFail($id);
            $loyalty->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Loyalty tier deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete loyalty tier: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get loyalty tier by ID (admin)
     */
    public function show($id)
    {
        try {
            $loyalty = Loyalty::findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => $loyalty
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get loyalty tier: ' . $e->getMessage()
            ], 500);
        }
    }
}
