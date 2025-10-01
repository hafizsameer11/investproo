<?php

namespace Database\Seeders;

use App\Models\Wallet;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WalletTotalBalanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         Wallet::chunk(150, function ($wallets) {
            foreach ($wallets as $wallet) {
                $wallet->total_balance = 
                    ($wallet->deposit_amount ?? 0)
                    + ($wallet->withdrawal_amount ?? 0)
                    + ($wallet->profit_amount ?? 0)
                    + ($wallet->bonus_amount ?? 0)
                    + ($wallet->referral_amount ?? 0);

                $wallet->save();
            }
        });
    }
}
