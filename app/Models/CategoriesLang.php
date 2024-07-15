<?php

namespace App\Models;

class CategoriesLang extends BaseModel
{
    protected $table = 'categories_lang';
    protected $guarded = ['id'];
    protected $appends = [];
    protected $dates = [];
    public $timestamps = false;

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeLoadRelations($query)
    {
        return $query->with('category');
    }

}
