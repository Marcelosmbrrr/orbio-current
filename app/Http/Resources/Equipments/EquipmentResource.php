<?php

namespace App\Http\Resources\Equipments;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\ResourceCollection;

class EquipmentResource extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return $this->collection->map(function ($equipment) {
            return [
                "id" => $equipment->public_id,
                "name" => $equipment->name,
                "manufacturer" => $equipment->manufacturer,
                "model" => $equipment->model,
                "record_number" => $equipment->record_number,
                "serial_number" => $equipment->serial_number,
                "weight" => $equipment->weight,
                "image" => $equipment->image ? Storage::url($equipment->image) : "",
                "status" => [
                    "title" => $equipment->trashed() ? "Deletado" : "Ativo",
                    "style_key" => $equipment->trashed() ? "deleted" : "active"
                ],
                "created_at" => $equipment->created_at->format('d/m/Y'),
                "updated_at" => $equipment->updated_at->format('d/m/Y'),
                "deleted_at" => $equipment->deleted_at
            ];
        })->all();
    }
}
