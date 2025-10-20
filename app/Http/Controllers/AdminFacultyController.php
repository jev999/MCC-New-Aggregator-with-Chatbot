<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminFacultyController extends Controller
{
    /**
     * Display a listing of faculty members.
     */
    public function index()
    {
        $faculty = User::where('role', 'faculty')->latest()->get();

        // Always use superadmin views since admin views don't exist
        return view('superadmin.faculty.index', compact('faculty'));
    }

    /**
     * Show the form for editing the specified faculty member.
     */
    public function edit(User $faculty)
    {
        // Ensure we're only editing faculty members
        if ($faculty->getAttribute('role') !== 'faculty') {
            abort(404);
        }

        // Always use superadmin views since admin views don't exist
        return view('superadmin.faculty.edit', compact('faculty'));
    }

    /**
     * Update the specified faculty member in storage.
     */
    public function update(Request $request, User $faculty)
    {
        // Ensure we're only updating faculty members
        if ($faculty->getAttribute('role') !== 'faculty') {
            abort(404);
        }

        $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'surname' => 'required|string|max:255',
            'ms365_account' => 'required|email|unique:users,ms365_account,' . $faculty->getAttribute('id'),
        ]);

        $faculty->update([
            'first_name' => $request->input('first_name'),
            'middle_name' => $request->input('middle_name'),
            'surname' => $request->input('surname'),
            'ms365_account' => $request->input('ms365_account'),
        ]);

        // Always redirect to superadmin since admin routes don't exist
        return redirect()->route('superadmin.faculty.index')->with('success', 'Faculty member updated successfully!');
    }

    /**
     * Show the form for creating a new faculty member.
     */
    public function create()
    {
        // Always use superadmin views since admin views don't exist
        return view('superadmin.faculty.create');
    }

    /**
     * Store a newly created faculty member in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'surname' => 'required|string|max:255',
            'ms365_account' => 'required|email|unique:users,ms365_account',
            'password' => 'required|string|min:6',
        ]);

        User::create([
            'first_name' => $request->input('first_name'),
            'middle_name' => $request->input('middle_name'),
            'surname' => $request->input('surname'),
            'ms365_account' => $request->input('ms365_account'),
            'password' => Hash::make($request->input('password')),
            'role' => 'faculty',
        ]);

        // Always redirect to superadmin since admin routes don't exist
        return redirect()->route('superadmin.faculty.index')->with('success', 'Faculty member created successfully!');
    }

    /**
     * Display the specified faculty member.
     */
    public function show(User $faculty)
    {
        // Ensure we're only showing faculty members
        if ($faculty->getAttribute('role') !== 'faculty') {
            abort(404);
        }

        // Always use superadmin views since admin views don't exist
        return view('superadmin.faculty.show', compact('faculty'));
    }

    /**
     * Remove the specified faculty member from storage.
     */
    public function destroy(User $faculty)
    {
        // Ensure we're only deleting faculty members
        if ($faculty->getAttribute('role') !== 'faculty') {
            abort(404);
        }

        $faculty->delete();

        // Always redirect to superadmin since admin routes don't exist
        return redirect()->route('superadmin.faculty.index')->with('success', 'Faculty member deleted successfully!');
    }
}
