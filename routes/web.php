<?php

use App\Http\Controllers\Admin\ChainController;
use App\Http\Controllers\Admin\InvestmentPlanController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\ReferralController;
use App\Http\Controllers\Admin\NewsController as AdminNewsController;
use App\Http\Controllers\Admin\KycController as AdminKycController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DepositeController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WithdrawalController;
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
// deposite
Route::get('/deposit', [DepositeController::class, 'index'])->name('deposits');
Route::get('/update/{depositId}', [DepositeController::class, 'update'])->name('deposits.verify');
Route::delete('/deposit-destroy/{id}', [DepositeController::class, 'destroy'])->name('deposits.destroy');
Route::put('/updateChain/{id}', [DepositeController::class, 'updateChain'])->name('deposits.updateChain');

// withdrawal
Route::get('withdrawal', [WithdrawalController::class, 'index'])->name('withdrawals');
Route::put('/withdrawals/{id}', [WithdrawalController::class, 'update'])->name('withdrawals.approve');
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
Route::get('/news', [AdminNewsController::class, 'index'])->name('news.index');
Route::get('/news/create', [AdminNewsController::class, 'create'])->name('news.create');
Route::post('/news', [AdminNewsController::class, 'store'])->name('news.store');
Route::get('/news/{id}/edit', [AdminNewsController::class, 'edit'])->name('news.edit');
Route::put('/news/{id}', [AdminNewsController::class, 'update'])->name('news.update');
Route::delete('/news/{id}', [AdminNewsController::class, 'destroy'])->name('news.destroy');

// KYC management
Route::get('/kyc', [AdminKycController::class, 'index'])->name('kyc.index');
Route::get('/kyc/pending', [AdminKycController::class, 'pending'])->name('kyc.pending');
Route::get('/kyc/{id}', [AdminKycController::class, 'show'])->name('kyc.show');
Route::put('/kyc/{id}/review', [AdminKycController::class, 'review'])->name('kyc.review');
Route::delete('/kyc/{id}', [AdminKycController::class, 'destroy'])->name('kyc.destroy');
});
