<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Hashing\HashManager;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'created_by',
        'assigned_to',
        'title',
        'description',
        'status',
        'priority',
        'progress',
        'due_date',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function team(){
        return $this->belongsTo(Team::class);
    }
    public function creator(){
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function issues(){
        return $this->hasMany(Issue::class);
    }

     public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function ballLogs(){
        return $this->hasMany(BallLog::class);
    }
}