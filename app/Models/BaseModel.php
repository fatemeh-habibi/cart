<?php

namespace App\Models;

use App\Helpers\Model\Authorable;
use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    use Authorable;

    public function created_user()
    {
        return $this->belongsTo(User::class,'created_user_id');
    }

    public function updated_user()
    {
        return $this->belongsTo(User::class,'updated_user_id');
    }

    //add modal name to stop cheat from id of another model
    /**
     * @return string
     */
    public function getRefidAttribute()
    {
        if(isset($this->attributes['id'])) {
            return md5($this->attributes['id'] . config('setting.encryption_key'));
        }
    } 

    /**
     * @param int $id
     * @param string $refid
     * @return bool
     */
    public static function checkRefid(int $id,string $refid)
    {
        return $refid == md5($id.config('setting.encryption_key'));
    }
}
