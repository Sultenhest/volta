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

    protected $with = ['client', 'tasks'];

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
}
