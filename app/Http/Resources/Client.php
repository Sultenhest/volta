<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Client extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'vat_abbr' => $this->vat_abbr,
            'vat' => $this->vat,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'projects' => $this->projects
        ];
    }
}
