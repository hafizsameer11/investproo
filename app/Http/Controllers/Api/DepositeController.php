<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\DepositRequest;
use App\Models\Chain;
use App\Models\Deposit;
use App\Models\Investment;
use App\Models\InvestmentPlan;
use App\Models\Referrals;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class DepositeController extends Controller
{
    /**
     * Default referral percentages per level.
     * Move to config('referrals.levels') if you want runtime control.
     */
    private array $referralPercentages = [
        1 => 0.10, // 10%
        2 => 0.07, // 7%
        3 => 0.05, // 5%
        4 => 0.03, // 3%
        5 => 0.02, // 2%
    ];

    public function store(DepositRequest $request)
    {
        try {
            $data = $request->validated();
            $user = Auth::user();

            // Handle image upload
            if (isset($data['deposit_picture']) && $data['deposit_picture'] instanceof \Illuminate\Http\UploadedFile) {
                $img = $data['deposit_picture'];
                $ext = $img->getClientOriginalExtension();
                $imageName = time() . '.' . $ext;
                $img->move(public_path('/deposits'), $imageName);
                $data['deposit_picture'] = 'deposits/' . $imageName;
            } else {
                $data['deposit_picture'] = null;
            }

            $data['user_id'] = $user->id;
            $data['deposit_date'] = Carbon::now();
            $data['status'] = 'pending';
            $data['investment_plan_id'] = null;

            $chainId = $data['chain_id'] ?? null;
            $chainDetails = $chainId ? Chain::where('id', $chainId)->get() : null;

            $deposit = Deposit::create($data);

            $response = [
                'deposit_detail' => $deposit,
                'chain_detail'   => $chainDetails,
                'amount'         => $data['amount']
            ];

            return ResponseHelper::success($response, 'Deposit request submitted successfully. It will be reviewed and added to your wallet.');
        } catch (Exception $ex) {
            Log::error('Deposit error: '.$ex->getMessage(), ['trace' => $ex->getTraceAsString()]);
            return ResponseHelper::error('Deposit failed: ' . $ex->getMessage());
        }
    }

    /**
     * Activate plan using wallet balance and trigger referral payouts.
     */
    public function activatePlan(Request $request, string $planId)
{
    DB::beginTransaction();

    try {
        $data = $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        $user = Auth::user();
        if (!$user) {
            return ResponseHelper::error('User not authenticated', 401);
        }
        $actualUser=User::find($user->id);
        $actualUser->first_investment_date = Carbon::now();
        // $actualUser->loyalty_days = 0;
        $actualUser->save();

        // Lock investor wallet row while we compute/deduct
        $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->first();
        if (!$wallet) {
            return ResponseHelper::error('Wallet not found', 422);
        }

        $plan = InvestmentPlan::find($planId);
        if (!$plan) {
            return ResponseHelper::error('Investment plan not found', 404);
        }

        // --- Balance checks ---
        $totalBalance = ($wallet->deposit_amount ?? 0)
            + ($wallet->withdrawal_amount ?? 0)
            + ($wallet->profit_amount ?? 0)
            + ($wallet->bonus_amount ?? 0)
            + ($wallet->referral_amount ?? 0);

        if ($totalBalance < $data['amount']) {
            return ResponseHelper::error('Insufficient balance. Please deposit first.', 400);
        }

        if ($data['amount'] < $plan->min_amount) {
            return ResponseHelper::error("Minimum amount for this plan is $" . $plan->min_amount, 400);
        }

        if ($data['amount'] > $plan->max_amount) {
            return ResponseHelper::error("Maximum amount for this plan is $" . $plan->max_amount, 400);
        }

        // --- Deduct in a deterministic order ---
        $deductedAmount   = (float) $data['amount'];
        $remainingToDeduct = $deductedAmount;

        $take = function (float $from) use (&$remainingToDeduct): float {
            $use = min($from, $remainingToDeduct);
            $remainingToDeduct -= $use;
            return $from - $use;
        };

        // $wallet->deposit_amount    = $take($wallet->deposit_amount ?? 0);
        $wallet->withdrawal_amount = $take($wallet->withdrawal_amount ?? 0);
        $wallet->profit_amount     = $take($wallet->profit_amount ?? 0);
        $wallet->bonus_amount      = $take($wallet->bonus_amount ?? 0);
        $wallet->referral_amount   = $take($wallet->referral_amount ?? 0);
        $wallet->save();

        // --- Create investment ---
        // NOTE: your schema used $plan->duration_days earlier; if your column is "duration" in days or months, adjust this line accordingly.
        $investment = Investment::create([
            'user_id'             => $user->id,
            'investment_plan_id'  => $plan->id,
            'amount'              => $deductedAmount,
            'start_date'          => Carbon::now(),
            'end_date'            => Carbon::now()->addDays($plan->duration_days ?? ($plan->duration ?? 30)),
            'status'              => 'active',
        ]);

        // --- Track loyalty: Set first investment date if not set ---
        if (!$user->first_investment_date) {
            $user->update(['first_investment_date' => Carbon::now()]);
        }

        // --- Investor-side transaction (self) ---
        Transaction::create([
            'user_id'      => $user->id,              // owner (investor)
            'type'         => 'investment',
            'amount'       => $deductedAmount,
            'status'       => 'completed',
            'description'  => "Activated {$plan->plan_name}",
            'reference_id' => $user->id,              // counterparty = self (investor)
        ]);

        // --- Referral payouts: create transactions for recipients (parents) ---
        $referralReport = $this->payReferralTree($user, $deductedAmount, $plan);

        DB::commit();

        $response = [
            'investment'        => $investment,
            'plan'              => $plan,
            'amount_invested'   => $deductedAmount,
            'remaining_balance' => $totalBalance - $deductedAmount,
            'referral_payouts'  => $referralReport, // helpful for UI/debug
        ];

        return ResponseHelper::success($response, 'Plan activated successfully!');
    } catch (\Throwable $ex) {
        DB::rollBack();
        Log::error('Plan activation failed: ' . $ex->getMessage(), ['trace' => $ex->getTraceAsString()]);
        return ResponseHelper::error('Plan activation failed: ' . $ex->getMessage());
    }
}
/**
 * Pays the referral tree (up to 5 levels), creates a transaction for each recipient,
 * and updates their wallet.referral_amount.
 * - reference_id = investing user id (counterparty)
 * - user_id      = recipient id (who receives money)
 *
 * @return array<int,array<string,mixed>>
 */
protected function payReferralTree(User $investor, float $investmentAmount, $plan): array
{
    // Define referral percentages per level
    $percentages = [
        1 => 0.10, // 10%
        2 => 0.07, // 7%
        3 => 0.05, // 5%
        4 => 0.03, // 3%
        5 => 0.02, // 2%
    ];

    $level      = 1;
    $report     = [];
    $nextCode   = $investor->referral_code; // investor's referral_code points to parent user_code

    while ($level <= 5 && !empty($nextCode)) {
        // Lock the referrer user row (and later their wallet) to avoid race conditions
        $referrer = User::where('user_code', $nextCode)->lockForUpdate()->first();

        if (!$referrer) {
            Log::info("Referral tree break: no referrer for code {$nextCode} at level {$level}");
            break;
        }

        $bonus = round($investmentAmount * ($percentages[$level] ?? 0), 2);
        if ($bonus > 0) {
            // Ensure referrer wallet exists & lock it
            $refWallet = Wallet::where('user_id', $referrer->id)->lockForUpdate()->first();

            if (!$refWallet) {
                $refWallet = Wallet::create([
                    'user_id'          => $referrer->id,
                    'deposit_amount'   => 0,
                    'withdrawal_amount'=> 0,
                    'profit_amount'    => 0,
                    'bonus_amount'     => 0,
                    'referral_amount'  => 0,
                    'status'           => 'active',
                ]);
            }

            // Credit referral balance
            $refWallet->referral_amount = ($refWallet->referral_amount ?? 0) + $bonus;
            $refWallet->save();

            // Create a transaction FOR THE RECIPIENT (this is what you asked for)
            Transaction::create([
                'user_id'      => $referrer->id, // recipient
                'type'         => 'referral',
                'amount'       => $bonus,
                'status'       => 'completed',
                'description'  => "Referral bonus (L{$level}) from {$investor->name} on {$plan->plan_name}",
                'reference_id' => $investor->id, // who triggered it
            ]);

            $report[] = [
                'level'          => $level,
                'recipient_id'   => $referrer->id,
                'recipient_code' => $referrer->user_code,
                'bonus'          => $bonus,
            ];
        }

        // Climb up the tree: parent -> grandparent -> ...
        $nextCode = $referrer->referral_code;
        $level++;
    }

    return $report;
}


    /**
     * Pay referral bonuses up to 5 levels above the investing user.
     * Uses: parent = User where user_code == child.referral_code
     */
    protected function payReferralChainBonuses(User $investingUser, Investment $investment, $plan): void
    {
        // No referrer code: nothing to pay
        if (!$investingUser->referral_code) {
            Log::info('No referral code for investing user; skipping payouts', ['user_id' => $investingUser->id]);
            return;
        }

        $amount = (float) $investment->amount;
        $currentReferralCode = $investingUser->referral_code;
        $visited = []; // loop guard

        for ($level = 1; $level <= 5; $level++) {
            if (!$currentReferralCode) {
                break;
            }

            /** @var User|null $referrer */
            $referrer = User::where('user_code', $currentReferralCode)->first();

            if (!$referrer) {
                Log::info('Referrer not found for code', [
                    'code'  => $currentReferralCode,
                    'level' => $level,
                ]);
                break;
            }

            // loop/self guard
            if (isset($visited[$referrer->id]) || $referrer->id === $investingUser->id) {
                Log::warning('Referral loop detected, breaking', [
                    'investing_user_id' => $investingUser->id,
                    'referrer_user_id'  => $referrer->id,
                    'level'             => $level,
                ]);
                break;
            }
            $visited[$referrer->id] = true;

            $percent = $this->referralPercentages[$level] ?? 0;
            if ($percent <= 0) {
                // no payout defined for this level
                $currentReferralCode = $referrer->referral_code;
                continue;
            }

            $bonus = round($amount * $percent, 2);

            // --- Update / create aggregated row in Referrals table (if you keep it) ---
            $agg = Referrals::firstOrCreate(
                ['user_id' => $referrer->id],
                [
                    'referral_code'         => $referrer->user_code,
                    'referral_bonus_amount' => 0,
                    'total_referrals'       => 0,
                ]
            );
            // increment bonus safely
            $agg->referral_bonus_amount = (float)$agg->referral_bonus_amount + $bonus;
            $agg->save();

            // --- Ensure referrer wallet exists and credit referral amount ---
            $refWallet = Wallet::firstOrCreate(
                ['user_id' => $referrer->id],
                [
                    'deposit_amount'    => 0,
                    'withdrawal_amount' => 0,
                    'profit_amount'     => 0,
                    'bonus_amount'      => 0,
                    'referral_amount'   => 0,
                    'status'            => 'active',
                ]
            );
            $refWallet->referral_amount = (float)$refWallet->referral_amount + $bonus;
            $refWallet->save();

            // --- Create transaction record for traceability ---
            Transaction::create([
                'user_id'      => $referrer->id,
                'type'         => 'referral', // keep same type you already use
                'amount'       => $bonus,
                'status'       => 'completed',
                'description'  => "Referral bonus (L{$level}) from {$investingUser->name} on plan {$plan->plan_name}",
                'reference_id' => $investment->id, // reference the investment; you can add column 'reference_type' if needed
            ]);

            Log::info('Referral payout', [
                'level'              => $level,
                'referrer_user_id'   => $referrer->id,
                'referrer_user_code' => $referrer->user_code,
                'investing_user_id'  => $investingUser->id,
                'amount'             => $amount,
                'percentage'         => $percent,
                'bonus'              => $bonus,
            ]);

            // Move up one level
            $currentReferralCode = $referrer->referral_code;
        }
    }

    /**
     * Admin approves deposit -> move to wallet.
     */
    public function update(Request $request, $depositId)
    {
        $deposit = Deposit::findOrFail($depositId);

        $deposit->update(['status' => 'active']);

        Transaction::create([
            'user_id'    => $deposit->user_id,
            'deposit_id' => $deposit->id,
            'type'       => 'deposit',
            'amount'     => $deposit->amount,
            'status'     => 'completed',
            'description'=> 'Admin approved deposit',
        ]);

        Log::info('Deposit status updated for user', [
            'user_id'   => $deposit->user_id,
            'deposit_id'=> $deposit->id
        ]);

        $wallet = Wallet::firstOrCreate(
            ['user_id' => $deposit->user_id],
            [
                'deposit_amount'    => 0,
                'withdrawal_amount' => 0,
                'profit_amount'     => 0,
                'bonus_amount'      => 0,
                'referral_amount'   => 0,
                'status'            => 'active',
            ]
        );

        $wallet->deposit_amount = (float)$wallet->deposit_amount + (float)$deposit->amount;
        $wallet->save();

        return redirect()->route('deposits');
    }

    public function index()
    {
        $all_deposits     = Deposit::with(['user', 'investmentPlan', 'chain'])->latest()->get();
        $total_deposits   = Deposit::count();
        $pending_deposits = Deposit::where('status', 'pending')->count();
        $active_deposits  = Deposit::where('status', 'active')->count();
        $chains           = Chain::all();

        return view('admin.pages.deposit', compact(
            'all_deposits', 'total_deposits', 'active_deposits', 'pending_deposits', 'chains'
        ));
    }

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
        $deposit = Deposit::findOrFail($id);

        if ($deposit->deposit_picture && Storage::exists($deposit->deposit_picture)) {
            Storage::delete($deposit->deposit_picture);
        }

        $deposit->delete();

        return redirect()->back()->with('success', 'Deposit deleted successfully.');
    }

    // API: user deposits list
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
