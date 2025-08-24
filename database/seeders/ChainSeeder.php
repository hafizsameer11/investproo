<?php

namespace Database\Seeders;

use App\Models\Chain;
use Illuminate\Database\Seeder;

class ChainSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $chains = [
            [
                'type' => 'USDT (TRC20)',
                'address' => 'TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t',
                'status' => 'active',
            ],
            [
                'type' => 'USDT (ERC20)',
                'address' => '0x742d35Cc6634C0532925a3b8D4C9db96C4b4d8b6',
                'status' => 'active',
            ],
            [
                'type' => 'Bitcoin (BTC)',
                'address' => 'bc1qxy2kgdygjrsqtzq2n0yrf2493p83kkfjhx0wlh',
                'status' => 'active',
            ],
            [
                'type' => 'Ethereum (ETH)',
                'address' => '0x742d35Cc6634C0532925a3b8D4C9db96C4b4d8b6',
                'status' => 'active',
            ],
            [
                'type' => 'Dogecoin (DOGE)',
                'address' => 'D8j6vFfLhu6LqKto2KQY7iNfLwS9X7Y2Z1',
                'status' => 'active',
            ],
        ];

        foreach ($chains as $chain) {
            Chain::create($chain);
        }
    }
}
