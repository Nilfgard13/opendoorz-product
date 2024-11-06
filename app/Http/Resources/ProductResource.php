<?php

namespace App\Http\Resources;

use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        return [
            // 'id' => $this->id,
            // 'title' => $this->title,
            // 'description' => $this->description,
            // 'price' => $this->price,
            // 'location' => $this->location,
            // 'type' => $this->type,
            // 'status' => $this->status,
            // 'category_id' => $this->categories->pluck('id'), // Mengambil ID kategori yang terkait
            // 'images' => ImageResource::collection($this->images), // Mengambil gambar terkait

            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'price' => (float) $this->price,
            'location' => $this->location,
            'type' => $this->type,
            'status' => $this->status,
            'category' => [
                'id' => $this->category_id,
                'name' => $this->whenLoaded('categories', function () {
                    return $this->categories?->name ?? null;
                })
            ],
            'images' => $this->whenLoaded('images', function () {
                return $this->images->map(function ($image) {
                    return [
                        'id' => $image->id,
                        'path' => $image->image_path
                    ];
                });
            }),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
