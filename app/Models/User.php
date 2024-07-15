<?php

namespace App\Models;

use App\Helpers\Model\Authorable;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens; // include this after passport install
use App\Permissions\HasPermissionsTrait;
use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens; // update this after passport install
    use SoftDeletes,Authorable;
    use HasPermissionsTrait;

    public const GENDER_MALE = 0;
    public const GENDER_FEMALE = 1;
    public const GENDER_UNKNOWN = 2;

    public const MOBILE_IS_NOT_VERIFIED = null;
    public const MOBILE_IS_VERIFIED = !null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded=['id'];
    protected $appends = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = ['password','remember_token',];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = ['email_verified_at' => 'datetime',];

    /**
     * The attributes that can be sort.
     *
     * @var array
     */
    public $sortable = ['first_name', 'last_name', 'email','roles', 'activation_code', 'mobile_verified_at', 'password', 'image','created_user_id','created_at', 'updated_at'];


    public function created_user()
    {
        return $this->belongsTo(User::class,'created_user_id');
    }

    public function updated_user()
    {
        return $this->belongsTo(User::class,'updated_user_id');
    }

    public function default_lang()
    {
        return $this->belongsTo(Language::class,'default_lang_id');
    }
  
    public function findForPassport($username) {
        return $this->where('username', $username)->first();
    }

    public function getPermissions1Attribute()
    {
        $role_permissions = $this->roles()->get()->map(function ($role) {
            return $role->permissions;
        })->collapse();
        $permissions = $this->permissions()->get()->map(function ($item) {
            return $item;
        });
        $merged = $role_permissions->merge($permissions);
        return $merged->groupBy('module')->map(function($item) {
          return [
            'module' => $item[0]['module'],
            'permissions' => $item->pluck('name')->all(),
          ];
        })->values();
    }

    public function getPermissions2Attribute()
    {
        $role_permissions = $this->roles()->get()->map(function ($role) {
            return $role->permissions;
        })->collapse();
        $permissions = $this->permissions()->get()->map(function ($item) {
            return $item;
        });
        $merged = $role_permissions->merge($permissions);
        $ids = $merged->pluck('id');
        // $names = $merged->groupBy('module')->map(function ($item) {
        //     $all_id = $item->firstWhere('name', 'all') ? $item->firstWhere('name', 'all')->id : 0;
        //     $title = $item[0]['module_fa'];
        //     return [
        //         'title' => $title,
        //         'children' => $all_id ? null : $item->filter(function ($item) {
        //             return $item->name != 'all';
        //         })->map(function ($item) use($title){
        //             return [
        //                 'title' => $item->name_fa,
        //             ];
        //         })->values(),
        //       ];
        // })->values();
  
        return $ids ?? [];
        // return ['ids' => $ids ?? [], 'names' => $names ?? []];
    }
    
    public function getPermissions3Attribute()
    {
        $role_permissions = $this->roles()->get()->map(function ($role) {
            return $role->permissions;
        })->collapse();
        return $role_permissions->pluck('id');
    }

    public function getRoles1Attribute()
    {
        return $this->roles()->get()->map(function ($role) {
          return $role->name;
        });
    }
  
    public function getFullNameAttribute()
    {
       return ucfirst($this->first_name) . ' ' . ucfirst($this->last_name);
    }

    public function scopeLoadRelations($query)
    {
        return $query->with(['roles','created_user','updated_user']);
    }
    
    public function getCreatedByAttribute()
    {
        
        if($this->first_name && $this->last_name){
            $name = $this->first_name.' '.$this->last_name;
        }        

        $result = (object)[
            'name' => $name ?? ''
        ];

        return $result;
    }

}
