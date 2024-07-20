<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Language extends BaseModel
{
    use SoftDeletes;

    protected $guarded = ['id'];
    protected $appends = [];

    public function scopeLoadRelations($query)
    {
        return $query->with(['created_user','updated_user']);
    }
}
