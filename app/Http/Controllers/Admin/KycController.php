<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KycDocument;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PDF;



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

        return view('admin.pages.kyc.index', compact('documents', 'totalDocuments', 'pendingDocuments', 'approvedDocuments', 'rejectedDocuments'));
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

        $document = KycDocument::with('user')->findOrFail($id);
        if (!$document) {
            Log::error("Document not found for ID: $id");
            abort(404, 'Document not found');
        }


        $path = storage_path('app/private/' . $document->file_path);
        return response()->download($path, $document->original_filename);
    }

    public function viewFile($id)
    {
        // dd($id);
        $document = KycDocument::findOrFail($id);
        $path = storage_path('app/private/' . $document->file_path);
        // dd($path);
        // if (!Storage::exists($path)) {
        //     abort(404);
        // }

        return response()->file($path);
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
