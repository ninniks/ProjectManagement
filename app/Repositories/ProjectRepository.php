<?php

namespace App\Repositories;

use App\Enum\ProjectStatusEnum;
use App\Exceptions\ProjectNotFoundException;
use App\Http\Traits\QueryUtilityTrait;
use App\Interfaces\ProjectRepositoryInterface;
use App\Models\Project;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpFoundation\Response;

class ProjectRepository implements ProjectRepositoryInterface
{
    use QueryUtilityTrait;

    public function all($withClosed, $onlyClosed, $sortBy, $page, $perPage): LengthAwarePaginator
    {

        $projects = Project::query()
            ->when($onlyClosed, function ($query){
                $query->where('status', ProjectStatusEnum::Closed->value);
            })->when(!$withClosed && !$onlyClosed, function ($query){
                $query->where('status', ProjectStatusEnum::Open->value);
            });

        return $this->sort($projects, $sortBy)->paginate(
                $perPage,
                ['*'],
                'page',
                $page);
    }

    public function find($project_id): Project
    {
        try {
            $project = Project::findOrFail($project_id);
        }catch (ModelNotFoundException){
            throw new ProjectNotFoundException("No project found with id $project_id", Response::HTTP_NOT_FOUND);
        }

        return $project;
    }

    public function create($data): Project
    {
        return Project::create($data);
    }

    public function update($project_id, $data)
    {
        $project = Project::where('id', $project_id)->first();
        $project->slug = null;

        return $project->update($data);
    }

    public function updateStatus(Project $project, string $status): bool
    {
        if($status === ProjectStatusEnum::Closed->value){
            if($project->hasAllTasksCompleted()){
                $project->update(['status' => $status]);
                return true;
            }else{
                return false;
            }
        }

        if($status === ProjectStatusEnum::Open->value &&
            $project->status === ProjectStatusEnum::Closed->value){
            return false;
        }

        return true;
    }
}
