<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminStudentController extends Controller
{
    public function index()
    {
        $students = User::where('role', 'student')->latest()->get();

        // Always use superadmin views since admin views don't exist
        return view('superadmin.students.index', compact('students'));
    }

    public function edit(User $student)
    {
        // Ensure we're only editing students
        if ($student->role !== 'student') {
            abort(404);
        }

        // Always use superadmin views since admin views don't exist
        return view('superadmin.students.edit', compact('student'));
    }

    public function update(Request $request, User $student)
    {
        // Ensure we're only updating students
        if ($student->role !== 'student') {
            abort(404);
        }

        $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'surname' => 'required|string|max:255',
            'ms365_account' => 'required|email|unique:users,ms365_account,' . $student->id,
            'department' => 'required|in:Bachelor of Science in Information Technology,Bachelor of Science in Business Administration,Bachelor of Elementary Education,Bachelor of Science in Hospitality Management',
            'year_level' => 'required|in:1st Year,2nd Year,3rd Year,4th Year',
        ]);

        $student->update([
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'surname' => $request->surname,
            'ms365_account' => $request->ms365_account,
            'department' => $request->department,
            'year_level' => $request->year_level,
        ]);

        // Always redirect to superadmin since admin routes don't exist
        return redirect()->route('superadmin.students.index')->with('success', 'Student updated successfully!');
    }

    public function create()
    {
        // Always use superadmin views since admin views don't exist
        return view('superadmin.students.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'surname' => 'required|string|max:255',
            'ms365_account' => 'required|email|unique:users,ms365_account',
            'password' => 'required|string|min:6',
            'department' => 'required|in:Bachelor of Science in Information Technology,Bachelor of Science in Business Administration,Bachelor of Elementary Education,Bachelor of Secondary Education,Bachelor of Science in Hospitality Management',
            'year_level' => 'required|in:1st Year,2nd Year,3rd Year,4th Year',
        ]);

        User::create([
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'surname' => $request->surname,
            'ms365_account' => $request->ms365_account,
            'password' => Hash::make($request->password),
            'role' => 'student',
            'department' => $request->department,
            'year_level' => $request->year_level,
        ]);

        // Always redirect to superadmin since admin routes don't exist
        return redirect()->route('superadmin.students.index')->with('success', 'Student created successfully!');
    }

    public function show(User $student)
    {
        // Ensure we're only showing students
        if ($student->role !== 'student') {
            abort(404);
        }

        // Always use superadmin views since admin views don't exist
        return view('superadmin.students.show', compact('student'));
    }

    public function destroy(User $student)
    {
        // Ensure we're only deleting students
        if ($student->role !== 'student') {
            abort(404);
        }

        $student->delete();

        // Always redirect to superadmin since admin routes don't exist
        return redirect()->route('superadmin.students.index')->with('success', 'Student deleted successfully!');
    }

    /**
     * Bulk delete students
     */
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'required|integer|exists:users,id'
        ]);

        try {
            $studentIds = $request->student_ids;
            $count = count($studentIds);
            
            // Get student details for audit before deletion
            $students = User::whereIn('id', $studentIds)
                ->where('role', 'student')
                ->get();
            
            if ($students->count() !== $count) {
                return response()->json([
                    'success' => false,
                    'message' => 'Some of the selected records are not students.'
                ], 422);
            }
            
            // Log the bulk deletion for audit purposes
            \Log::info('Bulk students deleted', [
                'deleted_by' => auth('admin')->user() ? auth('admin')->user()->username : 'Unknown',
                'deleted_count' => $count,
                'deleted_student_ids' => $studentIds,
                'deleted_students_details' => $students->map(function($student) {
                    return [
                        'id' => $student->id,
                        'name' => $student->first_name . ' ' . $student->surname,
                        'email' => $student->ms365_account,
                        'department' => $student->department,
                    ];
                })->toArray(),
                'timestamp' => now()->toISOString()
            ]);
            
            // Delete the students
            User::whereIn('id', $studentIds)->where('role', 'student')->delete();
            
            return response()->json([
                'success' => true,
                'message' => "{$count} student(s) deleted successfully."
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid student IDs provided.'
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error bulk deleting students', [
                'error' => $e->getMessage(),
                'student_ids' => $request->student_ids ?? [],
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete students. Please try again.'
            ], 500);
        }
    }
}
