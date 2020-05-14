<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function clients()
    {
        return $this->hasMany(Client::class)->latest('created_at');
    }

    public function projects()
    {
        return $this->hasMany(Project::class)->latest('updated_at');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class)->latest('updated_at');
    }

    public function activity()
    {
        return $this->hasMany(Activity::class)->latest('created_at');
    }

    public function toArray()
    {
        return parent::toArray() + [
            'clients_count'  => $this->clients->count(),
            'projects_count' => $this->projects->count(),
            'tasks_count'    => $this->tasks->count()
        ];
    }
}
