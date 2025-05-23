<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Employer;
use Illuminate\Http\Request;
use App\Models\Job;

class JobController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(
            Job::with(['employer.user'])
                ->where('status', 'approved')
                ->paginate(20)
        );        
    }

    public function alljobs()
    {
        return response()->json(Job::with(['employer.user'])->paginate(20));        
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
        $validated = $request->validate([
            'employer_id' => 'required|exists:users,id',
            'category_id' => 'required|exists:categories,id',
            'type' => 'required|in:full-time,part-time,contract,internship,temporary,freelance',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'responsibilities' => 'required|string',
            'qualifications' => 'required|string',
            'salary_range' => 'nullable|string|max:255',
            'benefits' => 'nullable|string',
            'location' => 'required|string|max:255',
            'application_deadline' => 'required|date',
            'status' => 'required|in:pending,approved,rejected,archived',
            'approved_at' => 'nullable|date',
            'approved_by' => 'nullable|exists:users,id',
        ]);

        $employer = Employer::where('user_id', $validated['employer_id'])->with('user')->firstOrFail();

        $validated['employer_id'] = $employer->id;

        $job = Job::create($validated);

        return response()->json($job, 201);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $job = Job::with('employer')->find($id);
        return $job ? response()->json($job) : response()->json(['message' => 'Not Found'], 404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $job = Job::findOrFail($id);

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'type' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'responsibilities' => 'nullable|string',
            'qualifications' => 'nullable|string',
            'salary_range' => 'nullable|string|max:255',
            'benefits' => 'nullable|string',
            'location' => 'required|string|max:255',
            'application_deadline' => 'required|date',
        ]);
    
        $job->update($validated);
    
        return response()->json([
            'message' => 'Job updated successfully',
            'job' => $job
        ]);
    
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $job = Job::find($id);
        if (!$job) return response()->json(['message' => 'Not Found'], 404);

        $job->delete();
        return response()->json(['message' => 'Deleted']);
    }

    public function findEmployerJob($id){
        $employer = Employer::where('user_id', $id)->with('user')->firstOrFail();
        $job = Job::where('employer_id', $employer->id)->get();
        if (!$job) return response()->json(['message'=> 'There is no Job yet']);
        return response()->json($job);
    }

    public function approveJob($id){
        $job = Job::find($id);
        if (!$job) return response()->json(['message' => 'Not Found'], 404);
        $job->status = 'approved';
        $job->approved_at = now();
        $job->save();
        return response()->json($job);
    }
    public function rejectJob($id){
        $job = Job::find($id);
        if (!$job) return response()->json(['message' => 'Not Found'], 404);
        $job->status = 'rejected';
        $job->save();
        return response()->json($job);
    }
}