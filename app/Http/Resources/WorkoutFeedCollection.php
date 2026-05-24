<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Wraps a paginated workout feed into the `{ data, meta }` shape the
 * frontend expects, preserving the original presentFeedMeta meta keys.
 *
 * @property \Illuminate\Pagination\LengthAwarePaginator $resource
 */
class WorkoutFeedCollection extends ResourceCollection
{
    public $collects = WorkoutFeedResource::class;

    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection
                ->map(fn (WorkoutFeedResource $workout) => $workout->resolve($request))
                ->all(),
            'meta' => [
                'current_page' => $this->resource->currentPage(),
                'last_page' => $this->resource->lastPage(),
                'per_page' => $this->resource->perPage(),
                'total' => $this->resource->total(),
                'has_more_pages' => $this->resource->hasMorePages(),
            ],
        ];
    }
}
