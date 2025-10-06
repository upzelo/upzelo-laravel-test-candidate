<?php

namespace App\Models;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
        'project_id',
        'assigned_to',
        'due_date',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'status' => TaskStatus::class,
            'priority' => TaskPriority::class,
        ];
    }

    /**
     * Get the project that owns the task.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user assigned to the task.
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Scope a query to only include high priority tasks by due date.
     * Returns tasks ordered by priority and due date for urgent planning.
     */
    public function scopeHighPriorityByDueDate($query)
    {
        return $query->where('priority', 'high')
            ->whereNotNull('due_date')
            ->orderBy('due_date', 'asc')
            ->orderBy('priority', 'desc');
    }

    /**
     * Get overdue tasks for dashboard alerts.
     * Critical for project management notifications.
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
            ->where('status', '!=', TaskStatus::Completed)
            ->orderBy('due_date');
    }

    /**
     * Calculate days until due date.
     * Used for priority calculations and notifications.
     */
    public function getDaysUntilDueAttribute()
    {
        if (!$this->due_date) {
            return null;
        }
        
        return now()->diffInDays($this->due_date, false);
    }
}
