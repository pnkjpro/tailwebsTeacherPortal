<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Models\Student;
use App\Models\User; //teacher
use Auth;
class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Student::query();
        
        // Search by name
        if ($request->has('name') && !empty($request->input('name'))) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }

        // Filter by subject
        if ($request->has('subject') && !empty($request->input('subject'))) {
            $query->where('subject', $request->input('subject'));
        }

        $user_id = Auth::User()->id;
        $students = $query->where('user_id', $user_id)->get();
        return response()->json($students);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    public function portal(){
        $user_id = Auth::User()->id;
        $students = Student::where('user_id', $user_id)->get();   
        return view('teacher-portal', compact('students'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    $rules = [
        'name' => 'required|string|max:255',
        'subject' => 'required|string|max:255',
        'marks' => 'required|integer|min:0|max:100',
    ];

    $validator = Validator::make($request->all(), $rules);

    // Check for validation errors
    if ($validator->fails()) {
        return response()->json([
            'message' => 'Validation failed',
            'errors' => $validator->errors(),
        ], 422);
    }

    // Validation passed, proceed with updating or creating the student record
    $student = Student::where('name', $request->name)
                      ->where('subject', $request->subject)
                      ->first();

    if ($student) {
        $student->marks += $request->marks;
        $student->save();
    } else {
        $validatedData = $request->only(['name', 'subject', 'marks']);
        $validatedData['user_id'] = Auth::user()->id;
        $student = Student::create($validatedData);
    }
    return response()->json($student, 201);
}

public function getCurrentMarks(Request $request)
{
    $name = $request->input('name');
    $subject = $request->input('subject');

    $student = Student::where('name', $name)
                      ->where('subject', $subject)
                      ->first();

    if ($student) {
        return response()->json(['marks' => $student->marks]);
    }

    return response()->json(['marks' => 0]);
}


    /**
     * Display the specified resource.
     */
    public function getSubjects()
    {
        $user_id = Auth::User()->id;
        $subjects = Student::select('subject')->where('user_id', $user_id)->distinct()->get()->pluck('subject');
        return response()->json($subjects);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'sometimes|string|max:225',
            'subject' => 'sometimes|string|max:225',
            'marks' => 'sometimes|integer|between:0,100',
        ]);

        $student = Student::findOrFail($id);
        $student->update($request->only('name','subject','marks'));

        return response()->json(['message' => 'Student updated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Student::findOrFail($id)->delete();
        return response()->json(['message' => 'Student deleted successfully.']);
    }
}
