<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends BaseModel
{
    use SoftDeletes;

    protected $table = 'categories';
    protected $guarded = ['id'];
    protected $appends = [];
    protected $dates = [];

    public function langs()
    {
        return $this->hasMany(CategoriesLang::class);
    }

    public function parent()
    {
        return $this->belongsTo(Category::class , 'parent_id');
    }

    public function childs()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    public function attributes() {
        return $this->belongsToMany(Attribute::class,'category_attributes');
    }

    public function scopeLoadRelations($query)
    {
        return $query->with(['parent','childs','created_user','updated_user']);
    }
}
