<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Referrals;
use Illuminate\Http\Request;

class ReferralController extends Controller
{
     public function index()
    {
        // Eager load user relationship
        $referrals = Referrals::with('user')->get();

        return view('admin.pages.referrals', compact('referrals'));
    }
}
