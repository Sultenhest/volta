<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes, RecordsActivity;
    
    protected $fillable = [
        'name', 'description', 'vat_abbr', 'vat'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class)->latest('updated_at');
    }
    
    public function path()
    {
        return "/api/clients/{$this->id}";
    }
}
