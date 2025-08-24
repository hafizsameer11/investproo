<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\DepositRequest;
use App\Models\Chain;
use App\Models\Deposit;
use App\Models\Investment;
use App\Models\Transaction;
use App\Models\Wallet;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DepositeController extends Controller
{
    public function store(DepositRequest $request)
    {
        try
        {
            $data = $request->validated();
            
            $user = Auth::user();
            // $wallet = Wallet::where('user_id', $user->id)->first();
            
            // Handle image upload
            if (isset($data['deposit_picture']) && $data['deposit_picture'] && $data['deposit_picture'] instanceof \Illuminate\Http\UploadedFile) {
                $img = $data['deposit_picture'];
                $ext = $img->getClientOriginalExtension();
                $imageName = time() . '.' . $ext;
                $img->move(public_path('/deposits'), $imageName);
                $data['deposit_picture'] = 'deposits/' . $imageName;
            } else {
                // If no image uploaded, set to null
                $data['deposit_picture'] = null;
            }
            
            $data['user_id'] = Auth::id();
            $data['deposit_date'] = Carbon::now();
            $data['status'] = 'pending'; // Deposit needs approval
            $data['investment_plan_id'] = null; // No plan for deposit-only flow
            $chain_id = $data['chain_id'] ?? null;
            $chain_details = $chain_id ? Chain::where('id', $chain_id)->get() : null;
            
            $deposit = Deposit::create($data);
            $response = [
                'deposit_detail' => $deposit,
                'chain_detail' => $chain_details,
                'amount' => $data['amount']
            ];
            return ResponseHelper::success($response, 'Deposit request submitted successfully. It will be reviewed and added to your wallet.');
        } catch (Exception $ex) {
            return ResponseHelper::error('Deposit failed: ' . $ex->getMessage());
        }
    }

    public function activatePlan(Request $request, string $planId)
    {
        try
        {
            $data = $request->validate([
                'amount' => 'required|numeric|min:1',
            ]);
            
            $user = Auth::user();
            $wallet = Wallet::where('user_id', $user->id)->first();
            $plan = \App\Models\InvestmentPlan::find($planId);
            
            if (!$plan) {
                return ResponseHelper::error('Investment plan not found', 404);
            }
            
            // Calculate total available balance
            $totalBalance = ($wallet->withdrawal_amount ?? 0) + 
                           ($wallet->profit_amount ?? 0) + 
                           ($wallet->bonus_amount ?? 0) + 
                           ($wallet->referral_amount ?? 0);
            
            if ($totalBalance < $data['amount']) {
                return ResponseHelper::error('Insufficient balance. Please deposit first.', 400);
            }
            
            // Check if plan minimum amount is met
            if ($data['amount'] < $plan->min_amount) {
                return ResponseHelper::error("Minimum amount for this plan is $" . $plan->min_amount, 400);
            }
            
            // Check if plan maximum amount is not exceeded
            if ($data['amount'] > $plan->max_amount) {
                return ResponseHelper::error("Maximum amount for this plan is $" . $plan->max_amount, 400);
            }
            
            // Deduct amount from wallet
            $deductedAmount = min($data['amount'], $totalBalance);
            Wallet::where('user_id', $user->id)->update([
                'withdrawal_amount' => max(0, ($wallet->withdrawal_amount ?? 0) - $deductedAmount)
            ]);
            
            // Create investment
            $investment = Investment::create([
                'user_id' => $user->id,
                'investment_plan_id' => $planId,
                'amount' => $deductedAmount,
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addDays($plan->duration_days),
                'status' => 'active'
            ]);
            
            // Create transaction record
            Transaction::create([
                'user_id' => $user->id,
                'investment_id' => $investment->id,
                'type' => 'investment',
                'amount' => $deductedAmount,
                'status' => 'completed'
            ]);
            
            $response = [
                'investment' => $investment,
                'plan' => $plan,
                'amount_invested' => $deductedAmount,
                'remaining_balance' => $totalBalance - $deductedAmount
            ];
            
            return ResponseHelper::success($response, 'Plan activated successfully!');
        } catch (Exception $ex) {
            return ResponseHelper::error('Plan activation failed: ' . $ex->getMessage());
        }
    }

   public function update(Request $request, $depositId)
{
    

    $deposit = Deposit::findOrFail($depositId);

    // Update deposit status
    $deposit->update([
        'status'=>'active'
    ]);

    // Create investment for the user
    // Investment::create([
    //     'user_id' => $deposit->user_id,
    //     'investment_plan_id' => $deposit->investment_plan_id,
    //     'start_date' => Carbon::now(),
    //     'end_date' => Carbon::now()->addMonth(),
    //     'status' => 'active'
    // ]);
    Transaction::create([
        'user_id'=> $deposit->user_id,
        'deposit_id'=> $deposit->id
    ]);
    Wallet::where('user_id', $deposit->user_id)->update([
            'deposit_amount' => $deposit->amount,
        ]);
    return redirect()->route('deposits');
}


    public function index()
    {
        $all_deposits = Deposit::with(['user', 'investmentPlan', 'chain'])->latest()->get();
        $total_deposits = Deposit::count();
        $pending_deposits = Deposit::where('status', 'pending')->count();
        $active_deposits = Deposit::where('status', 'active')->count();
        $chains = Chain::all();
        return view('admin.pages.deposit', compact('all_deposits','total_deposits', 'active_deposits','pending_deposits', 'chains'));
}
// update chain address
public function updateChain(Request $request, $id)
    {
         $request->validate([
        'chain_id' => 'nullable',
    ]);
    $deposit = Deposit::findOrFail($id);
    $deposit->chain_id = $request->chain_id;
    $deposit->save();

        return redirect()->route('deposits')->with('success', 'Deposit chain updated successfully.');
    }
    public function destroy($id)
{
    // dd($id);
    $deposit = Deposit::findOrFail($id);

    // Optional: Delete deposit image from storage if exists
    if ($deposit->deposit_picture && Storage::exists($deposit->deposit_picture)) {
        Storage::delete($deposit->deposit_picture);
    }

    // Delete the deposit record
    $deposit->delete();

    return redirect()->back()->with('success', 'Deposit deleted successfully.');
}

    // Get user deposits
    public function userDeposits()
    {
        try {
            $user = Auth::user();
            $deposits = Deposit::where('user_id', $user->id)
                ->with(['investmentPlan', 'chain'])
                ->orderBy('created_at', 'desc')
                ->get();
                
            return ResponseHelper::success($deposits, 'User deposits retrieved successfully');
        } catch (Exception $ex) {
            return ResponseHelper::error('Failed to retrieve deposits: ' . $ex->getMessage());
        }
    }
}
