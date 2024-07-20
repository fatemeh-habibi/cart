<?php

namespace App\Models;

class Cart extends BaseModel
{
    protected $guarded = ['id'];
    protected $appends = [];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function products()
    {
        return $this->hasMany(CartProduct::class);
    }

    public function delivery()
    {
        return $this->belongsTo(Delivery::class,'delivery_id');
    }

    public function scopeLoadRelations($query)
    {
        return $query->with(['customer','created_user','products']);
    }
}
