<?php

/**
 * Loyalty System Setup Script
 * Run this script when the database is available to set up the loyalty system
 */

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Artisan;

echo "Setting up Loyalty System...\n";

try {
    // Run migrations
    echo "Running migrations...\n";
    Artisan::call('migrate');
    echo "Migrations completed successfully!\n";

    // Seed loyalty data
    echo "Seeding loyalty data...\n";
    Artisan::call('db:seed', ['--class' => 'LoyaltySeeder']);
    echo "Loyalty data seeded successfully!\n";

    echo "\n✅ Loyalty system setup completed successfully!\n";
    echo "You can now:\n";
    echo "1. Access the admin panel and go to 'Loyalty Management'\n";
    echo "2. Create and manage loyalty tiers\n";
    echo "3. Users will see loyalty information in their dashboard and withdrawal screens\n";

} catch (Exception $e) {
    echo "❌ Error during setup: " . $e->getMessage() . "\n";
    echo "Please make sure your database is running and configured properly.\n";
}





