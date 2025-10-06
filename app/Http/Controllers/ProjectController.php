<?php

namespace App\Http\Controllers;

use App\Http\Resources\ApiResponseResource;
use App\Http\Resources\ProjectCollection;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Services\ProjectService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProjectController extends Controller
{
    public function __construct()
    {
    }

    public function index(Request $request)
    {

        try {
            $projects = Project::withCount('tasks')->get();

            return new ProjectCollection($projects)->response()->setStatusCode(200);
        } catch (Exception $e) {
            return new ApiResponseResource([
                'data' => [],
                'status' => 'error',
                'message' => 'Something went wrong! Please try again',

            ])->response()->setStatusCode(404);
        }
    }

    public function store(Request $request, ProjectService $projectService)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string|max:200',
                'user_id' => 'required|int'
            ]);

            $project = $projectService->createProject($validated);

            return (new ProjectResource($project))
                ->additional([
                    'status' => 'success',
                    'message' => 'Project created successfully'
                ])
                ->response()
                ->setStatusCode(201);
        } catch (ValidationException $e) {
            return new ApiResponseResource([
                'data' => $e->errors(),
                'status' => 'Error',
                'message' => 'Validation failed'
            ])->response()->setStatusCode(422);
        } catch (Exception $e) {
            return new ApiResponseResource([
                'data' => [],
                'status' => 'Error',
                'message' => 'Something went wrong! try again',
            ])->response()->setStatusCode(500);
            ;
        }
    }

    public function show($id)
    {
        try {
            $project = Project::withCount('tasks')->findOrFail($id);

            return (new ProjectResource($project));
        } catch (ModelNotFoundException $e) {
            return new ApiResponseResource([
                'data' => [],
                'status' => 'Error',
                'message' => 'Project Not Found'
            ])->response()
                ->setStatusCode(404);
        } catch (Exception $e) {
            return new ApiResponseResource([
                'data' => [],
                'status' => 'Error',
                'message' => 'Something Went wrong! Please try again',
            ])->response()->setStatusCode(500);
        }
    }

    public function update(Request $request, ProjectService $projectService, $id)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string|max:200'
            ]);
            $project = $projectService->updateProject($validated, $id);

            return new ProjectResource($project)
                ->additional([
                    'data' => [],
                    'status' => 'success',
                    'message' => 'Project created successfully'
                ])
                ->response()
                ->setStatusCode(200);
        } catch (ModelNotFoundException $e) {
            return new ApiResponseResource([
                'data' => [],
                'status' => 'Error',
                'message' => 'Project Not Found'
            ])->response()
                ->setStatusCode(404);
        } catch (Exception $e) {
            return new ApiResponseResource([
                'data' => [],
                'status' => 'Error',
                'message' => 'Something went wrong.'
            ]);
        }
    }

    public function destroy($id, ProjectService $projectService)
    {

        try {
            $projectService->destroy($id);

            return response()->noContent();
        } catch (ModelNotFoundException $e) {
            return new ApiResponseResource([
                'status' => 'Error',
                'message' => 'Project not found'
            ]);
        } catch (Exception $e) {
            return new ApiResponseResource([
                'status' => 'Error',
                'message' => 'Something went wrong',
            ]);
        }
    }
}
