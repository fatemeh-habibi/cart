<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends BaseModel
{
    use SoftDeletes;
    protected $dates = [];

    protected $guarded = ['id'];

    public function permissions() {
        return $this->belongsToMany(Permission::class,'roles_permissions');
    }
     
    public function users() {
        return $this->belongsToMany(User::class,'users_roles');
    }

    public function scopeLoadRelations($query)
    {
        return $query->with(['permissions','created_user','updated_user']);
    }
}
