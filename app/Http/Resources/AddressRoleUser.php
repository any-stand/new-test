<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AddressRoleUser extends JsonResource
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
            'city_id' => $this->city_id,
            'city' => $this->cities ? $this->cities->name : null,
            'area' =>  $this->areas ? $this->areas->name : null,
            'area_id' => $this->area_id,
            'street' => $this->street,
            'house_number' => $this->house_number,
            'number_entrances' => $this->number_entrances,
            'management_company' => $this->management_company,
            'status' => $this->orderAddress,
            'coordinates' => $this->coordinates,
            'addressName' => "г.{$this->cities->name}, ул.{$this->street}, {$this->house_number}",
            'images' => $this->getImagesRole($this->id),
            'data' => null,
            'result' => $this->status($this->orderAddress),
            'entrancesStatus' => $this->entrancesStatus($this->entrances),
            'entrances' => $this->entrances,
            'files' => null
        ];
    }
}
