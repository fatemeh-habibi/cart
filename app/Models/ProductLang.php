<?php

namespace App\Models;

class ProductLang extends BaseModel
{
    protected $table = 'products_lang';
    protected $guarded = ['id'];
    protected $appends = [];

    public $timestamps = false;

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeLoadRelations($query)
    {
        return $query->with(['product']);
    }
}
