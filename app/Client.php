<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'name', 'description', 'vat_abbr', 'vat'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
