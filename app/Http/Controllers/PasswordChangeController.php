<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Rules\StrongPassword;
use App\Rules\PasswordNotRecentlyUsed;

class PasswordChangeController extends Controller
{
    /**
     * Show the password change form
     */
    public function showChangeForm()
    {
        $user = Auth::user();
        
        if (!$user->mustChangePassword()) {
            return redirect()->route('user.dashboard');
        }
        
        return view('auth.password-change');
    }

    /**
     * Handle password change
     */
    public function changePassword(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->mustChangePassword()) {
            return redirect()->route('user.dashboard');
        }

        $request->validate([
            'current_password' => 'required',
            'new_password' => [
                'required',
                'string',
                'min:12',
                'confirmed',
                new StrongPassword(),
                new PasswordNotRecentlyUsed($user)
            ],
        ], [
            'current_password.required' => 'Current password is required.',
            'new_password.required' => 'New password is required.',
            'new_password.min' => 'New password must be at least 12 characters.',
            'new_password.confirmed' => 'Password confirmation does not match.',
        ]);

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        // Update password with security features
        $user->updatePassword($request->new_password);

        return redirect()->route('user.dashboard')
            ->with('success', 'Password changed successfully.');
    }
}