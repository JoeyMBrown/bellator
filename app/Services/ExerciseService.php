<?php

namespace App\Services;

use App\Models\Exercise;

class ExerciseService
{
    /**
     * Return all exercises as an options array.
     */
    public function toOptionsArray()
    {
        return Exercise::all(['id', 'name']);
    }
}