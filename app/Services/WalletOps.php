<?php

namespace App\Services;

use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WalletOps
{
    /**
     * Debit amount from wallet using priority order
     * Priority: profit_amount → referral_amount → deposit_amount
     * 
     * @param Wallet $wallet
     * @param float $amount
     * @return array Breakdown of deduction
     */
    public static function debitByPriority(Wallet $wallet, float $amount): array
    {
        $breakdown = [
            'profit_amount' => 0,
            'referral_amount' => 0,
            'deposit_amount' => 0,
            'total_deducted' => 0
        ];

        $remaining = $amount;

        // Priority 1: Deduct from profit_amount
        if ($remaining > 0 && $wallet->profit_amount > 0) {
            $deductFromProfit = min($remaining, $wallet->profit_amount);
            $wallet->profit_amount -= $deductFromProfit;
            $breakdown['profit_amount'] = $deductFromProfit;
            $remaining -= $deductFromProfit;
        }

        // Priority 2: Deduct from referral_amount
        if ($remaining > 0 && $wallet->referral_amount > 0) {
            $deductFromReferral = min($remaining, $wallet->referral_amount);
            $wallet->referral_amount -= $deductFromReferral;
            $breakdown['referral_amount'] = $deductFromReferral;
            $remaining -= $deductFromReferral;
        }

        // Priority 3: Deduct from deposit_amount
        if ($remaining > 0 && $wallet->deposit_amount > 0) {
            $deductFromDeposit = min($remaining, $wallet->deposit_amount);
            $wallet->deposit_amount -= $deductFromDeposit;
            $breakdown['deposit_amount'] = $deductFromDeposit;
            $remaining -= $deductFromDeposit;
        }

        $breakdown['total_deducted'] = $amount - $remaining;
        
        // Save wallet changes
        $wallet->save();

        // Log transaction for traceability
        if ($breakdown['total_deducted'] > 0) {
            Transaction::create([
                'user_id' => $wallet->user_id,
                'type' => 'wallet_debit',
                'amount' => $breakdown['total_deducted'],
                'status' => 'completed',
                'description' => "Wallet debit breakdown: Profit: {$breakdown['profit_amount']}, Referral: {$breakdown['referral_amount']}, Deposit: {$breakdown['deposit_amount']}",
            ]);
        }

        return $breakdown;
    }

    /**
     * Refund amount back to wallet buckets
     * 
     * @param Wallet $wallet
     * @param float $amount
     * @param string $bucket Priority bucket to refund to
     */
    public static function refundToBucket(Wallet $wallet, float $amount, string $bucket = 'deposit_amount'): void
    {
        switch ($bucket) {
            case 'profit_amount':
                $wallet->profit_amount += $amount;
                break;
            case 'referral_amount':
                $wallet->referral_amount += $amount;
                break;
            case 'deposit_amount':
            default:
                $wallet->deposit_amount += $amount;
                break;
        }

        $wallet->save();

        // Log refund transaction
        Transaction::create([
            'user_id' => $wallet->user_id,
            'type' => 'wallet_refund',
            'amount' => $amount,
            'status' => 'completed',
            'description' => "Refund to {$bucket} bucket",
        ]);
    }

    /**
     * Lock amount for investment
     * 
     * @param Wallet $wallet
     * @param float $amount
     */
    public static function lockAmount(Wallet $wallet, float $amount): void
    {
        $wallet->locked_amount += $amount;
        $wallet->save();

        Transaction::create([
            'user_id' => $wallet->user_id,
            'type' => 'amount_locked',
            'amount' => $amount,
            'status' => 'completed',
            'description' => "Amount locked for investment",
        ]);
    }

    /**
     * Unlock amount from investment
     * 
     * @param Wallet $wallet
     * @param float $amount
     */
    public static function unlockAmount(Wallet $wallet, float $amount): void
    {
        $wallet->locked_amount = max(0, $wallet->locked_amount - $amount);
        $wallet->save();

        Transaction::create([
            'user_id' => $wallet->user_id,
            'type' => 'amount_unlocked',
            'amount' => $amount,
            'status' => 'completed',
            'description' => "Amount unlocked from investment",
        ]);
    }
}
