<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Laravel\Lumen\Http\Request;

class Staff extends Model implements JWTSubject, AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    protected $table = "staffs";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'firstname',
        'lastname',
        'email',
        'phone',
        'password',
        'postal',
        'address',
        'mobile',
        'employeeid',
        'file',
        'marital_status_id',
        'tax_number',
        'facebook',
        'twitter',
        'linkedin',
        'instagram',
        'ssn',
        'nationality_id',
        'profileurl',
        'gender',
        'dob',
        'status_id',
        'company_id',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token'
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function nationality()
    {
        return $this->belongsTo('App\Nationality');
    }

    public function jobs()
    {
        return $this->hasMany('App\Job');
    }

    public function currentJob()
    {
        return $this->jobs()->orderBy('effective_date', 'DESC')->first();
    }

    public function branch()
    {
        $current_job = $this->jobs()->with('branch')->orderBy('effective_date', 'DESC')->first();
        return $current_job['branch'];
    }

    public function group()
    {
        $current_job = $this->jobs()->with('group')->orderBy('effective_date', 'DESC')->first();
        return $current_job['group'];
    }
    public function role()
    {
        $current_job = $this->jobs()->with('role')->orderBy('effective_date', 'DESC')->first();
        return $current_job['role'];
    }

    public function branchCoWorkers()
    {
        return $this->branch()->staffs()->where('staff_id', '<>', $this->id);
    }

    public function groupCoWorkers()
    {
        return $this->group()->staffs()->where('staff_id', '<>', $this->id);
    }

    public function roleCoWorkers()
    {
        return $this->role()->staffs()->where('staff_id', '<>', $this->id);
    }

    public function emergency()
    {
        return $this->hasMany("App\Emergency");
    }

    public function education()
    {
        return $this->hasMany("App\Education");
    }
    public function employmentStatus()
    {
        return $this->hasMany("App\EmployeeStatus");
    }

    public function status()
    {
        return $this->belongsTo('App\Status');
    }

    public function martialStatus()
    {
        return $this->belongsTo('App\MaritalStatus');
    }

    public function company()
    {
        return $this->belongsTo('App\Company');
    }

    public function messages()
    {
        return $this->morphMany("App\Message", 'messageable');
    }

    public function receiveds()
    {
        return $this->morphMany('App\MessageReceived', 'messagereceivedable');
    }

    public function createdProjects()
    {
        return $this->morphMany('App\Project', 'projectable');
    }

    public function assignedTasks()
    {
        return $this->belongsToMany('App\Task');
    }


    public function assignedProjects()
    {
        return $this->belongsToMany('App\Project');
    }

    public function createdTasks()
    {
        return $this->morphMany('App\Task', 'taskable');
    }

    public function contacts($party = 'partyoneable')
    {
        return $this->morphMany('App\Contact', $party);
    }

    public function isAssignedToProject(int $project_id)
    {
        $projects = $this->assignedProjects;
        $projectcount = count($projects);

        for ($i = 0; $i < $projectcount; $i++) {
            if ($this->assignedProjects[$i]->id === $project_id)
                return true;
        }

        return false;
    }

    public function isAssignedToTask(int $task_id)
    {
        $tasks = $this->assignedTasks;
        $taskcount = count($tasks);

        for ($i = 0; $i < $taskcount; $i++) {
            if ($this->assignedTasks[$i]->id === $task_id)
                return true;
        }

        return false;
    }

    public function ownsProject(int $project_id)
    {
        $projects = $this->createdProjects;
        $projectcount = count($projects);
        for ($i = 0; $i < $projectcount; $i++) {
            if ($projects[$i]->id === $project_id)
                return true;
        }

        return false;
    }

    public function hasPermission(String $permission)
    {
        $permissions = $this->permissions;
        $permcount = count($permissions);
        for ($i = 0; $i < $permcount; $i++) {
            if ($permissions[$i]->name === $permission)
                return true;
        }

        return false;
    }

    public function hasStatus(String $status)
    {
        if ($this->status->name === $status)
            return true;
        return false;
    }

}