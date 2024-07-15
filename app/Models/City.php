<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class City extends BaseModel
{
    use SoftDeletes;

    protected $table = 'province_cities';
    protected $guarded = ['id'];
    protected $appends = [];
    protected $dates = [];

    public function province()
    {
        return $this->belongsTo(City::class , 'parent_id');
    }

    public function cities()
    {
        return $this->hasMany(City::class, 'parent_id');
    }

    public function scopeLoadRelations($query)
    {
        return $query->with(['created_user','updated_user']);
    }
}
