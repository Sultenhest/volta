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

    public function toArray()
    {
        return parent::toArray() + [
            'echo_description' => ucwords( str_replace('_', ' ', $this->description) )
        ];
    }
}
