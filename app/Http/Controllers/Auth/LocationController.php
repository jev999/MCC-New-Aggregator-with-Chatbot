<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function show()
    {
        // If session already has location, redirect to login
        if (session()->has('user_location')) {
            return redirect()->route('login');
        }
        return view('auth.location-permission');
    }

    public function save(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'allow' => 'nullable|string'
        ]);

        session([
            'user_location' => [
                'lat' => $request->input('latitude'),
                'lng' => $request->input('longitude'),
                // address fields left empty (no API), user can add later if desired
                'street' => null,
                'barangay' => null,
                'municipality' => null,
                'province' => null,
            ]
        ]);

        return redirect()->route('login');
    }
}
