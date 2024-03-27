<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DishResource extends JsonResource
{
    public function toArray($request)
    {
        // Return the dish data without the pivot information
        return [
            'dish_id' => $this->dish_id,
            'name' => $this->name,
        ];
    }
}
