<?php

namespace App\Models;

use App\Enum\ProjectStatusEnum;
use App\Enum\TaskStatusEnum;
use Cviebrock\EloquentSluggable\Sluggable;
use Cviebrock\EloquentSluggable\SluggableObserver;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static create(array $data)
 */
class Task extends Model
{
    use HasFactory, HasUuids, Sluggable;


    protected $guarded = ['id', 'slug'];

    /**
     * @var array
     * Setting Task status open as default
     */
    protected $attributes = [
        'status' => TaskStatusEnum::Open
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'difficulty',
        'assignee_id',
        'project_id',
        'priority',
        'status'
    ];

    protected $hidden = [
        'project_id',
        'assignee_id',
        'created_at',
        'updated_at'
    ];

    protected $with = ['assignee'];

    /**
     * @return BelongsTo
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id', 'id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
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
