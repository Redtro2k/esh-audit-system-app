<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Support\Carbon;
use Kirschbaum\Commentions\Contracts\Commentable;
use Kirschbaum\Commentions\HasComments;

class Observation extends Model implements Commentable
{
    use HasComments;

    protected $guarded = [];

    protected $casts = [
        'concern' => 'array',
        'capture_concern' => 'array',
        'capture_solved' => 'array',
        'target_date' => 'datetime',
        'date_captured' => 'datetime',
        'date_pending' => 'datetime',
        'date_ongoing' => 'datetime',
        'date_for_further_discussion' => 'datetime',
        'counter_measure_date' => 'datetime',
        'date_resolved' => 'datetime',
    ];

    public function formatLeadTime(?string $endAttribute, string $startAttribute = 'date_captured'): ?string
    {
        $seconds = $this->getLeadTimeInSeconds($endAttribute, $startAttribute);

        if ($seconds === null) {
            return null;
        }

        return CarbonInterval::seconds($seconds)
            ->cascade()
            ->forHumans([
                'short' => true,
                'parts' => 3,
                'syntax' => CarbonInterface::DIFF_ABSOLUTE,
            ]);
    }

    public function getLeadTimeInSeconds(?string $endAttribute, string $startAttribute = 'date_captured'): ?int
    {
        $start = $this->resolveLeadTimeDate($startAttribute);
        $end = $this->resolveLeadTimeDate($endAttribute);

        if (! $start || ! $end) {
            return null;
        }

        return $start->diffInSeconds($end);
    }

    public function auditor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'auditor_id');
    }

    public function pic(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_id');
    }

    public function dealer(): BelongsTo
    {
        return $this->belongsTo(Dealer::class, 'dealer_id');
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

    protected function resolveLeadTimeDate(?string $attribute): ?Carbon
    {
        if (blank($attribute)) {
            return null;
        }

        $value = data_get($this, $attribute);

        if (blank($value) && $attribute === 'date_captured') {
            $value = $this->created_at;
        }

        if (blank($value)) {
            return null;
        }

        if ($value instanceof CarbonInterface) {
            return Carbon::instance($value);
        }

        return Carbon::parse($value);
    }
}
