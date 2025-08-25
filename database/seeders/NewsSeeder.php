<?php

namespace Database\Seeders;

use App\Models\News;
use Illuminate\Database\Seeder;

class NewsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $newsItems = [
            [
                'title' => 'Enhanced Mining Algorithm',
                'content' => 'We\'ve upgraded our mining algorithm to provide better rewards and more stable performance. The new system offers improved efficiency and higher success rates.',
                'type' => 'news',
                'status' => 'active',
                'created_by' => 1,
            ],
            [
                'title' => 'New Mining Rewards Structure',
                'content' => 'Introducing a new rewards structure that provides better incentives for active miners. Complete more sessions to unlock higher reward tiers.',
                'type' => 'update',
                'status' => 'active',
                'created_by' => 1,
            ],
            [
                'title' => 'System Maintenance Notice',
                'content' => 'Scheduled maintenance completed successfully. All mining operations are now running smoothly with improved performance and reliability.',
                'type' => 'info',
                'status' => 'active',
                'created_by' => 1,
            ],
            [
                'title' => 'New Investment Plans Available',
                'content' => 'We\'ve added new investment plans with higher returns and flexible terms. Check out the latest options in the Plans section.',
                'type' => 'news',
                'status' => 'active',
                'created_by' => 1,
            ],
            [
                'title' => 'Security Update',
                'content' => 'We\'ve implemented additional security measures to protect your account and transactions. Your funds are now even more secure.',
                'type' => 'update',
                'status' => 'active',
                'created_by' => 1,
            ],
        ];

        foreach ($newsItems as $news) {
            News::create($news);
        }
    }
}
