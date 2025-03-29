<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobOrderResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'job_order_number' => $this->job_order_number,
            'customer_name' => $this->customer_name,
            'contact_number' => $this->contact_number,
            'laptop_model' => $this->laptop_model,
            'status' => $this->status,
            'created_at' => $this->created_at->toDateTimeString(),
            'pullout_date' => $this->pullout_date ? $this->pullout_date->toDateString() : null,

            'ram' => collect($this->ram)->filter(fn ($r) => isset($r['capacity']))->values(),
            'ssd' => collect($this->ssd)->filter(fn ($s) => isset($s['capacity']))->values(),
            'hdd' => $this->hdd ?? 'None',

            'has_battery' => $this->has_battery ? 'Yes' : 'No',
            'has_wifi_card' => $this->has_wifi_card ? 'Yes' : 'No',

            'others' => $this->others ?: 'None',
            'without' => $this->without ?: 'None',
        ];
    }
}
