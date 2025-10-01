<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Investment;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvestmentController extends Controller
{
    public function investment()
    {
        try {
            $userId = Auth::id();
            Log::info('Fetching investments for user ID: ' . $userId);
            
            $investments = Investment::with(['investmentPlan', 'user'])
                ->where('user_id', $userId)
                ->where('status', 'active')
                ->orderBy('created_at', 'desc')
                ->get();

            Log::info('Found ' . $investments->count() . ' active investments for user ' . $userId);

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
                    'daily_profit' => $investment->daily_profit,
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
     public function cancelInvestment($id)
    {
        DB::beginTransaction();
        try {
            $userId = Auth::id();

            $investment = Investment::where('id', $id)
                ->where('user_id', $userId)
                ->where('status', 'active')
                ->lockForUpdate()
                ->first();

            if (!$investment) {
                return ResponseHelper::error('Investment not found or already completed/canceled.', 404);
            }

            $wallet = Wallet::where('user_id', $userId)->lockForUpdate()->first();
            if (!$wallet) {
                return ResponseHelper::error('Wallet not found', 422);
            }

            // release locked funds back
            $wallet->locked_amount -= $investment->amount;
            if ($wallet->locked_amount < 0) {
                $wallet->locked_amount = 0; // safeguard
            }

            // return principal to deposit_amount (or keep it as available balance)
            // $wallet->deposit_amount += $investment->amount;
            $wallet->save();

            // update investment
            $investment->status = 'canceled';
            $investment->end_date = now();
            $investment->save();

            // log transaction
            Transaction::create([
                'user_id'     => $userId,
                'type'        => 'canceled_investment',
                'amount'      => $investment->amount,
                'status'      => 'completed',
                'description' => "Canceled investment: {$investment->investmentPlan->plan_name}",
                'reference_id'=> $investment->id,
            ]);

            DB::commit();

            return ResponseHelper::success(null, 'Investment canceled successfully, funds returned to wallet.');
        } catch (\Throwable $ex) {
            DB::rollBack();
            Log::error('Cancel investment failed: '.$ex->getMessage(), ['trace'=>$ex->getTraceAsString()]);
            return ResponseHelper::error('Failed to cancel investment: '.$ex->getMessage());
        }
    }
}
