<?php

namespace App\Http\Controllers;

use App\Exceptions\ProjectNotFoundException;
use App\Http\Rules\FilterRequest;
use App\Http\Rules\PatchProjectFormRequest;
use App\Http\Rules\StoreProjectFormRequest;
use App\Http\Traits\QueryUtilityTrait;
use App\Interfaces\ProjectRepositoryInterface;
use App\Repositories\ProjectRepository;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;


class ProjectController extends Controller
{
    use QueryUtilityTrait;

    private ProjectRepositoryInterface $projectRepository;

    public function __construct()
    {
        $this->projectRepository = new ProjectRepository();
    }

    /**
     * @param FilterRequest $request
     * @return JsonResponse
     *
     * Returns all Projects with pagination and filters on status
     */
    public function index(FilterRequest $request): JsonResponse
    {
        $sortBy = $request->validated('sortBy');
        $withClosed = $request->validated('withClosed');
        $onlyClosed = $request->validated('onlyClosed');
        $page = $request->validated('page', 1);
        $perPage = $request->validated('perPage', 5);

        $projects = $this->projectRepository->all($withClosed, $onlyClosed, $sortBy, $page, $perPage);

        return response()->json($projects);
    }

    /**
     * @param $project_id
     * @return JsonResponse
     *
     * Returns Project by an id if exists
     */
    public function show($project_id): JsonResponse
    {
        try {
            $project = $this->projectRepository->find($project_id);
        }catch(ProjectNotFoundException $e){
            return response()->json(["data" => ["error" => $e->getMessage()]], $e->getCode());
        }

        return response()->json(["data" => $project]);
    }

    /**
     * @param StoreProjectFormRequest $request
     * @return JsonResponse
     *
     * Creates a new Project resource
     */
    public function store(StoreProjectFormRequest $request): JsonResponse
    {
        $project = $this->projectRepository->create($request->validated());

        return response()->json(["data" => $project], ResponseAlias::HTTP_CREATED);
    }

    /**
     * @param PatchProjectFormRequest $request
     * @param $project_id
     * @return JsonResponse
     *
     * Updates a Project
     */
    public function update(PatchProjectFormRequest $request, $project_id): JsonResponse
    {
        try {
            $project = $this->projectRepository->find($project_id);
        }catch (ProjectNotFoundException $e){
            return response()->json(['data' => ["error" => $e->getMessage()]], $e->getCode());
        }

       $this->projectRepository->update($project_id, $request->validated());

        return response()->json(["data" => $project->fresh()]);
    }

    /**
     * @param $project_id
     * @param $status
     * @return JsonResponse
     *
     * Updates only Project status
     */
    public function updateStatus($project_id, $status): JsonResponse
    {

        try {
            $project = $this->projectRepository->find($project_id);
        }catch (ProjectNotFoundException $e){
            return response()->json(['data' => ["error" => $e->getMessage()]], $e->getCode());
        }

        if(!$this->projectRepository->updateStatus($project, $status))
        {
            return response()->json([], 400);
        }

       return response()->json([], 204);
    }
}
