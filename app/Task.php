<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'title', 'description',
        'hours_spent', 'minutes_spent',
        'completed_at', 'billed_at'
    ];
    
    protected $touches = ['project'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function path()
    {
        return "{$this->project->path()}/tasks/{$this->id}";
    }

    public function complete()
    {
        $this->update(['completed_at' => Carbon::now()->toDateTimeString()]);
    }

    public function incomplete()
    {
        $this->update(['completed_at' => NULL]);
    }

    public function billed()
    {
        $this->update(['billed_at' => Carbon::now()->toDateTimeString()]);
    }

    public function unbilled()
    {
        $this->update(['billed_at' => NULL]);
    }
}
