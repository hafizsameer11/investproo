<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KycDocument;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class KycController extends Controller
{
    public function index()
    {
        $documents = KycDocument::with(['user', 'reviewedBy'])
            ->orderBy('created_at', 'desc')
            ->get();

        $totalDocuments = KycDocument::count();
        $pendingDocuments = KycDocument::where('status', 'pending')->count();
        $approvedDocuments = KycDocument::where('status', 'approved')->count();
        $rejectedDocuments = KycDocument::where('status', 'rejected')->count();

        return view('admin.pages.kyc', compact('documents', 'totalDocuments', 'pendingDocuments', 'approvedDocuments', 'rejectedDocuments'));
    }

    public function pending()
    {
        $documents = KycDocument::with(['user', 'reviewedBy'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.pages.kyc-pending', compact('documents'));
    }

    public function show($id)
    {
        $document = KycDocument::with(['user', 'reviewedBy'])->findOrFail($id);
        return view('admin.pages.kyc-show', compact('document'));
    }

    public function review(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'admin_notes' => 'nullable|string|max:1000'
        ]);

        $document = KycDocument::findOrFail($id);
        $document->update([
            'status' => $request->status,
            'admin_notes' => $request->admin_notes,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now()
        ]);

        // If approved, update user status to active
        if ($request->status === 'approved') {
            $user = User::find($document->user_id);
            if ($user) {
                $user->update(['status' => 'active']);
            }
        }

        return redirect()->route('kyc.index')->with('success', 'KYC document reviewed successfully');
    }

    public function download($id)
    {
        $document = KycDocument::findOrFail($id);

        if (!Storage::disk('local')->exists($document->file_path)) {
            return back()->with('error', 'File not found');
        }

        $file = Storage::disk('local')->get($document->file_path);
        $mimeType = Storage::disk('local')->mimeType($document->file_path);

        return response($file, 200)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', 'inline; filename="' . $document->original_filename . '"');
    }

    public function destroy($id)
    {
        $document = KycDocument::findOrFail($id);

        // Delete file from storage
        if (Storage::disk('local')->exists($document->file_path)) {
            Storage::disk('local')->delete($document->file_path);
        }

        $document->delete();

        return redirect()->route('kyc.index')->with('success', 'KYC document deleted successfully');
    }
}
