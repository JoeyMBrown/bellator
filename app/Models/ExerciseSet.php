<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExerciseSet extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'repetitions',
        'weight'
    ];

    /**
     * Get the exercise that owns the exercise set.
     */
    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }

    /**
     * Get the user that owns the exercise set.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
