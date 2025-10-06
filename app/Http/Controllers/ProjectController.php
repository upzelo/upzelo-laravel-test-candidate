<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ProjectRequest;
use App\Http\Resources\ProjectCollection;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProjectController extends Controller
{
    public function index(): JsonResponse
    {
        $projects = Project::with('tasks')->get();
        return new ProjectCollection($projects)->response()->setStatusCode(Response::HTTP_OK);
    }

    public function store(ProjectRequest $request): JsonResponse
    {
        $data = $request->validated();
        $project = Project::create($data);

        return new ProjectResource($project)->response()->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(int $id): JsonResponse
    {
        $project = Project::with('tasks')->findOrFail($id);

        return new ProjectResource($project)->response()->setStatusCode(Response::HTTP_OK);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $project = Project::findOrFail($id);
        $project->update($request->all());

        return new ProjectResource($project)->response()->setStatusCode(Response::HTTP_OK);
    }

    public function destroy(int $id): JsonResponse
    {
        $project = Project::findOrFail($id);
        $project->delete();
        return response()->json([], Response::HTTP_NO_CONTENT);
    }
}
