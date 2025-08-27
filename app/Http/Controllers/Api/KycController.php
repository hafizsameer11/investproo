<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\KycDocument;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class KycController extends Controller
{
    /**
     * Upload KYC document
     */
    public function upload(Request $request)
    {
        try {
            
            $validator = Validator::make($request->all(), [
                'document_type' => 'required|in:passport,national_id,drivers_license,utility_bill,bank_statement',
                'document' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240' // 10MB max
            ]);

            if ($validator->fails()) {
                \Log::error('KYC validation failed', ['errors' => $validator->errors()]);
                return ResponseHelper::error($validator->errors()->first(), 422);
            }

            // $user = Auth::user();
            
            // // Check if user already has a pending document of this type
            // $existingDocument = KycDocument::where('user_id', $user->id)
            //     ->where('document_type', $request->document_type)
            //     ->where('status', 'pending')
            //     ->first();

            // if ($existingDocument) {
            //     return ResponseHelper::error('You already have a pending document of this type', 422);
            // }

            $file = $request->file('document');
            $originalFilename = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $filename = 'kyc_ _' . $request->document_type . '_' . time() . '.' . $extension;
            
            // Store file in storage/app/kyc directory
            $path = $file->storeAs('kyc', $filename, 'local');

            $kycDocument = KycDocument::create([
                'user_id' =>7,
                'document_type' => $request->document_type,
                'file_path' => $path,
                'original_filename' => $originalFilename,
                'status' => 'pending'
            ]);

            return ResponseHelper::success($kycDocument, 'KYC document uploaded successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to upload KYC document: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get user's KYC documents
     */
    public function userDocuments()
    {
        try {
            $user = Auth::user();
            $documents = KycDocument::getUserDocuments($user->id);

            return ResponseHelper::success($documents, 'KYC documents retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to retrieve KYC documents: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get KYC document file
     */
    public function download($id)
    {
        try {
            $user = Auth::user();
            $document = KycDocument::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$document) {
                return ResponseHelper::error('Document not found', 404);
            }

            if (!Storage::disk('local')->exists($document->file_path)) {
                return ResponseHelper::error('File not found', 404);
            }

            $file = Storage::disk('local')->get($document->file_path);
            $mimeType = Storage::disk('local')->mimeType($document->file_path);

            return response($file, 200)
                ->header('Content-Type', $mimeType)
                ->header('Content-Disposition', 'inline; filename="' . $document->original_filename . '"');
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to download document: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Admin: Get all pending KYC documents
     */
    public function adminPendingDocuments()
    {
        try {
            $documents = KycDocument::getPendingDocuments();

            return ResponseHelper::success($documents, 'Pending KYC documents retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to retrieve pending documents: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Admin: Get all KYC documents
     */
    public function adminAllDocuments()
    {
        try {
            $documents = KycDocument::with(['user', 'reviewedBy'])
                ->orderBy('created_at', 'desc')
                ->get();

            return ResponseHelper::success($documents, 'All KYC documents retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to retrieve documents: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Admin: Review KYC document
     */
    public function review(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:approved,rejected',
                'admin_notes' => 'nullable|string|max:1000'
            ]);

            if ($validator->fails()) {
                return ResponseHelper::error($validator->errors()->first(), 422);
            }

            $document = KycDocument::find($id);
            if (!$document) {
                return ResponseHelper::error('Document not found', 404);
            }

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

            return ResponseHelper::success($document, 'KYC document reviewed successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to review document: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Admin: Download KYC document
     */
    public function adminDownload($id)
    {
        try {
            $document = KycDocument::find($id);
            if (!$document) {
                return ResponseHelper::error('Document not found', 404);
            }

            if (!Storage::disk('local')->exists($document->file_path)) {
                return ResponseHelper::error('File not found', 404);
            }

            $file = Storage::disk('local')->get($document->file_path);
            $mimeType = Storage::disk('local')->mimeType($document->file_path);

            return response($file, 200)
                ->header('Content-Type', $mimeType)
                ->header('Content-Disposition', 'inline; filename="' . $document->original_filename . '"');
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to download document: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete KYC document (Admin only)
     */
    public function destroy($id)
    {
        try {
            $document = KycDocument::find($id);
            if (!$document) {
                return ResponseHelper::error('Document not found', 404);
            }

            // Delete file from storage
            if (Storage::disk('local')->exists($document->file_path)) {
                Storage::disk('local')->delete($document->file_path);
            }

            $document->delete();

            return ResponseHelper::success(null, 'KYC document deleted successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to delete document: ' . $e->getMessage(), 500);
        }
    }
}
