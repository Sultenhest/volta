<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $guarded = [];

    protected $casts = [
        'changes' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subject()
    {
        return $this->morphTo();
    }

    public static function feed()
    {
        return static::where('user_id', auth()->id())
            ->latest()
            ->with('subject')
            ->paginate(50)
            ->groupBy(function ($activity) {
                return $activity->created_at->format('Y-m-d');
            });
    }

    //['description', 'completed_task']
    //format('W')

    public function toArray()
    {
        return parent::toArray() + [
            'echo_description' => ucwords( str_replace('_', ' ', $this->description) )
        ];
    }
}
