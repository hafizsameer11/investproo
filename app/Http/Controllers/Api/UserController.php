<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\Referrals;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Otp;
use App\Services\OtpService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UserController extends Controller
{
    protected $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }
    // register
    public function register(UserRequest $request)
    {
        try {
            $data = $request->validated();

            // Check if OTP is provided and valid
            if (!isset($data['otp'])) {
                return ResponseHelper::error('OTP is required for registration', 422);
            }

            // Verify OTP
            $otpResult = $this->otpService->verifyOtp($data['email'], $data['otp'], 'signup');
            if (!$otpResult['success']) {
                return ResponseHelper::error($otpResult['message'], 422);
            }

            // Check if user already exists
            $existingUser = User::where('email', $data['email'])->first();
            if ($existingUser) {
                return ResponseHelper::error('User with this email already exists', 422);
            }

            $data['user_code'] = strtolower(str_replace(' ', '', $data['name'])) . rand(100, 999);
            $data['password'] = Hash::make($data['password']);

            $user = User::create($data);
            $this->createWallet($user);

            // Create referral record
            Referrals::create([
                'referral_code' => $user->user_code,
                'user_id' => $user->id
            ]);

            // Process referral if provided
            if (!empty($user['referral_code'])) {
                $referralRecord = Referrals::where('referral_code', $user['referral_code'])->first();

                if ($referralRecord) {
                    $bonusAmount = $referralRecord->bonus_amount ?? 0;
                    $perUserBonus = 0;

                    // Get current total referrals
                    $total = $referralRecord->total_referrals;
                    $total += 1; // Increment locally

                    // Determine per-user bonus based on new total
                    if ($total <= 15) {
                        $perUserBonus = 15;
                    } elseif ($total <= 50) {
                        $perUserBonus = 20;
                    } elseif ($total <= 100) {
                        $perUserBonus = 25;
                    } elseif ($total <= 200) {
                        $perUserBonus = 30;
                    }

                    // Determine milestone bonus
                    if ($total == 15) {
                        $bonusAmount = 100;
                    } elseif ($total == 50) {
                        $bonusAmount = 200;
                    } elseif ($total == 100) {
                        $bonusAmount = 250;
                    } elseif ($total == 200) {
                        $bonusAmount = 300;
                    }

                    // Update values and save
                    if ($referralRecord) {
                        Referrals::where('id', $referralRecord->id)->update([
                            'total_referrals' => $total,
                            'per_user_referral' => $perUserBonus,
                            'referral_bonus_amount' => $bonusAmount
                        ]);
                    }
                }

                if (!empty($user['referral_code'])) {
                    // Multi-level referral chain
                    $this->processReferralChain($user, $user['referral_code']);
                }
            }

            return ResponseHelper::success($user, 'User is created successfully');
        } catch (Exception $ex) {
            return ResponseHelper::error('User is not created: ' . $ex->getMessage());
        }
    }
    protected function processReferralChain($newUser, $referralCode, $baseBonus = 100)
    {
        // Define profit chain percentages per level
        $profitChainPercentages = [
            1 => 1.00,  // 100%
            2 => 0.75,  // 75%
            3 => 0.50,  // 50%
            4 => 0.25,  // 25%
            5 => 0.05,  // 5%
        ];

        $currentReferralCode = $referralCode;
        $level = 1;

        while ($level <= 5 && $currentReferralCode) {
            // Find the referrer user by their user_code
            $referrerUser = User::where('user_code', $currentReferralCode)->first();

            if (!$referrerUser) {
                break; // Stop if no referrer found
            }

            // Find or create referral record for this referrer
            $referralRecord = Referrals::firstOrCreate(
                ['user_id' => $referrerUser->id],
                ['referral_code' => $referrerUser->user_code]
            );

            // Calculate level-based bonus
            $levelBonus = $baseBonus * $profitChainPercentages[$level];

            // Update total referrals
            $referralRecord->total_referrals += 1;

            // Add the bonus for this level
            $referralRecord->referral_bonus_amount += $levelBonus;

            // Save level bonus temporarily (could be used for audit or logs)
            $referralRecord->per_user_referral = $levelBonus;

            $referralRecord->save();

            // Go up the chain to next level
            $currentReferralCode = $referrerUser->referral_code;

            $level++;
        }
    }

    // user approval
    public function kyc($id)
    {
        try {
            $user = User::where('id', $id)->update([
                'status' => 'active',
            ]);

            return redirect()->route('users');
        } catch (Exception $ex) {
            return ResponseHelper::error('User KYC is not verified ' . $ex->getMessage());
        }
    }
    // login
  public function login(Request $request)
{
    try {
        $data = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
            'otp'      => 'required|string|size:6',
        ]);

        // --- Step 1: Verify OTP ---
        $otpResult = $this->otpService->verifyOtp($data['email'], $data['otp'], 'login');
        if (!$otpResult['success']) {
            return ResponseHelper::error([
                'field'   => 'otp',
                'message' => $otpResult['message']
            ], 422);
        }

        // --- Step 2: Check user existence ---
        $user = \App\Models\User::where('email', $data['email'])->first();
        if (!$user) {
            return ResponseHelper::error([
                'field'   => 'email',
                'message' => 'No account found with this email address'
            ], 404);
        }

        // --- Step 3: Verify password ---
        if (!\Hash::check($data['password'], $user->password)) {
            return ResponseHelper::error([
                'field'   => 'password',
                'message' => 'The password is incorrect'
            ], 401);
        }

        // --- Step 4: Successful login ---
        Auth::login($user);
        $token = $user->createToken("API Token")->plainTextToken;

        return response()->json([
            'status'     => true,
            'message'    => 'Login Successfully',
            'token_type' => 'bearer',
            'token'      => $token,
            'user'       => $user,
        ], 200);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return ResponseHelper::error([
            'field'   => $e->errors(),
            'message' => 'Validation failed'
        ], 422);
    } catch (Exception $ex) {
        return ResponseHelper::error('Login failed: ' . $ex->getMessage(), 500);
    }
}


    // all users
    public function allUser()
    {
        try {
            $users = User::all();
            return ResponseHelper::success($users, "All Users");
        } catch (Exception $ex) {
            return ResponseHelper::error('Don"t fetch all the users' . $ex);
        }
    }

    // single user/ profile
    public function profile()
    {
        try {
            \Log::info('Profile request received', [
                'auth_id' => Auth::id(),
                'auth_check' => Auth::check(),
                'user' => Auth::user()
            ]);

            $user = User::find(Auth::id());
            if (!$user) {
                Log::error('User not found for ID: ' . Auth::id());
                return ResponseHelper::error('User not found');
            }

            Log::info('Profile data returned', ['user_id' => $user->id, 'email' => $user->email]);
            return ResponseHelper::success($user, "Your profile");
        } catch (Exception $ex) {
            Log::error('Profile error: ' . $ex->getMessage());
            return ResponseHelper::error('Not fetch the single user datas' . $ex);
        }
    }
    // update
    public function update(UserRequest $request)
    {
        try {
            Log::info('Profile update request received', [
                'auth_id' => Auth::id(),
                'request_data' => $request->all()
            ]);

            $data = $request->validated();
            $user = User::find(Auth::id());

            if (!$user) {
                Log::error('User not found for ID: ' . Auth::id());
                return ResponseHelper::error('User not found');
            }

            // Check if email is being changed and if it's already taken
            if (isset($data['email']) && $data['email'] !== $user->email) {
                $existingUser = User::where('email', $data['email'])->where('id', '!=', $user->id)->first();
                if ($existingUser) {
                    return ResponseHelper::error('Email is already taken');
                }
            }

            // Hash password if provided
            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            // Update user
            $user->update($data);

            Log::info('Profile updated successfully', [
                'user_id' => $user->id,
                'updated_fields' => array_keys($data)
            ]);

            return ResponseHelper::success($user, 'User profile updated successfully');
        } catch (Exception $ex) {
            Log::error('Profile update error: ' . $ex->getMessage());
            return ResponseHelper::error('Failed to update profile: ' . $ex->getMessage());
        }
    }

    // delete user
    public function deleteUser($userId)
    {
        try {
            $user = User::find($userId);

            User::where('id', $userId)->delete();
            return redirect()->route('users');
        } catch (Exception $e) {
            return ResponseHelper::error('User deletion failed', 500);
        }
    }

    // create wallet
    public function createWallet($user)
    {
        $wallet = Wallet::create([
            'user_id' => $user->id,
            'status' => 'active'
        ]);
        Log::info('Wallet created for user', ['user_id' => $user->id, 'wallet_id' => $wallet->id]);
        return $wallet;
    }

    // logout
    public function logout()
    {
        try {
            $user = Auth::user();
            if ($user) {
                $user->tokens()->delete();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Logged out successfully.'
                ]);
            }
            return ResponseHelper::success($user, 'User logged out successfully');
        } catch (Exception $e) {
            return ResponseHelper::error($e->getMessage());
        }
    }

    // user-page
    public function user_page()
    {
        $all_users = User::get();
        $total_users = User::count();
        $active_users = User::where('status', 'active')->count();
        $inactive_user = User::where('status', 'inactive')->count();

        return view('admin.pages.users', compact('all_users', 'total_users', 'active_users', 'inactive_user'));
    }


    public function showLoginForm()
    {
        return view('admin.login');
    }

    public function Adminlogin(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if (Auth::attempt($credentials)) {
            // Optional: Ensure user is admin (if you use is_admin flag)
            if (auth()->user()->role === 'admin') {
                return redirect()->route('dashboard')->with('success', 'Welcome Admin!');
            }

            // Auth::logout();
            return back()->withErrors(['email' => 'You are not authorized as admin.']);
        }

        return back()->withErrors(['email' => 'Invalid credentials.'])->withInput();
    }

    // Logout function
    public function Adminlogout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Logged out successfully.');
    }

    public function userDetail($id)
    {
        $user = User::with(['wallet', 'deposits', 'withdrawals', 'investments', 'claimedAmounts'])->find($id);
        // $referral = User::where('referral_code', $user->referral_id)->first();
        $referrals = $this->getUserReferrals($user, 5);
        if (!$user) {
            return ResponseHelper::error('User not found', 404);
        }
        // $user->load(['wallet', 'deposits', 'withdrawals', 'investments']);
        return view('admin.pages.user-detail', compact('user', 'referrals'));
    }

    // Update user wallet
    public function updateWallet(Request $request, $userId)
    {
        $request->validate([
            'deposit_amount' => 'nullable|numeric|min:0',
            'profit_amount' => 'nullable|numeric|min:0',
            'referral_amount' => 'nullable|numeric|min:0',
            'bonus_amount' => 'nullable|numeric|min:0',
            'withdrawal_amount' => 'nullable|numeric|min:0',
            'locked_amount' => 'nullable|numeric|min:0',
            'reason' => 'nullable|string|max:255'
        ]);

        $user = User::findOrFail($userId);
        $wallet = $user->wallet;

        if (!$wallet) {
            $wallet = Wallet::create([
                'user_id' => $user->id,
                'status' => 'active'
            ]);
        }

        DB::beginTransaction();
        try {
            $oldValues = $wallet->toArray();
            $changes = [];

            // Update each field if provided
            $fields = ['deposit_amount', 'profit_amount', 'referral_amount', 'bonus_amount', 'withdrawal_amount', 'locked_amount'];
            
            foreach ($fields as $field) {
                if ($request->has($field) && $request->$field !== null) {
                    $oldValue = $wallet->$field ?? 0;
                    $newValue = $request->$field;
                    
                    if ($oldValue != $newValue) {
                        $wallet->$field = $newValue;
                        $changes[$field] = [
                            'old' => $oldValue,
                            'new' => $newValue
                        ];
                    }
                }
            }

            $wallet->save();

            // Log each change
            foreach ($changes as $field => $change) {
                \App\Models\AdminEdit::create([
                    'admin_id' => Auth::id(),
                    'user_id' => $userId,
                    'field_name' => $field,
                    'old_value' => $change['old'],
                    'new_value' => $change['new'],
                    'edit_type' => 'wallet_update',
                    'reason' => $request->reason ?? 'Admin updated wallet balance'
                ]);
            }

            DB::commit();
            return redirect()->back()->with('success', 'Wallet balances updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to update wallet: ' . $e->getMessage());
        }
    }
    public function getUserReferrals(User $user, $maxDepth = 5)
{
    $referrals = collect(); // Flat collection of all referrals
    $currentLevel = collect([$user]);

    for ($depth = 1; $depth <= $maxDepth; $depth++) {
        $nextLevel = collect();

        foreach ($currentLevel as $u) {
            $children = User::where('referral_code', $u->user_code)->get();

            // Optional: Tag the referral level
            $children->each(function ($child) use ($depth) {
                $child->referral_level = $depth;
            });

            $referrals = $referrals->merge($children);
            $nextLevel = $nextLevel->merge($children);
        }

        // Stop if no children found
        if ($nextLevel->isEmpty()) {
            break;
        }

        $currentLevel = $nextLevel;
    }

    return $referrals;
}

}
