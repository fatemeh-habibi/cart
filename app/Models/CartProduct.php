<?php

namespace App\Models;

class CartProduct extends BaseModel
{
    protected $table = 'cart_products';
    protected $guarded = ['id'];
    protected $dates   = [];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeLoadRelations($query)
    {
        return $query->with(['cart','created_user','product']);
    }
}
