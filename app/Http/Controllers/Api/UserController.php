<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\Referrals;
use App\Models\User;
use App\Models\Wallet;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UserController extends Controller
{
    // register
    public function register(UserRequest $request)
    {
        try {
            $data = $request->validated();
            $data['user_code'] = Str::lower($data['name']) . rand(100, 999);


            $user = User::create($data);
            $this->createWallet($user);
            // $referral = $user['referral_code'];
            Referrals::create([
                'referral_code' => $user->user_code,
                'user_id' => $user->id
            ]);
            if (!empty($user['referral_code'])) {
                $referralRecord = Referrals::where('referral_code', $user['referral_code'])->first();


                if ($referralRecord) {
                    $bonusAmount = $referralRecord->bonus_amount ?? 0;
                    $perUserBonus = 0;

                    // Get current total referrals
                    $total = $referralRecord->total_referrals ;
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



                    // Step 4: Update values and save
                    if ($referralRecord) {
                        Referrals::where('id', $referralRecord->id)->update([
                            'total_referrals' => $total,
                            'per_user_referral' => $perUserBonus,
                            'referral_bonus_amount' => $bonusAmount
                        ]);
                    }
                }
                 if (!empty($user['referral_code'])) {
            // Multi-level referral bonus chain
            $this->processReferralChain($user, $user['referral_code']);
        }
            }

            return ResponseHelper::success($user, 'User is created successsfully');
        } catch (Exception $ex) {
            return ResponseHelper::error('User is not create' . $ex);
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
            $user = $request->validate([
                'email' => 'required|email',
                'password' => 'required'
            ]);
            // Log::info('Login attempt', ['email' => $user['email']]);

            if (Auth::attempt($user)) {
                $authUser = Auth::user();
                $token = $authUser->createToken("API Token")->plainTextToken;

                return response()->json([
                    'status' => true,
                    'message' => 'Login Successfully',
                    'token_type' => 'bearer',
                    'token' => $token,
                    'user' => $authUser,
                ], 200);
            } else {
                return ResponseHelper::error('Invalid credentials', 401);
            }
        } catch (Exception $ex) {
            return ResponseHelper::error('User is not Login' . $ex);
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
            $user = User::where('id', Auth::id())->get();
            return ResponseHelper::success($user, "Your profile");
        } catch (Exception $ex) {
            return ResponseHelper::error('Not fetch the single user datas' . $ex);
        }
    }
    // update
    public function update(UserRequest $request)
    {
        try {
            $data = $request->validated();
            // dd($data);
            $user = User::find(Auth::id());
            if (!$user) {
                throw new Exception("User not found");
            }
            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }
            $update_user = User::where('id', Auth::id())->update($data);
            return ResponseHelper::success($update_user, 'User profile updated successfully');
        } catch (Exception $ex) {
            return ResponseHelper::error('User is not update profile' . $ex);
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

        return view('admin.pages.users', compact('all_users', 'total_users', 'active_users','inactive_user'));
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
}
