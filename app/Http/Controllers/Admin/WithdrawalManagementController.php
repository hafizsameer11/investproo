<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use App\Models\Wallet;
use App\Services\WalletOps;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WithdrawalManagementController extends Controller
{
    /**
     * Display withdrawal management dashboard
     */
    public function index()
    {
        $withdrawals = Withdrawal::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'total' => Withdrawal::count(),
            'pending' => Withdrawal::where('status', 'pending')->count(),
            'approved' => Withdrawal::where('status', 'active')->count(),
            'rejected' => Withdrawal::where('status', 'rejected')->count(),
            'total_amount' => Withdrawal::sum('amount'),
            'pending_amount' => Withdrawal::where('status', 'pending')->sum('amount'),
        ];

        return view('admin.pages.withdrawal-management', compact('withdrawals', 'stats'));
    }

    /**
     * Show detailed withdrawal information
     */
    public function show($id)
    {
        $withdrawal = Withdrawal::with(['user', 'user.wallet'])->findOrFail($id);
        
        return view('admin.pages.withdrawal-detail', compact('withdrawal'));
    }

    /**
     * Approve withdrawal
     */
    public function approve(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            
            $withdrawal = Withdrawal::findOrFail($id);
            
            if ($withdrawal->status !== 'pending') {
                return redirect()->back()->with('error', 'Withdrawal is not pending');
            }

            $withdrawal->update([
                'status' => 'active',
                'withdrawal_date' => now(),
            ]);

            // Update related transactions
            Transaction::where('withdrawal_id', $id)->update([
                'status' => 'completed',
            ]);

            DB::commit();
            
            return redirect()->back()->with('success', 'Withdrawal approved successfully');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to approve withdrawal: ' . $e->getMessage());
        }
    }

    /**
     * Reject withdrawal with reason
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();
            
            $withdrawal = Withdrawal::findOrFail($id);
            
            if ($withdrawal->status !== 'pending') {
                return redirect()->back()->with('error', 'Withdrawal is not pending');
            }

            $withdrawal->update([
                'status' => 'rejected',
                'rejection_reason' => $request->rejection_reason,
            ]);

            // Refund the deducted amount back to wallet
            $wallet = Wallet::where('user_id', $withdrawal->user_id)->first();
            if ($wallet) {
                WalletOps::refundToBucket($wallet, $withdrawal->amount, 'deposit_amount');
            }

            // Update related transactions
            Transaction::where('withdrawal_id', $id)->update([
                'status' => 'failed',
            ]);

            DB::commit();
            
            return redirect()->back()->with('success', 'Withdrawal rejected and amount refunded');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to reject withdrawal: ' . $e->getMessage());
        }
    }

    /**
     * Get withdrawal statistics for dashboard
     */
    public function getStats()
    {
        $stats = [
            'total_withdrawals' => Withdrawal::count(),
            'pending_withdrawals' => Withdrawal::where('status', 'pending')->count(),
            'approved_withdrawals' => Withdrawal::where('status', 'active')->count(),
            'rejected_withdrawals' => Withdrawal::where('status', 'rejected')->count(),
            'total_amount' => Withdrawal::sum('amount'),
            'pending_amount' => Withdrawal::where('status', 'pending')->sum('amount'),
            'approved_amount' => Withdrawal::where('status', 'active')->sum('amount'),
            'rejected_amount' => Withdrawal::where('status', 'rejected')->sum('amount'),
        ];

        return response()->json($stats);
    }

    /**
     * Export withdrawals to CSV
     */
    public function export(Request $request)
    {
        $withdrawals = Withdrawal::with('user')
            ->when($request->status, function($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->date_from, function($query, $date) {
                return $query->whereDate('created_at', '>=', $date);
            })
            ->when($request->date_to, function($query, $date) {
                return $query->whereDate('created_at', '<=', $date);
            })
            ->get();

        $filename = 'withdrawals_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($withdrawals) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'ID', 'User Name', 'User Email', 'Amount', 'Status', 
                'Crypto Type', 'Wallet Address', 'Request Date', 
                'Processed Date', 'Notes', 'Rejection Reason'
            ]);

            // CSV data
            foreach ($withdrawals as $withdrawal) {
                fputcsv($file, [
                    $withdrawal->id,
                    $withdrawal->user->name ?? 'N/A',
                    $withdrawal->user->email ?? 'N/A',
                    $withdrawal->amount,
                    $withdrawal->status,
                    $withdrawal->crypto_type ?? 'N/A',
                    $withdrawal->wallet_address ?? 'N/A',
                    $withdrawal->created_at->format('Y-m-d H:i:s'),
                    $withdrawal->withdrawal_date ?? 'N/A',
                    $withdrawal->notes ?? 'N/A',
                    $withdrawal->rejection_reason ?? 'N/A'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
