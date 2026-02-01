<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\StudentGradesResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\ResourceCollection;

class StudentsGradesCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
protected $paginator;

    public function __construct($resource)
    {
        if ($resource instanceof LengthAwarePaginator) {
            parent::__construct(collect($resource->items())); // فقط الـ items
            $this->paginator = $resource;
        } else {
            parent::__construct($resource);
            $this->paginator = null;
        }
    }

    public function toArray($request): array
    {
        // تجميع التقييمات لكل طالب
        return [
            'data' => $this->collection->groupBy('student_id')->map(
                fn($evaluations) => StudentGradesResource::make($evaluations)
            )->values()
        ];
    }

    public function with($request): array
    {
        $meta = [];

        if ($this->paginator) {
            $meta['pagination'] = [
                'current_page' => $this->paginator->currentPage(),
                'last_page'    => $this->paginator->lastPage(),
                'per_page'     => $this->paginator->perPage(),
                'total'        => $this->paginator->total(),
            ];
        }

        return [
            'status'  => 'success',
            'message' => 'تم جلب التقييمات بنجاح',
        ] + $meta;
    }
}
