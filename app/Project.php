<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes, RecordsActivity;
    
    protected $fillable = [
        'client_id', 'title', 'description'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function addTask($task)
    {
        //return $this->tasks()->create($task);
        
        $task = new Task($task);
        $task->project()->associate($this->id);
        $task->user()->associate(auth()->id());
        $task->save();

        return $task;
    }

    public function path()
    {
        return "/api/projects/{$this->id}";
    }

    protected function completed_tasks()
    {
        return count($this->tasks->filter(function ($task) {
            return $task->completed_at;
        }));
    }

    protected function incompleted_tasks()
    {
        return count($this->tasks->filter(function ($task) {
            return !$task->completed_at;
        }));
    }

    public function toArray()
    {
        return [
            'id'                => $this->id,
            'title'             => $this->title,
            'client_id'         => $this->client_id,
            'client_name'       => optional($this->client)->name,
            'tasks_count'       => $this->tasks->count(),
            'completed_tasks'   => $this->completed_tasks(),
            'incompleted_tasks' => $this->incompleted_tasks()
        ];
    }
}
