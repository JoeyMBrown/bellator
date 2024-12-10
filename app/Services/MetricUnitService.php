<?php

namespace App\Services;

use App\Models\MetricUnit;

class MetricUnitService
{
    /**
     * Return all exercises as an options array.
     */
    public function toOptionsArray()
    {
        return MetricUnit::all(['id', 'name']);
    }
}