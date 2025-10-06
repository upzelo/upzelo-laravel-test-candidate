<?php

namespace App\Services;

use App\Models\Project;

class ProjectService
{
    public function createProject(array $data)
    {

        $project = Project::create([
            'name' => $data['name'],
            'description' => $data['description'],
            'user_id' => $data['user_id']
        ]);

        return $project;
    }

    public function updateProject(array $data, $id)
    {

        $project = Project::findOrFail($id);

        $project->update($data);

        return $project->fresh();
    }

    public function destroy($id)
    {

        $project = Project::findOrFail($id);
        $project->delete();

        return true;
    }
}
