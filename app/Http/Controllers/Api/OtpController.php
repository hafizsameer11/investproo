<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class OtpController extends Controller
{
    protected $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    /**
     * Send OTP for signup
     */
    public function sendSignupOtp(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email'
            ]);

            if ($validator->fails()) {
                return ResponseHelper::error($validator->errors()->first(), 422);
            }

            $result = $this->otpService->sendSignupOtp($request->email);

            if ($result['success']) {
                return ResponseHelper::success($result, $result['message']);
            }

            return ResponseHelper::error($result['message'], 400);
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to send OTP: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Send OTP for login
     */
    public function sendLoginOtp(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email'
            ]);

            if ($validator->fails()) {
                return ResponseHelper::error($validator->errors()->first(), 422);
            }

            $result = $this->otpService->sendLoginOtp($request->email);

            if ($result['success']) {
                return ResponseHelper::success($result, $result['message']);
            }

            return ResponseHelper::error($result['message'], 400);
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to send OTP: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Send OTP for withdrawal
     */
    public function sendWithdrawalOtp(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return ResponseHelper::error('User not authenticated', 401);
            }

            $result = $this->otpService->sendWithdrawalOtp($user->email, $user->id);

            if ($result['success']) {
                return ResponseHelper::success($result, $result['message']);
            }

            return ResponseHelper::error($result['message'], 400);
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to send OTP: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Verify OTP
     */
    public function verifyOtp(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'otp' => 'required|string|size:6',
                'type' => 'required|in:signup,login,withdrawal'
            ]);

            if ($validator->fails()) {
                return ResponseHelper::error($validator->errors()->first(), 422);
            }

            $result = $this->otpService->verifyOtp(
                $request->email,
                $request->otp,
                $request->type
            );

            if ($result['success']) {
                return ResponseHelper::success($result, $result['message']);
            }

            return ResponseHelper::error($result['message'], 400);
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to verify OTP: ' . $e->getMessage(), 500);
        }
    }
}
