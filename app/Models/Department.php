<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $guarded = [];

    public function manager(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->HasMany(User::class);
    }

}
