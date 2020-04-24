<?php

namespace App;

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
}
