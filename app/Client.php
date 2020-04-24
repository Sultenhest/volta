<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'name', 'description', 'vat_abbr', 'vat'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function path()
    {
        return "/clients/{$this->id}";
    }
}
