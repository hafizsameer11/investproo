<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Referrals;
use Illuminate\Http\Request;

class ReferralController extends Controller
{
     public function index()
    {
        // Eager load user relationship and paginate
        $referrals = Referrals::with('user')->latest()->paginate(20);

        return view('admin.pages.referrals', compact('referrals'));
    }
}
