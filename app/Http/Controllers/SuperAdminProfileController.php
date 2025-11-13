<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\Admin;

class SuperAdminProfileController extends Controller
{
    /**
     * Upload admin profile picture.
     */
    public function uploadProfilePicture(Request $request, Admin $admin)
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
            // Delete old profile picture if it exists
            if ($admin->profile_picture) {
                Storage::disk('public')->delete($admin->profile_picture);
            }

            // Generate unique filename
            $file = $request->file('profile_picture');
            $filename = 'admin_profile_pictures/' . $admin->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            
            // Store the file
            $path = $file->storeAs('admin_profile_pictures', $admin->id . '_' . time() . '.' . $file->getClientOriginalExtension(), 'public');
            
            // Update admin record
            $admin->update(['profile_picture' => $path]);

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
     * Remove admin profile picture.
     */
    public function removeProfilePicture(Admin $admin)
    {
        try {
            if ($admin->profile_picture) {
                // Delete the file from storage
                Storage::disk('public')->delete($admin->profile_picture);
                
                // Update admin record
                $admin->update(['profile_picture' => null]);
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
