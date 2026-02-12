<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\CategoryConcern;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Observation extends Model
{
    //
    protected $guarded = [];

    protected $casts = [
        'capture_concern' => 'array',
        'capture_solved' => 'array',
    ];

    public function auditor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'auditor_id');
    }
    public function pic(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_id');
    }
    public function department(): HasOneThrough
    {
        return $this->hasOneThrough(
            Department::class, // Final model
            User::class,       // Through model
            'id',              // FK on users (users.id)
            'id',              // FK on departments (departments.id)
            'pic_id',          // FK on observations
            'department_id'    // FK on users
        );
    }

    public function concernType(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ConcernCategory::class, 'concern_type');
    }
}
