<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'user_id',
    ];

    /**
     * Get the user that owns the project.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the tasks for the project.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Calculate the completion percentage of the project.
     */
    public function getCompletionPercentageAttribute(): float
    {
        // If we have tasks_count attribute, use it
        if (array_key_exists('tasks_count', $this->attributes)) {
            $totalTasks = (int) $this->attributes['tasks_count'];
        } else {
            // Fallback to querying the database
            $totalTasks = $this->tasks()->count();
        }

        if ($totalTasks === 0) {
            return 0;
        }

        // If we already have completed_tasks_count, use it
        if (array_key_exists('completed_tasks_count', $this->attributes)) {
            $completedTasks = (int) $this->attributes['completed_tasks_count'];
        } else {
            $completedTasks = $this->tasks()->where('status', 'completed')->count();
        }

        return round(($completedTasks / $totalTasks) * 100, 2);
    }
}
