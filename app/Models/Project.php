<?php

namespace App\Models;

use App\Enum\ProjectStatusEnum;
use App\Enum\TaskStatusEnum;
use Cviebrock\EloquentSluggable\Sluggable;
use Cviebrock\EloquentSluggable\SluggableObserver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static create(array $all)
 * @method static where(string $string, $project_id)
 * @method static findOrFail($project_id)
 * @method task($task_id)
 * @property mixed $status
 * @property mixed $id
 */
class Project extends Model
{
    use HasFactory, HasUuids ,Sluggable;


    protected $guarded = ['id', 'slug'];
    /**
     * @var array
     */
    protected $attributes = [
        'status' => ProjectStatusEnum::Open
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'status'
    ];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    protected $appends = ['tasks_count', 'completed_tasks_count'];

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'project_id', 'id');
    }

    /**
     * @return int
     */
    public function getTasksCountAttribute(): int
    {
        return $this->tasks()
            ->where('tasks.status', TaskStatusEnum::Open->value)
            ->count();
    }

    /**
     * @return int
     */
    public function getCompletedTasksCountAttribute(): int
    {
        return $this->tasks()
            ->where('tasks.status', TaskStatusEnum::Closed->value)
            ->count();
    }

    public function hasAllTasksCompleted(): bool
    {
        return $this->tasks()
            ->where('tasks.status', TaskStatusEnum::Open->value)
            ->orWhere('tasks.status', TaskStatusEnum::Blocked->value)
            ->count() == 0;
    }

    public function sluggableEvent(): string
    {
        return SluggableObserver::SAVED;
    }


    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => ['id', 'title']
            ]
        ];
    }
}
