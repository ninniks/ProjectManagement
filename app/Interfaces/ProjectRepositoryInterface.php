<?php

namespace App\Interfaces;


use App\Models\Project;

interface ProjectRepositoryInterface
{
    public function all(?bool $withClosed, ?bool $onlyClosed, ?string $sortBy, ?int $page, ?int $perPage);
    public function find(string $project_id);

    public function create(mixed $data);

    public function update(string $project_id, mixed $data);

    public function updateStatus(Project $project, string $status);


}
