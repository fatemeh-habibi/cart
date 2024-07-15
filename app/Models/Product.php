<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends BaseModel
{
    use SoftDeletes;

    protected $table = 'products';
    protected $guarded = ['id'];
    protected $appends = [];
    protected $dates = [];

    public function langs()
    {
        return $this->hasMany(ProductLang::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function carts()
    {
        return $this->hasMany(CartProduct::class);
    }

    public function getPersianNameAttribute()
    {
        $lang = $this->langs()->where('lang_id', 2)->first();
        return $lang->name ?? '';
    }

    public function getEnglishNameAttribute()
    {
        $lang = $this->langs()->where('lang_id', 1)->first();
        return $lang->name ?? '';
    }

    public function scopeLoadRelations($query)
    {
        return $query->with(['langs','created_user','updated_user']);
    }
}
