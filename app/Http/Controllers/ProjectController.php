<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\ProjectCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Js;

class ProjectController extends Controller
{
    /**
     * Display a listing of projects.
     * @return JsonResponse
     * $todo Consider pagination of results and selecting specific fields to return as data set grows
     */
    public function index(): JsonResponse
    {
        // Eager-load total tasks count and completed tasks count
        $projects = Project::with('tasks')
            ->withCount('tasks')
            ->withCount(['tasks as completed_tasks_count' => function ($query) {
                $query->where('status', 'completed');
            }])
            ->get();
        return new ProjectCollection($projects)->response()->setStatusCode(200);
    }

    /**
     * Store a newly created project
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $request->request->add(['user_id' => Auth::user()->id]);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'user_id' => 'required|exists:users,id',
        ]);

        $project = Project::create($validated);
        // Reload the model with counts and relations needed by the resource
        $project = Project::with('tasks')
            ->withCount('tasks')
            ->withCount(['tasks as completed_tasks_count' => function ($query) {
                $query->where('status', 'completed');
            }])
            ->find($project->id);

        return new ProjectResource($project)->response()->setStatusCode(201);
    }

    /**
     * Display the specified project
     * @param int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        $project = Project::with('tasks')
            ->withCount('tasks')
            ->withCount(['tasks as completed_tasks_count' => function ($query) {
                $query->where('status', 'completed');
            }])
            ->findOrFail($id);
        return new ProjectResource($project)->response()->setStatusCode(200);
    }
}
