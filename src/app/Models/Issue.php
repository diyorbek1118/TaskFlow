<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Issue extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'created_by',
        'assigned_to',
        'title',
        'description',
        'severity',
        'status',
        'is_checked',
        'resolved_at',
        'checked_at',
        'checked_by',
        'ball_awarded',
    ];

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
            'checked_at' => 'datetime',
            'is_checked' => 'boolean',
            'ball_awarded' => 'boolean',
        ];
    }


    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function checkedBy()
    {
        return $this->belongsTo(User::class, 'checked_by');
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeBySeverity($query, $severity)
    {
        return $query->where('severity', $severity);
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    public function scopeChecked($query)
    {
        return $query->where('is_checked', true);
    }

    public function scopeNotChecked($query)
    {
        return $query->where('is_checked', false);
    }