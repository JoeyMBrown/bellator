<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class GroupExercisePoints extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'group_exercise_points';

    protected $fillable = [
        'group_id',
        'exercise_id',
        'points_per_unit',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'points_per_unit' => 'decimal:6',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }

    public function scopeActive($query)
    {
        return $query->whereNull('end_date');
    }
}
