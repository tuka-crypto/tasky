<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'password',
        'first_name',
        'last_name',
        'date_of_birth',
        'profile_image',
        'gender',
        'role_id',
        'is_approved',
        'fcm_token',
        'language',
        'theme'
    ];
    public function role()
    {
    return $this->belongsTo(Role::class);
    }
    public function isAdmin(): bool
    {
        return $this->role && $this->role->name === 'admin';
    }

    public function isMember(): bool
    {
        return $this->role && $this->role->name === 'member';
    }

    public function isManager(): bool
    {
        return $this->role && $this->role->name === 'manager';
    }
    public function teams()
    {
    return $this->belongsToMany(Team::class, 'team_members');
    }

    public function projects()
    {
    return $this->belongsToMany(Project::class, 'project_members');
    }
    public function createdProjects()
    {
    return $this->hasMany(Project::class, 'created_by');
    }
    public function createdTasks()
    {
    return $this->hasMany(Task::class, 'created_by');
    }
    public function tasks()
    {
    return $this->belongsToMany(Task::class, 'task_members');
    }
    public function performance()
    {
    return $this->hasOne(UserPerformance::class);
    }
    public function rewards()
    {
    return $this->hasMany(Reward::class);
    }




    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
