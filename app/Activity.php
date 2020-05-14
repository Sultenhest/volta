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
            ->take(50)
            ->get()
            ->groupBy(function ($activity) {
                return $activity->created_at->format('Y-m-d');
            });
    }

    public static function statistics()
    {
        $activities = static::where('user_id', auth()->id())
            ->latest()
            ->get()
            ->groupBy(function ($activity) {
                return $activity->created_at->format('W');
            })
            ->take(10);

        $groupedTypes = $activities->map(function ($item, $key) {
            return collect($item)->groupBy('description');
        });
        
        return $groupedTypes->map(function ($item, $key) {
            return $item->map(function ($item, $key) {
                return $item->count();
            });
        });
    }

    public function toArray()
    {
        return parent::toArray() + [
            'echo_description' => ucwords( str_replace('_', ' ', $this->description) )
        ];
    }
}
