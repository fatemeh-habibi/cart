<?php

namespace App\Models;

use App\Helpers\Model\Authorable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Passport\HasApiTokens; // include this after passport install
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Customer extends Authenticatable
{
    use SoftDeletes,HasApiTokens,Authorable,Notifiable;
    
    public const MOBILE_IS_NOT_VERIFIED = null;
    public const MOBILE_IS_VERIFIED = !null;

    protected $table = 'customers';
    protected $guarded = ['id'];
    protected $appends = [];
    protected $dates   = [];

    public function scopeLoadRelations($query)
    {
        return $query->with(['created_user','updated_user']);
    }

    public function getFullNameAttribute()
    {
       return ucfirst($this->first_name) . ' ' . ucfirst($this->last_name);
    }

    public function created_user()
    {
        return $this->belongsTo(User::class,'created_user_id');
    }

    public function updated_user()
    {
        return $this->belongsTo(User::class,'updated_user_id');
    }

}
