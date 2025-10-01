<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Investment;
use App\Models\Transaction;
use App\Services\WalletOps;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActiveInvestmentsController extends Controller
{
    public function index()
    {
        $activeInvestments = Investment::with(['user', 'investmentPlan'])
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $totalActiveInvestments = Investment::where('status', 'active')->count();
        $totalInvestedAmount = Investment::where('status', 'active')->sum('amount');

        return view('admin.pages.active-investments', compact(
            'activeInvestments', 
            'totalActiveInvestments', 
            'totalInvestedAmount'
        ));
    }

    public function cancelInvestment($id)
    {
        $investment = Investment::with(['user', 'investmentPlan'])->findOrFail($id);

        if ($investment->status !== 'active') {
            return redirect()->back()->with('error', 'Investment is not active');
        }

        DB::beginTransaction();
        try {
            // Cancel the investment
            if ($investment->cancel()) {
                // Log transaction
                Transaction::create([
                    'user_id' => $investment->user_id,
                    'type' => 'admin_canceled_investment',
                    'amount' => $investment->amount,
                    'status' => 'completed',
                    'description' => "Investment canceled by admin: {$investment->investmentPlan->plan_name}",
                    'reference_id' => $investment->id,
                ]);

                DB::commit();
                return redirect()->back()->with('success', 'Investment canceled successfully');
            } else {
                DB::rollBack();
                return redirect()->back()->with('error', 'Failed to cancel investment');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to cancel investment: ' . $e->getMessage());
        }
    }

    public function deactivateInvestment($id)
    {
        $investment = Investment::findOrFail($id);

        if ($investment->status !== 'active') {
            return redirect()->back()->with('error', 'Investment is not active');
        }

        DB::beginTransaction();
        try {
            // Mark as inactive
            $investment->status = 'inactive';
            $investment->end_date = now();
            $investment->save();

            // Unlock funds
            $wallet = $investment->user->wallet;
            if ($wallet) {
                WalletOps::unlockAmount($wallet, $investment->amount);
            }

            // Log transaction
            Transaction::create([
                'user_id' => $investment->user_id,
                'type' => 'admin_deactivated_investment',
                'amount' => $investment->amount,
                'status' => 'completed',
                'description' => "Investment deactivated by admin: {$investment->investmentPlan->plan_name}",
                'reference_id' => $investment->id,
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Investment deactivated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to deactivate investment: ' . $e->getMessage());
        }
    }

    public function getInvestmentDetails($id)
    {
        $investment = Investment::with(['user', 'investmentPlan'])->findOrFail($id);
        
        return response()->json([
            'investment' => $investment,
            'days_remaining' => $investment->days_remaining,
            'progress_percentage' => $investment->progress_percentage,
            'total_profit' => $investment->total_profit,
            'daily_profit' => $investment->daily_profit,
        ]);
    }
}
