<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\Admin;

class OfficeAdminProfileController extends Controller
{
    /**
     * Upload profile picture for a specific office admin (superadmin only).
     */
    public function uploadProfilePictureForAdmin(Request $request, Admin $officeAdmin)
    {
        // Ensure only superadmins can upload profile pictures for office admins
        if (!auth('admin')->user()->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg|max:5120', // 5MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Delete old profile picture if it exists
            if ($officeAdmin->profile_picture) {
                Storage::disk('public')->delete($officeAdmin->profile_picture);
            }

            // Generate unique filename
            $file = $request->file('profile_picture');
            
            // Store the file
            $path = $file->storeAs('office_admin_profile_pictures', $officeAdmin->id . '_' . time() . '.' . $file->getClientOriginalExtension(), 'public');
            
            // Update office admin record
            $officeAdmin->update(['profile_picture' => $path]);

            return response()->json([
                'success' => true,
                'message' => 'Profile picture uploaded successfully!',
                'profile_picture_url' => storage_asset($path)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error uploading profile picture: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload office admin profile picture.
     */
    /**
     * Upload office admin profile picture.
     */
    public function uploadProfilePicture(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg|max:5120', // 5MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $officeAdmin = auth('admin')->user();

            // Delete old profile picture if it exists
            if ($officeAdmin->profile_picture) {
                Storage::disk('public')->delete($officeAdmin->profile_picture);
            }

            // Generate unique filename
            $file = $request->file('profile_picture');
            $filename = 'office_admin_profile_pictures/' . $officeAdmin->id . '_' . time() . '.' . $file->getClientOriginalExtension();

            // Store the file
            $path = $file->storeAs('office_admin_profile_pictures', $officeAdmin->id . '_' . time() . '.' . $file->getClientOriginalExtension(), 'public');

            // Update office admin record
            $officeAdmin->update(['profile_picture' => $path]);

            return response()->json([
                'success' => true,
                'message' => 'Profile picture uploaded successfully!',
                'profile_picture_url' => storage_asset($path)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error uploading profile picture: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove profile picture for a specific office admin (superadmin only).
     */
    public function removeProfilePictureForAdmin(Admin $officeAdmin)
    {
        // Ensure only superadmins can remove profile pictures for office admins
        if (!auth('admin')->user()->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        try {
            if ($officeAdmin->profile_picture) {
                // Delete the file from storage
                Storage::disk('public')->delete($officeAdmin->profile_picture);
                
                // Update office admin record
                $officeAdmin->update(['profile_picture' => null]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Profile picture removed successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error removing profile picture: ' . $e->getMessage()
            ], 500);
        }
    }
}
