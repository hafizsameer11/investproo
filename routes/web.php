<?php

use App\Http\Controllers\Admin\ChainController;
use App\Http\Controllers\Admin\InvestmentPlanController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\ReferralController;
use App\Http\Controllers\Admin\NewsController as AdminNewsController;
use App\Http\Controllers\Admin\KycController as AdminKycController;
use App\Http\Controllers\Admin\LoyaltyController as AdminLoyaltyController;
use App\Http\Controllers\Admin\RewardsController;
use App\Http\Controllers\Admin\ActiveInvestmentsController;
use App\Http\Controllers\Admin\MiningSessionsController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DepositeController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WithdrawalController;
use App\Http\Controllers\NewsController;
use Illuminate\Support\Facades\Route;







// Route::get('/', function () {
//     return view('admin.index');
// });
Route::get('/login', [UserController::class, 'showLoginForm'])->name('login');
Route::post('/login', [UserController::class, 'Adminlogin'])->name('loginMatch');
Route::get('/logout', [UserController::class, 'Adminlogout'])->name('logout');
Route::middleware(['auth'])->group(function () {
    // Your protected routes go here
   Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
// users
Route::get('/kyc/{user_id}', [UserController::class, 'kyc'])->name('kyc');
Route::get('/user-page', [UserController::class, 'user_page'])->name('users');
Route::delete('/deleteUser/{id}', [UserController::class, 'deleteUser'])->name('destroy.user');
Route::get('/user/{id}/detail', [UserController::class, 'userDetail'])->name('user.detail');
// deposite
Route::get('/deposit', [DepositeController::class, 'index'])->name('deposits');
Route::get('/update/{depositId}', [DepositeController::class, 'update'])->name('deposits.verify');
Route::delete('/deposit-destroy/{id}', [DepositeController::class, 'destroy'])->name('deposits.destroy');
Route::put('/updateChain/{id}', [DepositeController::class, 'updateChain'])->name('deposits.updateChain');

// withdrawal
Route::get('withdrawal', [WithdrawalController::class, 'index'])->name('withdrawals');
Route::post('/withdrawals/{id}/approve', [WithdrawalController::class, 'approve'])->name('withdrawals.approve');
Route::post('/withdrawals/{id}/reject', [WithdrawalController::class, 'reject'])->name('withdrawals.reject');
Route::delete('/withdrawals/{id}', [WithdrawalController::class, 'destroy'])->name('withdrawals.destroy');

// investment plans
Route::get('/plans', [PlanController::class, 'index'])->name('plans');
Route::post('/store', [PlanController::class, 'store'])->name('plans.store');
Route::put('/plan-update/{id}', [PlanController::class, 'update'])->name('plans.update');
Route::delete('/destroy/{id}', [PlanController::class, 'destroy'])->name('plans.destroy');
// transaction
Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions');
// chains
Route::resource('/chains', ChainController::class);
// referrals
Route::get('/referrals', [ReferralController::class, 'index'])->name('referrals');

// News management
// Route::get('/news', [AdminNewsController::class, 'index'])->name('news.index');
// Route::get('/news/create', [AdminNewsController::class, 'create'])->name('news.create');
// Route::post('/news', [AdminNewsController::class, 'store'])->name('news.store');
// Route::get('/news/{id}/edit', [AdminNewsController::class, 'edit'])->name('news.edit');
// Route::put('/news/{id}', [AdminNewsController::class, 'update'])->name('news.update');
// Route::delete('/news/{id}', [AdminNewsController::class, 'destroy'])->name('news.destroy');

// KYC management
Route::get('/kyc', [AdminKycController::class, 'index'])->name('kyc.index');
Route::get('/kyc/pending', [AdminKycController::class, 'pending'])->name('kyc.pending');
Route::get('/kyc/{id}', [AdminKycController::class, 'show'])->name('kyc.show');
Route::put('/kyc/{id}/review', [AdminKycController::class, 'review'])->name('kyc.review');
Route::delete('/kyc/{id}', [AdminKycController::class, 'destroy'])->name('kyc.destroy');
Route::get('/adminDownload/{id}', [AdminKycController::class, 'download'])->name('kyc.adminDownload');
Route::get('/document-view/{id}', [AdminKycController::class, 'viewFile'])->name('document.view');

// Loyalty management
Route::get('/loyalty', [AdminLoyaltyController::class, 'index'])->name('loyalty.index');
Route::get('/loyalty/create', [AdminLoyaltyController::class, 'create'])->name('loyalty.create');
Route::post('/loyalty', [AdminLoyaltyController::class, 'store'])->name('loyalty.store');
Route::get('/loyalty/{id}/edit', [AdminLoyaltyController::class, 'edit'])->name('loyalty.edit');
Route::put('/loyalty/{id}', [AdminLoyaltyController::class, 'update'])->name('loyalty.update');
Route::delete('/loyalty/{id}', [AdminLoyaltyController::class, 'destroy'])->name('loyalty.destroy');
Route::put('/loyalty/{id}/toggle-status', [AdminLoyaltyController::class, 'toggleStatus'])->name('loyalty.toggle-status');

// Rewards management
Route::get('/rewards', [RewardsController::class, 'index'])->name('rewards.index');
Route::post('/rewards/{userId}/mining', [RewardsController::class, 'updateMiningReward'])->name('rewards.mining');
Route::post('/rewards/{userId}/referral', [RewardsController::class, 'updateReferralBonus'])->name('rewards.referral');
Route::post('/rewards/{userId}/loyalty', [RewardsController::class, 'updateLoyaltyBonus'])->name('rewards.loyalty');
Route::get('/rewards/{userId}/history', [RewardsController::class, 'getEditHistory'])->name('rewards.history');

// Active investments management
Route::get('/active-investments', [ActiveInvestmentsController::class, 'index'])->name('active-investments.index');
Route::post('/active-investments/{id}/cancel', [ActiveInvestmentsController::class, 'cancelInvestment'])->name('active-investments.cancel');
Route::post('/active-investments/{id}/deactivate', [ActiveInvestmentsController::class, 'deactivateInvestment'])->name('active-investments.deactivate');
Route::get('/active-investments/{id}/details', [ActiveInvestmentsController::class, 'getInvestmentDetails'])->name('active-investments.details');

// Mining sessions management
Route::get('/mining-sessions', [MiningSessionsController::class, 'index'])->name('mining-sessions.index');
Route::get('/mining-sessions/user/{userId}', [MiningSessionsController::class, 'getUserSessions'])->name('mining-sessions.user');
Route::post('/mining-sessions/{id}/reward', [MiningSessionsController::class, 'updateReward'])->name('mining-sessions.reward');
Route::post('/mining-sessions/{id}/activate', [MiningSessionsController::class, 'activateSession'])->name('mining-sessions.activate');
Route::post('/mining-sessions/{id}/deactivate', [MiningSessionsController::class, 'deactivateSession'])->name('mining-sessions.deactivate');
Route::get('/mining-sessions/stats', [MiningSessionsController::class, 'getSessionStats'])->name('mining-sessions.stats');

// User management - help users claim amounts and update wallets
Route::get('/user-management', [UserManagementController::class, 'index'])->name('user-management.index');
Route::post('/user-management/{userId}/help-claim', [UserManagementController::class, 'helpClaimAmount'])->name('user-management.help-claim');
Route::post('/user-management/{userId}/update-wallet', [UserManagementController::class, 'updateWallet'])->name('user-management.update-wallet');
Route::get('/user-management/{userId}/claimable-amounts', [UserManagementController::class, 'getUserClaimableAmounts'])->name('user-management.claimable-amounts');
Route::post('/user-management/{userId}/force-claim-mining', [UserManagementController::class, 'forceClaimMiningRewards'])->name('user-management.force-claim-mining');
Route::get('/user-management/{userId}/mining-sessions', [UserManagementController::class, 'getUserMiningSessions'])->name('user-management.mining-sessions');
});

  Route::get('/news', [NewsController::class, 'index'])->name('news.index');
    Route::get('/news/create', [NewsController::class, 'create'])->name('news.create');
    Route::post('/news', [NewsController::class, 'store'])->name('news.store');
    Route::get('/news/{news}/edit', [NewsController::class, 'edit'])->name('news.edit');
    Route::put('/news/{news}', [NewsController::class, 'update'])->name('news.update');
    Route::delete('/news/{news}', [NewsController::class, 'destroy'])->name('news.destroy');

    // Lightweight endpoints
    Route::get('/news/{news}', [NewsController::class, 'show'])->name('news.show'); // used by modal
    Route::put('/news/{news}/status', [NewsController::class, 'updateStatus'])->name('news.status'); // approve/archive/activate