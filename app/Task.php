<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use SoftDeletes, RecordsActivity;
    
    protected $fillable = [
        'title', 'description',
        'hours_spent', 'minutes_spent',
        'completed_at', 'billed_at'
    ];
    
    protected $touches = ['project'];

    protected static $triggerUpdatedFields = ['title', 'description', 'hours_spent', 'minutes_spent'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

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

        $this->recordActivity('completed_task');
    }

    public function incomplete()
    {
        $this->update(['completed_at' => NULL]);

        $this->recordActivity('incompleted_task');
    }

    public function billed()
    {
        $this->update(['billed_at' => Carbon::now()->toDateTimeString()]);

        $this->recordActivity('billed_task');
    }

    public function unbilled()
    {
        $this->update(['billed_at' => NULL]);

        $this->recordActivity('unbilled_task');
    }
}
