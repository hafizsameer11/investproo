<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Investment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvestmentController extends Controller
{
    public function investment()
    {
        $investment = Investment::where('user_id', Auth::id())->get();
        return ResponseHelper::success($investment,"Your Investment");
    }
}
