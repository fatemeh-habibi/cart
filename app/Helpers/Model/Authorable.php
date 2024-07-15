<?php

namespace App\Helpers\Model;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

trait Authorable
{
    public static function bootAuthorable()
    {
        //todo
        static::creating(function ($model) {

            if (Schema::hasColumn($model->getTable(), 'updated_at')) {
                $model->updated_at = null;
            }
            if (Schema::hasColumn($model->getTable(), 'created_user_id') && !isset($model->created_user_id)) {
                $model->created_user_id = Auth::id();
            }
        });

        static::updating(function ($model) {
            if (Schema::hasColumn($model->getTable(), 'updated_user_id') && !isset($model->updated_user_id)) {
                $model->updated_user_id = Auth::id();
            }
            if (Schema::hasColumn($model->getTable(), 'updated_at') && !isset($model->updated_at)) {
                $model->updated_at = Carbon::now();
            }
        });

    }
}
