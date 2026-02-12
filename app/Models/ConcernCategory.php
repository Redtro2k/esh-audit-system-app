<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConcernCategory extends Model
{
    protected $guarded = [];

    public function parent(){
        return $this->belongsTo(ConcernCategory::class, 'parent_id');
    }
    public function children(){
        return $this->hasMany(ConcernCategory::class, 'parent_id');
    }
}
